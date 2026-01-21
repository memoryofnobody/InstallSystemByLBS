<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

// GET - 获取用户列表
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    checkPermission([USER_TYPE_ADMIN]);
    
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->query("SELECT id, username, user_type, real_name, phone, company, created_at FROM users ORDER BY created_at DESC");
        $users = $stmt->fetchAll();
        
        jsonResponse(['success' => true, 'users' => $users]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => '获取用户列表失败: ' . $e->getMessage()]);
    }
}

// POST - 创建新用户
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkPermission([USER_TYPE_ADMIN]);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $user_type = $data['user_type'] ?? '';
    $real_name = $data['real_name'] ?? '';
    $phone = $data['phone'] ?? '';
    $company = $data['company'] ?? '';
    
    if (empty($username) || empty($password) || empty($user_type)) {
        jsonResponse(['success' => false, 'message' => '用户名、密码和用户类型不能为空']);
    }
    
    $allowed_types = [USER_TYPE_ADMIN, USER_TYPE_BUYER, USER_TYPE_SUPPLIER, USER_TYPE_ENGINEER];
    if (!in_array($user_type, $allowed_types)) {
        jsonResponse(['success' => false, 'message' => '无效的用户类型']);
    }
    
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("INSERT INTO users (username, password, user_type, real_name, phone, company) VALUES (?, ?, ?, ?, ?, ?)");
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([$username, $hashed_password, $user_type, $real_name, $phone, $company]);
        
        jsonResponse(['success' => true, 'message' => '用户创建成功', 'user_id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(['success' => false, 'message' => '用户名已存在']);
        }
        jsonResponse(['success' => false, 'message' => '创建用户失败: ' . $e->getMessage()]);
    }
}

// DELETE - 删除用户
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    checkPermission([USER_TYPE_ADMIN]);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['user_id'] ?? 0;
    
    if ($user_id <= 0) {
        jsonResponse(['success' => false, 'message' => '无效的用户ID']);
    }
    
    if ($user_id == $_SESSION['user_id']) {
        jsonResponse(['success' => false, 'message' => '不能删除当前登录用户']);
    }
    
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        jsonResponse(['success' => true, 'message' => '用户删除成功']);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => '删除用户失败: ' . $e->getMessage()]);
    }
}
?>
