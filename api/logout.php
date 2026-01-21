<?php
require_once __DIR__ . '/../config/config.php';

session_destroy();
jsonResponse(['success' => true, 'message' => '退出成功']);
?>
