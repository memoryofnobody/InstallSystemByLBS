<?php
/**
 * 配置文件示例
 * 
 * 首次使用请按照以下步骤配置:
 * 
 * 1. 配置数据库连接信息
 * 2. 申请并配置百度地图API密钥
 * 3. 运行 install/install.php 初始化数据库
 * 4. 访问 login.html 登录系统
 * 
 * 默认管理员账号:
 * 用户名: admin
 * 密码: admin
 */

// ============================================
// 数据库配置
// ============================================

// 数据库服务器地址 (通常为 localhost)
define('DB_HOST', 'localhost');

// 数据库用户名
define('DB_USER', 'root');

// 数据库密码
define('DB_PASS', '');

// 数据库名称
define('DB_NAME', 'order_map_system');

// 数据库字符集
define('DB_CHARSET', 'utf8mb4');


// ============================================
// 百度地图API配置
// ============================================

/**
 * 如何获取百度地图API密钥(AK):
 * 
 * 1. 访问百度地图开放平台: https://lbsyun.baidu.com/
 * 2. 注册/登录百度账号
 * 3. 进入"控制台" -> "应用管理" -> "我的应用"
 * 4. 点击"创建应用"
 * 5. 填写应用信息:
 *    - 应用名称: 订单地图管理系统
 *    - 应用类型: 浏览器端
 *    - 启用服务: 勾选"地图"相关服务
 *    - 白名单: 填写你的域名或IP (开发时可填 * 允许所有)
 * 6. 提交后获得AK(访问密钥)
 * 7. 将AK复制到下方配置中
 * 
 * 注意: 
 * - 免费版每天有配额限制,个人开发测试足够使用
 * - 生产环境请配置白名单保护AK安全
 */

// 百度地图API密钥 (请替换为你自己的AK)
define('BAIDU_MAP_AK', 'YOUR_BAIDU_MAP_AK_HERE');


// ============================================
// 系统配置
// ============================================

// 时区设置
define('TIMEZONE', 'Asia/Shanghai');

// Session配置
define('SESSION_LIFETIME', 7200); // Session有效期(秒), 默认2小时

// 分页配置
define('PAGE_SIZE', 20); // 每页显示条数

// 文件上传配置
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 最大上传文件大小(字节), 默认10MB
define('UPLOAD_ALLOWED_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx'); // 允许上传的文件类型


// ============================================
// 用户类型常量
// ============================================

define('USER_TYPE_ADMIN', 'admin');          // 管理员
define('USER_TYPE_BUYER', 'buyer');          // 买家
define('USER_TYPE_SUPPLIER', 'supplier');    // 供应商
define('USER_TYPE_ENGINEER', 'engineer');    // 安装工程师


// ============================================
// 安全配置
// ============================================

// 是否启用调试模式 (生产环境请设置为 false)
define('DEBUG_MODE', true);

// 密码最小长度
define('PASSWORD_MIN_LENGTH', 6);

// 登录失败锁定配置
define('LOGIN_MAX_ATTEMPTS', 5);        // 最大失败次数
define('LOGIN_LOCK_TIME', 900);         // 锁定时间(秒), 默认15分钟


// ============================================
// 日志配置
// ============================================

// 是否启用操作日志
define('ENABLE_LOG', true);

// 日志存储路径
define('LOG_PATH', __DIR__ . '/../logs/');


// ============================================
// 以下配置一般不需要修改
// ============================================

// 项目根目录
define('ROOT_PATH', dirname(__DIR__));

// API接口路径
define('API_PATH', ROOT_PATH . '/api/');

// 上传文件存储路径
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');

?>
