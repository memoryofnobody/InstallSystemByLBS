<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

checkLogin();

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
    
    // 筛选条件
    $company_name = $_GET['company_name'] ?? '';
    $is_shipped = $_GET['is_shipped'] ?? '';
    $is_received = $_GET['is_received'] ?? '';
    
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
    
    // 查询地图数据
    $sql = "
        SELECT 
            o.id,
            o.company_name,
            o.install_latitude as lat,
            o.install_longitude as lng,
            o.purchase_quantity,
            o.install_address,
            COUNT(ir.id) as total_install_items,
            SUM(CASE WHEN ir.installed = 1 THEN 1 ELSE 0 END) as installed_count
        FROM orders o
        LEFT JOIN install_requirements ir ON o.id = ir.order_id
        $whereClause
        GROUP BY o.id
        HAVING lat IS NOT NULL AND lng IS NOT NULL
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
    // 按位置聚合数据
    $map_points = [];
    foreach ($orders as $order) {
        $key = $order['lat'] . '_' . $order['lng'];
        
        if (!isset($map_points[$key])) {
            $map_points[$key] = [
                'lat' => floatval($order['lat']),
                'lng' => floatval($order['lng']),
                'address' => $order['install_address'],
                'total_quantity' => 0,
                'total_installed' => 0,
                'companies' => []
            ];
        }
        
        $map_points[$key]['total_quantity'] += intval($order['purchase_quantity']);
        $map_points[$key]['total_installed'] += intval($order['installed_count']);
        $map_points[$key]['companies'][] = [
            'order_id' => $order['id'],
            'company_name' => $order['company_name'],
            'purchase_quantity' => intval($order['purchase_quantity']),
            'installed_count' => intval($order['installed_count']),
            'total_install_items' => intval($order['total_install_items'])
        ];
    }
    
    jsonResponse(['success' => true, 'points' => array_values($map_points)]);
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => '获取地图数据失败: ' . $e->getMessage()]);
}
?>
