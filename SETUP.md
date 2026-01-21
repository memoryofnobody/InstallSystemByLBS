# 订单地图管理系统 - 搭建指南

## 系统概述

这是一个基于百度地图API的订单管理系统,支持多角色用户管理、订单地图可视化、发货追踪和安装管理等功能。

## 技术架构

- **前端**: HTML5 + CSS3 + JavaScript + 百度地图API
- **后端**: PHP 7.4+
- **数据库**: MySQL 5.7+
- **Web服务器**: Apache 2.4+

## 文件结构

```
project/
├── api/                          # API接口目录
│   ├── login.php                 # 登录接口
│   ├── logout.php                # 退出登录接口
│   ├── current_user.php          # 获取当前用户信息
│   ├── users.php                 # 用户管理接口
│   ├── orders.php                # 订单管理接口
│   ├── install_requirements.php  # 安装需求管理接口
│   ├── map_data.php              # 地图数据接口
│   └── export.php                # 导出接口
├── config/                       # 配置文件目录
│   ├── config.php                # 全局配置
│   └── database.php              # 数据库配置
├── css/                          # 样式文件目录
│   └── style.css                 # 主样式文件
├── js/                           # JavaScript文件目录
│   ├── common.js                 # 通用函数
│   ├── map.js                    # 地图功能
│   ├── orders.js                 # 订单管理
│   └── users.js                  # 用户管理
├── install/                      # 安装脚本目录
│   └── install.php               # 数据库初始化脚本
├── .htaccess                     # Apache配置文件
├── login.html                    # 登录页面
└── index.html                    # 主界面

```

## 搭建步骤

### 1. 环境准备

#### 1.1 安装Apache + PHP + MySQL

**Windows系统推荐使用XAMPP:**
- 下载: https://www.apachefriends.org/
- 安装后启动Apache和MySQL服务

**Linux系统:**
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install apache2 php php-mysql mysql-server

# CentOS/RHEL
sudo yum install httpd php php-mysqlnd mariadb-server
```

#### 1.2 验证环境

创建 `phpinfo.php` 测试文件:
```php
<?php phpinfo(); ?>
```

访问 `http://localhost/phpinfo.php` 确认PHP正常运行。

### 2. 项目部署

#### 2.1 复制项目文件

将所有项目文件复制到Web服务器根目录:

- **XAMPP**: `C:\xampp\htdocs\order_system\`
- **Linux Apache**: `/var/www/html/order_system/`

#### 2.2 配置数据库

编辑 `config/database.php`,修改数据库连接信息:

```php
define('DB_HOST', 'localhost');    // 数据库主机
define('DB_USER', 'root');         // 数据库用户名
define('DB_PASS', '');             // 数据库密码
define('DB_NAME', 'order_map_system'); // 数据库名称
```

#### 2.3 配置百度地图API密钥

1. 访问百度地图开放平台: https://lbsyun.baidu.com/
2. 注册并创建应用,获取AK(访问密钥)
3. 编辑 `config/config.php`,替换API密钥:

```php
define('BAIDU_MAP_AK', 'YOUR_BAIDU_MAP_AK_HERE');
```

4. 编辑 `index.html`,在第7行替换API密钥:

```html
<script type="text/javascript" src="https://api.map.baidu.com/api?v=3.0&ak=YOUR_BAIDU_MAP_AK_HERE"></script>
```

### 3. 数据库初始化

访问安装页面: `http://localhost/order_system/install/install.php`

该脚本会自动:
- 创建数据库 `order_map_system`
- 创建数据表(users, orders, install_requirements)
- 插入默认管理员账号: 
  - 用户名: `admin`
  - 密码: `admin`

### 4. Apache配置(可选)

#### 4.1 启用mod_rewrite模块

**Windows (XAMPP):**
编辑 `C:\xampp\apache\conf\httpd.conf`,取消以下行的注释:
```
LoadModule rewrite_module modules/mod_rewrite.so
```

**Linux:**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### 4.2 允许.htaccess覆盖

编辑Apache配置文件,确保项目目录允许覆盖:

```apache
<Directory "/path/to/order_system">
    AllowOverride All
    Require all granted
</Directory>
```

重启Apache服务。

### 5. 访问系统

打开浏览器访问: `http://localhost/order_system/login.html`

使用默认管理员账号登录:
- 用户名: `admin`
- 密码: `admin`

## 功能说明

### 用户角色

1. **管理员(admin)**: 拥有所有权限,可管理用户、订单、发货、安装
2. **买家(buyer)**: 可创建订单,查看自己的订单
3. **供应商(supplier)**: 可查看所有订单,录入发货信息
4. **安装工程师(engineer)**: 可查看所有订单,录入安装信息

### 主要功能

#### 1. 地图视图
- 根据安装位置在地图上显示标记点
- 支持点聚合(缩放时汇聚/离散)
- 点击标记显示订单详情
- 支持筛选(单位名称、发货状态、收货状态)

#### 2. 列表视图
- 表格形式显示所有订单
- 支持多条件筛选
- 快速操作(查看详情、发货、安装、导出)

#### 3. 订单管理
- **买家**: 点击地图创建订单,填写详细信息
- **供应商**: 录入发货信息(物流单号、收货情况)
- **安装工程师**: 录入安装详情(设备序号、SIM卡序号)

#### 4. 数据导出
- 支持按订单导出
- 支持按单位导出
- CSV格式,包含安装清单完整信息

## 常见问题

### 1. 数据库连接失败

检查:
- MySQL服务是否启动
- `config/database.php` 中的数据库连接信息是否正确
- 数据库用户是否有足够权限

### 2. 地图不显示

检查:
- 百度地图AK是否正确配置
- 浏览器控制台是否有JavaScript错误
- 网络是否能访问百度地图API

### 3. .htaccess不生效

检查:
- Apache是否启用mod_rewrite模块
- 目录配置是否允许AllowOverride All

### 4. 文件上传/权限问题

确保Web服务器对项目目录有读写权限:

**Linux:**
```bash
sudo chown -R www-data:www-data /var/www/html/order_system
sudo chmod -R 755 /var/www/html/order_system
```

## 安全建议

1. **修改默认管理员密码**: 首次登录后立即修改
2. **配置HTTPS**: 生产环境使用SSL证书
3. **数据库安全**: 为数据库用户设置强密码,限制访问权限
4. **定期备份**: 定期备份数据库和重要文件
5. **更新依赖**: 保持PHP、Apache、MySQL版本更新

## 技术支持

如遇到问题,请检查:
1. Apache错误日志: `error.log`
2. PHP错误日志: `php_error.log`
3. 浏览器控制台(F12)的JavaScript错误

## 后续优化建议

1. 添加用户权限细化控制
2. 实现消息通知功能
3. 添加订单状态流转记录
4. 集成短信/邮件通知
5. 实现数据统计和报表功能
6. 添加移动端适配
7. 优化地图性能(大量数据时)
