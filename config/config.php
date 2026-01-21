<?php
// 全局配置
session_start();

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 错误报告
error_reporting(E_ALL);
ini_set('display_errors', 0);

// 百度地图API密钥 (请替换为您自己的密钥)
define('BAIDU_MAP_AK', '************');

// 用户类型常量
define('USER_TYPE_ADMIN', 'admin');
define('USER_TYPE_BUYER', 'buyer');
define('USER_TYPE_SUPPLIER', 'supplier');
define('USER_TYPE_ENGINEER', 'engineer');

// 引入数据库配置
require_once __DIR__ . '/database.php';

// 检查用户是否登录
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '未登录', 'redirect' => '/login.html']);
        exit;
    }
}

// 检查用户权限
function checkPermission($allowedTypes) {
    checkLogin();
    if (!in_array($_SESSION['user_type'], $allowedTypes)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '权限不足']);
        exit;
    }
}

// 返回JSON响应
function jsonResponse($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
?>
