# GitHub推送指南

## 方法一: 使用Git命令行 (推荐)

### 前置准备

#### 1. 安装Git

**Windows系统:**
- 下载Git: https://git-scm.com/download/win
- 运行安装程序,使用默认设置即可
- 安装完成后重启命令行

**验证安装:**
```bash
git --version
```

#### 2. 配置Git

首次使用需要配置用户信息:
```bash
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

#### 3. 创建GitHub仓库

1. 登录GitHub: https://github.com
2. 点击右上角 "+" -> "New repository"
3. 填写仓库信息:
   - **Repository name**: `order-map-system` (或其他名称)
   - **Description**: 基于百度地图API的订单管理系统
   - **Public/Private**: 选择公开或私有
   - ❌ **不要勾选** "Initialize this repository with a README"
4. 点击 "Create repository"
5. 复制仓库地址 (HTTPS或SSH)

### 推送步骤

打开命令行(CMD或PowerShell),执行以下命令:

```bash
# 1. 进入项目目录
cd c:/Users/memor/CodeBuddy/20260121230754

# 2. 初始化Git仓库
git init

# 3. 添加所有文件到暂存区
git add .

# 4. 提交代码
git commit -m "Initial commit: 订单地图管理系统完整代码"

# 5. 添加远程仓库 (替换为你的仓库地址)
git remote add origin https://github.com/YOUR_USERNAME/order-map-system.git

# 6. 推送到GitHub
git push -u origin master
```

如果使用main分支:
```bash
git branch -M main
git push -u origin main
```

### 可能遇到的问题

#### 问题1: 推送时要求输入账号密码

**解决方法:**
GitHub已不支持密码认证,需要使用Personal Access Token:

1. 登录GitHub
2. 点击头像 -> Settings
3. 左侧菜单 -> Developer settings -> Personal access tokens -> Tokens (classic)
4. 点击 "Generate new token"
5. 设置Token权限,至少勾选 `repo`
6. 生成并复制Token
7. 推送时使用Token作为密码

#### 问题2: 推送被拒绝 (rejected)

```bash
# 强制推送 (首次推送可用)
git push -u origin master --force
```

#### 问题3: 中文文件名显示乱码

```bash
git config --global core.quotepath false
```

---

## 方法二: 使用GitHub Desktop (图形化界面)

### 1. 安装GitHub Desktop

- 下载: https://desktop.github.com/
- 安装并登录GitHub账号

### 2. 添加本地仓库

1. 打开GitHub Desktop
2. 点击 "File" -> "Add local repository"
3. 选择项目目录: `c:/Users/memor/CodeBuddy/20260121230754`
4. 如果提示"不是Git仓库",点击 "create a repository"

### 3. 提交代码

1. 在左侧查看变更文件列表
2. 在底部输入提交信息: "Initial commit: 订单地图管理系统"
3. 点击 "Commit to master"

### 4. 推送到GitHub

1. 点击顶部 "Publish repository"
2. 填写仓库名称和描述
3. 选择公开或私有
4. 点击 "Publish repository"

---

## 方法三: 直接在GitHub网页上传

### 适用场景
- 不想安装Git
- 只是简单上传代码

### 步骤

1. 在GitHub创建新仓库 (同方法一步骤3)

2. 进入仓库页面,点击 "uploading an existing file"

3. 将项目文件夹拖拽到网页上传区域
   - 或点击 "choose your files" 选择文件

4. 填写提交信息

5. 点击 "Commit changes"

**注意**: 
- 网页上传有文件大小和数量限制
- 不能上传空文件夹
- 首次可能需要多次上传

---

## 推荐的 .gitignore 文件

在推送前,建议创建 `.gitignore` 文件排除不必要的文件:

```gitignore
# 日志文件
*.log
logs/

# 临时文件
*.tmp
*.temp
*.cache

# 上传文件目录
uploads/*
!uploads/.gitkeep

# 操作系统
.DS_Store
Thumbs.db

# IDE
.vscode/
.idea/
*.sublime-*

# 敏感配置 (可选)
# config/database.php
# config/config.php
```

---

## 推送后的维护

### 更新代码到GitHub

```bash
# 1. 查看修改状态
git status

# 2. 添加修改的文件
git add .

# 3. 提交
git commit -m "描述你的修改"

# 4. 推送
git push
```

### 从GitHub拉取最新代码

```bash
git pull
```

### 查看提交历史

```bash
git log
```

### 创建分支

```bash
# 创建并切换到新分支
git checkout -b dev

# 推送新分支
git push -u origin dev
```

---

## 完整的推送命令 (复制即用)

**请替换 `YOUR_USERNAME` 和 `REPO_NAME` 为你的实际信息**

```bash
cd c:/Users/memor/CodeBuddy/20260121230754
git init
git add .
git commit -m "Initial commit: 订单地图管理系统完整实现

功能特性:
- 多角色用户系统(管理员/买家/供应商/安装工程师)
- 百度地图集成(标记点聚合、信息窗口)
- 订单全流程管理(创建/发货/收货/安装)
- 地图和列表双视图
- 多条件筛选搜索
- CSV数据导出

技术栈: PHP + MySQL + JavaScript + 百度地图API"

git remote add origin https://github.com/YOUR_USERNAME/REPO_NAME.git
git branch -M main
git push -u origin main
```

---

## 建议的README.md优化

推送到GitHub后,README.md会自动显示在仓库首页。
建议添加以下内容让项目更专业:

### 添加徽章 (Badges)

在README.md顶部添加:

```markdown
![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)
```

### 添加截图

在项目根目录创建 `screenshots/` 文件夹,放入系统截图,然后在README中引用:

```markdown
## 系统截图

### 登录页面
![登录页面](screenshots/login.png)

### 地图视图
![地图视图](screenshots/map-view.png)

### 订单管理
![订单管理](screenshots/order-list.png)
```

### 添加在线演示

如果部署了在线版本:

```markdown
## 在线演示

🔗 [点击访问在线演示](https://your-demo-url.com)

测试账号:
- 管理员: admin / admin
- 买家: buyer / 123456
- 供应商: supplier / 123456
```

---

## 安全提示

### ⚠️ 重要: 不要推送敏感信息

在推送前,检查以下文件:

1. **数据库密码**: `config/database.php`
   - 建议使用环境变量或配置模板

2. **百度地图API密钥**: `config/config.php` 和 `index.html`
   - 如果是私有仓库可以保留
   - 公开仓库建议使用环境变量

3. **示例配置文件**:
   ```bash
   # 提交配置模板而不是实际配置
   git rm --cached config/config.php
   git rm --cached config/database.php
   ```
   
   然后只保留 `config.example.php`

### 使用环境变量

创建 `config/config.local.php`:
```php
<?php
// 本地配置文件,不提交到GitHub
define('DB_PASS', 'your_actual_password');
define('BAIDU_MAP_AK', 'your_actual_api_key');
?>
```

在 `.gitignore` 中添加:
```gitignore
config/config.local.php
```

---

## 推送清单

推送前检查:

- [ ] 已安装Git
- [ ] 已配置Git用户信息
- [ ] 已在GitHub创建仓库
- [ ] 已创建 `.gitignore` 文件
- [ ] 已检查敏感信息
- [ ] 已测试代码可正常运行
- [ ] README.md 描述清晰
- [ ] 已添加开源协议 (可选)

---

## 获取帮助

- Git官方文档: https://git-scm.com/doc
- GitHub帮助: https://docs.github.com/
- Git教程: https://www.liaoxuefeng.com/wiki/896043488029600

---

**祝推送成功!** 🚀

如有问题,请参考上述文档或搜索相关错误信息。
