# 项目文件清单

## 完整文件结构

```
order_map_system/
│
├── 📁 api/                                 # 后端API接口目录
│   ├── login.php                          # 用户登录接口
│   ├── logout.php                         # 退出登录接口
│   ├── current_user.php                   # 获取当前登录用户信息
│   ├── users.php                          # 用户管理接口(增删查)
│   ├── orders.php                         # 订单管理接口(增删改查)
│   ├── install_requirements.php           # 安装需求管理接口
│   ├── map_data.php                       # 地图数据查询接口
│   └── export.php                         # 数据导出接口(CSV)
│
├── 📁 config/                              # 配置文件目录
│   ├── config.php                         # 全局配置(Session、时区、常量)
│   └── database.php                       # 数据库连接配置
│
├── 📁 css/                                 # 样式文件目录
│   └── style.css                          # 主样式文件(全局样式、组件样式、响应式)
│
├── 📁 js/                                  # JavaScript文件目录
│   ├── common.js                          # 通用函数(API请求、消息提示、工具函数)
│   ├── map.js                             # 百度地图功能(地图初始化、标记管理、聚合)
│   ├── orders.js                          # 订单管理功能(创建、查看、发货、安装)
│   └── users.js                           # 用户管理功能(创建、删除、列表)
│
├── 📁 install/                             # 安装脚本目录
│   └── install.php                        # 数据库初始化脚本
│
├── 📄 .htaccess                            # Apache配置文件(URL重写、PHP设置)
├── 📄 login.html                           # 登录页面
├── 📄 index.html                           # 主界面(地图视图、列表视图、用户管理)
│
├── 📄 README.md                            # 项目说明文档
├── 📄 SETUP.md                             # 详细搭建指南
├── 📄 USER_GUIDE.md                        # 用户使用手册
├── 📄 config.example.php                   # 配置文件示例
└── 📄 PROJECT_FILES.md                     # 本文件(项目文件清单)
```

---

## 文件功能说明

### 📁 后端API接口 (api/)

| 文件名 | 请求方法 | 功能描述 |
|--------|---------|----------|
| `login.php` | POST | 用户登录验证,创建Session |
| `logout.php` | POST | 退出登录,销毁Session |
| `current_user.php` | GET | 获取当前登录用户信息 |
| `users.php` | GET/POST/DELETE | 用户管理(查询列表/创建/删除) |
| `orders.php` | GET/POST/PUT | 订单管理(查询/创建/更新) |
| `install_requirements.php` | POST/PUT | 安装信息管理(单条更新/批量更新) |
| `map_data.php` | GET | 获取地图标记数据(聚合查询) |
| `export.php` | GET | 导出安装清单(CSV格式) |

### 📁 配置文件 (config/)

| 文件名 | 说明 |
|--------|------|
| `config.php` | 全局配置(Session管理、时区、用户类型常量、工具函数) |
| `database.php` | 数据库连接配置(PDO连接封装) |

### 📁 前端样式 (css/)

| 文件名 | 说明 |
|--------|------|
| `style.css` | 主样式文件(1000+ 行),包含:<br>- 全局样式<br>- 登录页面样式<br>- 主界面布局<br>- 表格样式<br>- 模态框样式<br>- 地图容器样式<br>- 响应式设计<br>- 动画效果 |

### 📁 前端脚本 (js/)

| 文件名 | 主要函数 | 功能描述 |
|--------|---------|----------|
| `common.js` | `apiRequest()`<br>`showMessage()`<br>`checkLogin()`<br>`formatDateTime()`<br>`exportToCSV()` | 通用工具函数:<br>- API请求封装<br>- 消息提示<br>- 登录状态检查<br>- 日期格式化<br>- 数据导出 |
| `map.js` | `initMap()`<br>`loadMapData()`<br>`displayMapMarkers()`<br>`showMarkerInfo()`<br>`initOrderMap()` | 百度地图功能:<br>- 地图初始化<br>- 加载地图数据<br>- 显示标记点<br>- 点聚合<br>- 信息窗口 |
| `orders.js` | `loadOrderList()`<br>`showCreateOrderModal()`<br>`submitCreateOrder()`<br>`showOrderDetail()`<br>`submitShipping()`<br>`submitInstall()` | 订单管理:<br>- 订单列表加载<br>- 创建订单<br>- 查看详情<br>- 发货管理<br>- 安装管理 |
| `users.js` | `loadUserList()`<br>`showCreateUserModal()`<br>`submitCreateUser()`<br>`deleteUser()` | 用户管理:<br>- 用户列表加载<br>- 创建用户<br>- 删除用户 |

### 📁 安装脚本 (install/)

| 文件名 | 说明 |
|--------|------|
| `install.php` | 数据库初始化脚本,自动创建:<br>- 数据库<br>- users表(用户表)<br>- orders表(订单表)<br>- install_requirements表(安装需求表)<br>- 默认管理员账号 |

### 📄 核心页面

| 文件名 | 说明 |
|--------|------|
| `login.html` | 登录页面,包含:<br>- 登录表单<br>- 自动登录检测<br>- 表单验证 |
| `index.html` | 主界面,包含:<br>- 顶部导航栏<br>- 标签页切换<br>- 地图视图<br>- 列表视图<br>- 用户管理<br>- 多个模态框(创建订单、订单详情、创建用户等) |

### 📄 配置与文档

