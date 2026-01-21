<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => '请求方法错误']);
}

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    jsonResponse(['success' => false, 'message' => '用户名和密码不能为空']);
}

try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['real_name'] = $user['real_name'];
        
        jsonResponse([
            'success' => true, 
            'message' => '登录成功',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'user_type' => $user['user_type'],
                'real_name' => $user['real_name']
            ]
        ]);
    } else {
        jsonResponse(['success' => false, 'message' => '用户名或密码错误']);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => '登录失败: ' . $e->getMessage()]);
}
?>
