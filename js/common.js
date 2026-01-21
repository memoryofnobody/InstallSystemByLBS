// 通用JavaScript函数

// 显示消息提示
function showMessage(message, type = 'info') {
    const messageEl = document.createElement('div');
    messageEl.className = `message message-${type}`;
    messageEl.textContent = message;
    document.body.appendChild(messageEl);
    
    setTimeout(() => {
        messageEl.remove();
    }, 3000);
}

// API请求封装
async function apiRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    if (data && (method === 'POST' || method === 'PUT' || method === 'DELETE')) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        const result = await response.json();
        
        if (result.redirect) {
            window.location.href = result.redirect;
        }
        
        return result;
    } catch (error) {
        console.error('API请求失败:', error);
        showMessage('网络请求失败', 'error');
        throw error;
    }
}

// 检查登录状态
async function checkLogin() {
    const result = await apiRequest('/api/current_user.php');
    if (!result.success) {
        window.location.href = '/login.html';
        return null;
    }
    return result.user;
}

// 退出登录
async function logout() {
    const result = await apiRequest('/api/logout.php', 'POST');
    if (result.success) {
        window.location.href = '/login.html';
    }
}

// 标签页切换
function initTabs() {
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-tab');
            
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(tc => tc.classList.remove('active'));
            
            tab.classList.add('active');
            document.getElementById(target).classList.add('active');
            
            // 触发标签切换事件
            const event = new CustomEvent('tabchange', { detail: { tab: target } });
            document.dispatchEvent(event);
        });
    });
}

// 模态框控制
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// 初始化模态框关闭按钮
function initModals() {
    const closeBtns = document.querySelectorAll('.close-btn');
    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.modal');
            if (modal) {
                modal.classList.remove('active');
            }
        });
    });
    
    // 点击模态框背景关闭
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
}

// 格式化日期时间
function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleString('zh-CN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// 导出CSV
function exportToCSV(orderId = '', companyName = '') {
    let url = '/api/export.php?';
    const params = [];
    
    if (orderId) params.push(`order_id=${orderId}`);
    if (companyName) params.push(`company_name=${encodeURIComponent(companyName)}`);
    
    url += params.join('&');
    window.open(url, '_blank');
}

// 获取订单状态标签
function getOrderStatusBadges(order) {
    const badges = [];
    
    // 发货状态
    if (order.is_shipped == 1) {
        badges.push('<span class="badge badge-success">已发货</span>');
    } else {
        badges.push('<span class="badge badge-warning">未发货</span>');
    }
    
    // 收货状态
    if (order.is_received == 1) {
        badges.push('<span class="badge badge-success">已收货</span>');
    } else {
        badges.push('<span class="badge badge-danger">未收货</span>');
    }
    
    // 安装状态
    const installedCount = parseInt(order.installed_count) || 0;
    const totalInstallItems = parseInt(order.total_install_items) || 0;
    
    if (totalInstallItems === 0) {
        badges.push('<span class="badge badge-info">无安装需求</span>');
    } else if (installedCount === 0) {
        badges.push('<span class="badge badge-danger">未安装</span>');
    } else if (installedCount < totalInstallItems) {
        badges.push(`<span class="badge badge-warning">部分安装(${installedCount}/${totalInstallItems})</span>`);
    } else {
        badges.push('<span class="badge badge-success">全部安装</span>');
    }
    
    return badges.join(' ');
}

// 用户类型中文显示
function getUserTypeText(userType) {
    const types = {
        'admin': '管理员',
        'buyer': '买家',
        'supplier': '供应商',
        'engineer': '安装工程师'
    };
    return types[userType] || userType;
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', () => {
    initTabs();
    initModals();
});
