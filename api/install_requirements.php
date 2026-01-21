<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

// POST - 更新安装信息
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkPermission([USER_TYPE_ADMIN, USER_TYPE_ENGINEER]);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $requirement_id = $data['requirement_id'] ?? 0;
    
    if ($requirement_id <= 0) {
        jsonResponse(['success' => false, 'message' => '无效的安装需求ID']);
    }
    
    try {
        $pdo = getDbConnection();
        
        $updates = [];
        $params = [];
        
        if (isset($data['device_serial'])) {
            $updates[] = "device_serial = ?";
            $params[] = $data['device_serial'];
        }
        
        if (isset($data['sim_serial'])) {
            $updates[] = "sim_serial = ?";
            $params[] = $data['sim_serial'];
        }
        
        if (isset($data['installed'])) {
            $updates[] = "installed = ?";
            $params[] = intval($data['installed']);
            
            if ($data['installed']) {
                $updates[] = "install_time = ?";
                $params[] = date('Y-m-d H:i:s');
                
                $updates[] = "engineer_id = ?";
                $params[] = $_SESSION['user_id'];
                
                $updates[] = "engineer_name = ?";
                $params[] = $_SESSION['real_name'] ?? $_SESSION['username'];
            }
        }
        
        if (empty($updates)) {
            jsonResponse(['success' => false, 'message' => '没有要更新的字段']);
        }
        
        $params[] = $requirement_id;
        $sql = "UPDATE install_requirements SET " . implode(', ', $updates) . " WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        jsonResponse(['success' => true, 'message' => '安装信息更新成功']);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => '更新安装信息失败: ' . $e->getMessage()]);
    }
}

// PUT - 批量更新安装信息
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    checkPermission([USER_TYPE_ADMIN, USER_TYPE_ENGINEER]);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $install_items = $data['install_items'] ?? [];
    
    if (empty($install_items) || !is_array($install_items)) {
        jsonResponse(['success' => false, 'message' => '安装项目列表不能为空']);
    }
    
    try {
        $pdo = getDbConnection();
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            UPDATE install_requirements 
            SET device_serial = ?, sim_serial = ?, installed = 1, 
                install_time = ?, engineer_id = ?, engineer_name = ?
            WHERE id = ?
        ");
        
        $install_time = date('Y-m-d H:i:s');
        $engineer_id = $_SESSION['user_id'];
        $engineer_name = $_SESSION['real_name'] ?? $_SESSION['username'];
        
        foreach ($install_items as $item) {
            if (isset($item['requirement_id'])) {
                $stmt->execute([
                    $item['device_serial'] ?? '',
                    $item['sim_serial'] ?? '',
                    $install_time,
                    $engineer_id,
                    $engineer_name,
                    $item['requirement_id']
                ]);
            }
        }
        
        $pdo->commit();
        jsonResponse(['success' => true, 'message' => '批量安装信息更新成功']);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => '批量更新失败: ' . $e->getMessage()]);
    }
}
?>
