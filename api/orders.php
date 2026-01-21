<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

// GET - 获取订单列表
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    checkLogin();
    
    $company_name = $_GET['company_name'] ?? '';
    $is_shipped = $_GET['is_shipped'] ?? '';
    $is_received = $_GET['is_received'] ?? '';
    $install_status = $_GET['install_status'] ?? '';
    
    try {
        $pdo = getDbConnection();
        
        // 构建查询条件
        $where = [];
        $params = [];
        
        // 根据用户类型限制查询
        if ($_SESSION['user_type'] === USER_TYPE_BUYER) {
            $where[] = "o.buyer_id = ?";
            $params[] = $_SESSION['user_id'];
        }
        
        if (!empty($company_name)) {
            $where[] = "o.company_name LIKE ?";
            $params[] = "%$company_name%";
        }
        
        if ($is_shipped !== '') {
            $where[] = "o.is_shipped = ?";
            $params[] = intval($is_shipped);
        }
        
        if ($is_received !== '') {
            $where[] = "o.is_received = ?";
            $params[] = intval($is_received);
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // 查询订单及统计信息
        $sql = "
            SELECT 
                o.*,
                u.username as buyer_username,
                u.real_name as buyer_name,
                COUNT(ir.id) as total_install_items,
                SUM(CASE WHEN ir.installed = 1 THEN 1 ELSE 0 END) as installed_count
            FROM orders o
            LEFT JOIN users u ON o.buyer_id = u.id
            LEFT JOIN install_requirements ir ON o.id = ir.order_id
            $whereClause
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();
        
        // 根据安装状态筛选
        if ($install_status !== '') {
            $orders = array_filter($orders, function($order) use ($install_status) {
                if ($install_status === 'all_installed') {
                    return $order['total_install_items'] > 0 && $order['installed_count'] == $order['total_install_items'];
                } elseif ($install_status === 'partial_installed') {
                    return $order['installed_count'] > 0 && $order['installed_count'] < $order['total_install_items'];
                } elseif ($install_status === 'not_installed') {
                    return $order['installed_count'] == 0;
                }
                return true;
            });
            $orders = array_values($orders);
        }
        
        // 获取每个订单的安装详情
        foreach ($orders as &$order) {
            $stmt = $pdo->prepare("
                SELECT ir.*, u.username as engineer_username, u.real_name as engineer_real_name
                FROM install_requirements ir
                LEFT JOIN users u ON ir.engineer_id = u.id
                WHERE ir.order_id = ?
                ORDER BY ir.installed DESC, ir.created_at ASC
            ");
            $stmt->execute([$order['id']]);
            $order['install_details'] = $stmt->fetchAll();
        }
        
        jsonResponse(['success' => true, 'orders' => $orders]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => '获取订单列表失败: ' . $e->getMessage()]);
    }
}

// POST - 创建订单
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkPermission([USER_TYPE_ADMIN, USER_TYPE_BUYER]);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // 验证必填字段
    $required = ['company_name', 'purchase_quantity', 'install_quantity', 'install_address'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            jsonResponse(['success' => false, 'message' => "字段 $field 不能为空"]);
        }
    }
    
    try {
        $pdo = getDbConnection();
        $pdo->beginTransaction();
        
        // 插入订单
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                buyer_id, company_name, purchase_quantity, install_quantity,
                install_address, install_latitude, install_longitude,
                order_contact, order_phone, receiver_name, receiver_address, receiver_phone,
                install_contact, install_phone, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $buyer_id = $_SESSION['user_type'] === USER_TYPE_BUYER ? $_SESSION['user_id'] : $data['buyer_id'];
        
        $stmt->execute([
            $buyer_id,
            $data['company_name'],
            $data['purchase_quantity'],
            $data['install_quantity'],
            $data['install_address'],
            $data['install_latitude'] ?? null,
            $data['install_longitude'] ?? null,
            $data['order_contact'] ?? '',
            $data['order_phone'] ?? '',
            $data['receiver_name'] ?? '',
            $data['receiver_address'] ?? '',
            $data['receiver_phone'] ?? '',
            $data['install_contact'] ?? '',
            $data['install_phone'] ?? '',
            $data['remarks'] ?? ''
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // 插入安装需求清单
        if (!empty($data['install_requirements']) && is_array($data['install_requirements'])) {
            $stmt = $pdo->prepare("
                INSERT INTO install_requirements (order_id, vehicle_plate, terminal_number)
                VALUES (?, ?, ?)
            ");
            
            foreach ($data['install_requirements'] as $item) {
                if (!empty($item['vehicle_plate']) && !empty($item['terminal_number'])) {
                    $stmt->execute([
                        $order_id,
                        $item['vehicle_plate'],
                        $item['terminal_number']
                    ]);
                }
            }
        }
        
        $pdo->commit();
        jsonResponse(['success' => true, 'message' => '订单创建成功', 'order_id' => $order_id]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => '创建订单失败: ' . $e->getMessage()]);
    }
}

// PUT - 更新订单
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    checkLogin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    $order_id = $data['order_id'] ?? 0;
    
    if ($order_id <= 0) {
        jsonResponse(['success' => false, 'message' => '无效的订单ID']);
    }
    
    try {
        $pdo = getDbConnection();
        
        // 检查权限
        if ($_SESSION['user_type'] === USER_TYPE_BUYER) {
            $stmt = $pdo->prepare("SELECT buyer_id FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch();
            if (!$order || $order['buyer_id'] != $_SESSION['user_id']) {
                jsonResponse(['success' => false, 'message' => '无权操作此订单']);
            }
        }
        
        // 构建更新语句
        $updates = [];
        $params = [];
        
        $allowed_fields = [
            'company_name', 'purchase_quantity', 'install_quantity', 'install_address',
            'install_latitude', 'install_longitude', 'order_contact', 'order_phone',
            'receiver_name', 'receiver_address', 'receiver_phone', 'install_contact',
            'install_phone', 'is_shipped', 'tracking_number', 'is_received', 
            'received_quantity', 'remarks'
        ];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            jsonResponse(['success' => false, 'message' => '没有要更新的字段']);
        }
        
        $params[] = $order_id;
        $sql = "UPDATE orders SET " . implode(', ', $updates) . " WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        jsonResponse(['success' => true, 'message' => '订单更新成功']);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => '更新订单失败: ' . $e->getMessage()]);
    }
}
?>
