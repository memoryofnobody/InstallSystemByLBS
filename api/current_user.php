<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['user_id'])) {
    jsonResponse([
        'success' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'user_type' => $_SESSION['user_type'],
            'real_name' => $_SESSION['real_name']
        ]
    ]);
} else {
    jsonResponse(['success' => false, 'message' => '未登录']);
}
?>