| 文件名 | 说明 |
|--------|------|
| `.htaccess` | Apache配置:<br>- URL重写规则<br>- PHP配置<br>- 字符编码设置 |
| `README.md` | 项目概述文档:<br>- 快速开始<br>- 核心功能介绍<br>- 技术特点 |
| `SETUP.md` | 详细搭建指南:<br>- 环境安装<br>- 项目部署<br>- 配置说明<br>- 常见问题 |
| `USER_GUIDE.md` | 用户使用手册:<br>- 各角色操作指南<br>- 功能详解<br>- 操作技巧 |
| `config.example.php` | 配置文件示例:<br>- 详细的配置说明<br>- 百度地图AK获取教程 |

---

## 数据库设计

### users (用户表)

| 字段名 | 类型 | 说明 |
|--------|-----|------|
| id | INT | 主键,自增 |
| username | VARCHAR(50) | 用户名,唯一 |
| password | VARCHAR(255) | 密码(哈希存储) |
| user_type | ENUM | 用户类型(admin/buyer/supplier/engineer) |
| real_name | VARCHAR(100) | 真实姓名 |
| phone | VARCHAR(20) | 电话 |
| company | VARCHAR(200) | 公司 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |

### orders (订单表)

| 字段名 | 类型 | 说明 |
|--------|-----|------|
| id | INT | 主键,自增 |
| buyer_id | INT | 买家ID(外键) |
| company_name | VARCHAR(200) | 单位名称 |
| purchase_quantity | INT | 购买数量 |
| install_quantity | INT | 安装数量 |
| install_address | VARCHAR(500) | 安装地址 |
| install_latitude | DECIMAL(10,7) | 纬度 |
| install_longitude | DECIMAL(10,7) | 经度 |
| order_contact | VARCHAR(100) | 下单人 |
| order_phone | VARCHAR(20) | 下单人电话 |
| receiver_name | VARCHAR(100) | 收货人 |
| receiver_address | VARCHAR(500) | 收货地址 |
| receiver_phone | VARCHAR(20) | 收货人电话 |
| install_contact | VARCHAR(100) | 安装联系人 |
| install_phone | VARCHAR(20) | 安装电话 |
| is_shipped | TINYINT(1) | 是否发货 |
| tracking_number | VARCHAR(100) | 物流单号 |
| is_received | TINYINT(1) | 是否收货 |
| received_quantity | INT | 收货数量 |
| remarks | TEXT | 备注 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |

### install_requirements (安装需求表)

| 字段名 | 类型 | 说明 |
|--------|-----|------|
| id | INT | 主键,自增 |
| order_id | INT | 订单ID(外键) |
| vehicle_plate | VARCHAR(50) | 车牌号 |
| terminal_number | VARCHAR(50) | 终端编号 |
| device_serial | VARCHAR(100) | 设备序列号 |
| sim_serial | VARCHAR(100) | SIM卡序列号 |
| installed | TINYINT(1) | 是否已安装 |
| install_time | DATETIME | 安装时间 |
| engineer_id | INT | 安装工程师ID(外键) |
| engineer_name | VARCHAR(100) | 安装工程师姓名 |
| created_at | TIMESTAMP | 创建时间 |
| updated_at | TIMESTAMP | 更新时间 |

---

## 代码统计

### 文件数量
- PHP文件: 10个
- HTML文件: 2个
- CSS文件: 1个
- JavaScript文件: 4个
- 文档文件: 5个

### 代码行数(估算)
- PHP代码: ~1500行
- JavaScript代码: ~1200行
- CSS代码: ~1000行
- HTML代码: ~600行
- **总计**: ~4300行

---

## 技术要点

### 后端技术
- ✅ PDO预处理语句(防SQL注入)
- ✅ Session管理(用户认证)
- ✅ RESTful API设计
- ✅ JSON响应格式
- ✅ 密码哈希存储(password_hash)
- ✅ 权限验证中间件

### 前端技术
- ✅ 原生JavaScript(ES6+)
- ✅ Fetch API(异步请求)
- ✅ 百度地图API集成
- ✅ MarkerClusterer(点聚合)
- ✅ 响应式布局(Flexbox/Grid)
- ✅ CSS3动画效果
- ✅ 模态框组件
- ✅ 动态表单生成

### 安全特性
- ✅ Session认证
- ✅ SQL注入防护
- ✅ XSS防护
- ✅ 权限控制
- ✅ 密码加密存储

---

## 浏览器兼容性

| 浏览器 | 最低版本 | 状态 |
|--------|---------|------|
| Chrome | 60+ | ✅ 完全支持 |
| Firefox | 55+ | ✅ 完全支持 |
| Safari | 11+ | ✅ 完全支持 |
| Edge | 79+ | ✅ 完全支持 |
| IE | 11 | ⚠️ 部分支持 |

---

## 第三方依赖

### 必需依赖
- **百度地图API**: v3.0 (在线引入)
- **MarkerClusterer**: v1.2 (在线引入)

### 无需额外安装
- 所有代码使用原生PHP和JavaScript
- 无npm/composer依赖
- 无需构建工具

---

## 部署检查清单

在部署前,请确保以下文件已正确配置:

- [ ] `config/database.php` - 数据库连接信息
- [ ] `config/config.php` - 百度地图AK
- [ ] `index.html` - 第7行百度地图AK
- [ ] 运行 `install/install.php` 初始化数据库
- [ ] Apache已启用mod_rewrite模块
- [ ] 项目目录有正确的读写权限
- [ ] 修改了默认admin密码

---

**项目完整,开箱即用!** 🚀
