// 用户管理相关功能

// 加载用户列表
async function loadUserList() {
    const result = await apiRequest('/api/users.php');
    
    if (result.success) {
        displayUserList(result.users);
    } else {
        showMessage(result.message || '加载用户列表失败', 'error');
    }
}

// 显示用户列表
function displayUserList(users) {
    const tbody = document.getElementById('userTableBody');
    
    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: #999;">暂无用户数据</td></tr>';
        return;
    }
    
    tbody.innerHTML = users.map(user => {
        let actions = `<button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id}, '${user.username}')">删除</button>`;
        
        return `
            <tr>
                <td>${user.username}</td>
                <td>${user.real_name || '-'}</td>
                <td>${getUserTypeText(user.user_type)}</td>
                <td>${user.phone || '-'}</td>
                <td>${user.company || '-'}</td>
                <td>${formatDateTime(user.created_at)}</td>
                <td>${actions}</td>
            </tr>
        `;
    }).join('');
}

// 显示创建用户模态框
function showCreateUserModal() {
    document.getElementById('createUserForm').reset();
    openModal('createUserModal');
}

// 提交创建用户
async function submitCreateUser() {
    const form = document.getElementById('createUserForm');
    const formData = new FormData(form);
    
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    const result = await apiRequest('/api/users.php', 'POST', data);
    
    if (result.success) {
        showMessage('用户创建成功', 'success');
        closeModal('createUserModal');
        loadUserList();
    } else {
        showMessage(result.message || '创建用户失败', 'error');
    }
}

// 删除用户
async function deleteUser(userId, username) {
    if (!confirm(`确定要删除用户 "${username}" 吗?`)) {
        return;
    }
    
    const result = await apiRequest('/api/users.php', 'DELETE', {
        user_id: userId
    });
    
    if (result.success) {
        showMessage('用户删除成功', 'success');
        loadUserList();
    } else {
        showMessage(result.message || '删除用户失败', 'error');
    }
}

// 监听标签切换,加载用户列表
document.addEventListener('tabchange', (e) => {
    if (e.detail.tab === 'user-management') {
        loadUserList();
    }
});
