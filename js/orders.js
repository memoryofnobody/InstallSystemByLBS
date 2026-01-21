// 订单管理相关功能

let currentUser = null;
let currentOrders = [];

// 初始化
(async () => {
    currentUser = await checkLogin();
    if (currentUser) {
        document.getElementById('userName').textContent = currentUser.real_name || currentUser.username;
        document.getElementById('userType').textContent = getUserTypeText(currentUser.user_type);
        
        // 显示用户管理标签(仅管理员)
        if (currentUser.user_type === 'admin') {
            document.getElementById('userManagementTab').style.display = 'block';
        }
        
        // 显示创建订单按钮(管理员和买家)
        if (currentUser.user_type === 'admin' || currentUser.user_type === 'buyer') {
            document.getElementById('createOrderBtn').style.display = 'block';
        }
        
        // 加载订单列表
        loadOrderList();
    }
})();

// 加载订单列表
async function loadOrderList() {
    const companyName = document.getElementById('listFilterCompany').value;
    const isShipped = document.getElementById('listFilterShipped').value;
    const isReceived = document.getElementById('listFilterReceived').value;
    const installStatus = document.getElementById('listFilterInstall').value;
    
    const params = new URLSearchParams();
    if (companyName) params.append('company_name', companyName);
    if (isShipped !== '') params.append('is_shipped', isShipped);
    if (isReceived !== '') params.append('is_received', isReceived);
    if (installStatus) params.append('install_status', installStatus);
    
    const result = await apiRequest(`/api/orders.php?${params.toString()}`);
    
    if (result.success) {
        currentOrders = result.orders;
        displayOrderList(result.orders);
    } else {
        showMessage(result.message || '加载订单列表失败', 'error');
    }
}

// 显示订单列表
function displayOrderList(orders) {
    const tbody = document.getElementById('orderTableBody');
    
    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: #999;">暂无订单数据</td></tr>';
        return;
    }
    
    tbody.innerHTML = orders.map(order => {
        const installedCount = parseInt(order.installed_count) || 0;
        const totalInstallItems = parseInt(order.total_install_items) || 0;
        
        let actions = `<button class="btn btn-primary btn-sm" onclick="showOrderDetail(${order.id})">查看详情</button>`;
        
        // 供应商可以录入发货信息
        if (currentUser.user_type === 'supplier' || currentUser.user_type === 'admin') {
            actions += ` <button class="btn btn-success btn-sm" onclick="showShippingModal(${order.id})">发货</button>`;
        }
        
        // 安装工程师可以录入安装信息
        if (currentUser.user_type === 'engineer' || currentUser.user_type === 'admin') {
            actions += ` <button class="btn btn-success btn-sm" onclick="showInstallModal(${order.id})">安装</button>`;
        }
        
        // 导出功能
        actions += ` <button class="btn btn-secondary btn-sm" onclick="exportToCSV('${order.id}', '')">导出</button>`;
        
        return `
            <tr>
                <td>${order.id}</td>
                <td>${formatDateTime(order.created_at)}</td>
                <td>${order.company_name}</td>
                <td>${order.purchase_quantity}</td>
                <td>${installedCount}/${totalInstallItems}</td>
                <td>${order.install_address || '-'}</td>
                <td>${getOrderStatusBadges(order)}</td>
                <td>${actions}</td>
            </tr>
        `;
    }).join('');
}

// 显示创建订单模态框
function showCreateOrderModal() {
    document.getElementById('createOrderForm').reset();
    document.getElementById('installRequirements').innerHTML = '';
    addInstallRequirement();
    addInstallRequirement();
    
    openModal('createOrderModal');
    
    // 初始化订单地图
    setTimeout(() => {
        initOrderMap();
    }, 300);
}

// 添加安装需求项
function addInstallRequirement() {
    const container = document.getElementById('installRequirements');
    const index = container.children.length;
    
    const div = document.createElement('div');
    div.className = 'form-array-item';
    div.innerHTML = `
        <div class="form-group">
            <label>车牌号</label>
            <input type="text" name="vehicle_plate_${index}" placeholder="如: 京A12345">
        </div>
        <div class="form-group">
            <label>终端编号</label>
            <input type="text" name="terminal_number_${index}" placeholder="终端编号">
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">删除</button>
    `;
    
    container.appendChild(div);
}

// 提交创建订单
async function submitCreateOrder() {
    const form = document.getElementById('createOrderForm');
    const formData = new FormData(form);
    
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    // 收集安装需求清单
    const installRequirements = [];
    const container = document.getElementById('installRequirements');
    const items = container.querySelectorAll('.form-array-item');
    
    items.forEach((item, index) => {
        const vehiclePlate = item.querySelector(`input[name="vehicle_plate_${index}"]`).value;
        const terminalNumber = item.querySelector(`input[name="terminal_number_${index}"]`).value;
        
        if (vehiclePlate && terminalNumber) {
            installRequirements.push({
                vehicle_plate: vehiclePlate,
                terminal_number: terminalNumber
            });
        }
    });
    
    data.install_requirements = installRequirements;
    
    const result = await apiRequest('/api/orders.php', 'POST', data);
    
    if (result.success) {
        showMessage('订单创建成功', 'success');
        closeModal('createOrderModal');
        loadOrderList();
        loadMapData();
    } else {
        showMessage(result.message || '创建订单失败', 'error');
    }
}

// 显示订单详情
async function showOrderDetail(orderId) {
    const order = currentOrders.find(o => o.id == orderId);
    
    if (!order) {
        showMessage('订单不存在', 'error');
        return;
    }
    
    const installedCount = parseInt(order.installed_count) || 0;
    const totalInstallItems = parseInt(order.total_install_items) || 0;
    
    let detailHtml = `
        <div style="line-height: 1.8;">
            <h3 style="margin-bottom: 15px;">基本信息</h3>
            <p><strong>订单号:</strong> ${order.id}</p>
            <p><strong>下单时间:</strong> ${formatDateTime(order.created_at)}</p>
            <p><strong>单位名称:</strong> ${order.company_name}</p>
            <p><strong>购买数量:</strong> ${order.purchase_quantity}</p>
            <p><strong>安装数量:</strong> ${order.install_quantity}</p>
            <p><strong>安装地址:</strong> ${order.install_address || '-'}</p>
            
            <h3 style="margin-top: 20px; margin-bottom: 15px;">联系信息</h3>
            <p><strong>下单人:</strong> ${order.order_contact || '-'} ${order.order_phone || ''}</p>
            <p><strong>收货人:</strong> ${order.receiver_name || '-'} ${order.receiver_phone || ''}</p>
            <p><strong>收货地址:</strong> ${order.receiver_address || '-'}</p>
            <p><strong>安装对接人:</strong> ${order.install_contact || '-'} ${order.install_phone || ''}</p>
            
            <h3 style="margin-top: 20px; margin-bottom: 15px;">发货收货信息</h3>
            <p><strong>发货状态:</strong> ${order.is_shipped == 1 ? '<span class="badge badge-success">已发货</span>' : '<span class="badge badge-warning">未发货</span>'}</p>
            <p><strong>物流单号:</strong> ${order.tracking_number || '-'}</p>
            <p><strong>收货状态:</strong> ${order.is_received == 1 ? '<span class="badge badge-success">已收货</span>' : '<span class="badge badge-danger">未收货</span>'}</p>
            <p><strong>收货数量:</strong> ${order.received_quantity || 0}</p>
            
            <h3 style="margin-top: 20px; margin-bottom: 15px;">安装详情 (${installedCount}/${totalInstallItems})</h3>
    `;
    
    if (order.install_details && order.install_details.length > 0) {
        detailHtml += `
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 8px; border: 1px solid #ddd;">车牌号</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">终端编号</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">设备序号</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">SIM卡序号</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">安装工程师</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">安装时间</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">状态</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        order.install_details.forEach(detail => {
            detailHtml += `
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;">${detail.vehicle_plate}</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">${detail.terminal_number}</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">${detail.device_serial || '-'}</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">${detail.sim_serial || '-'}</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">${detail.engineer_name || '-'}</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">${formatDateTime(detail.install_time)}</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">
                        ${detail.installed == 1 ? '<span class="badge badge-success">已安装</span>' : '<span class="badge badge-warning">未安装</span>'}
                    </td>
                </tr>
            `;
        });
        
        detailHtml += `
                </tbody>
            </table>
        `;
    } else {
        detailHtml += '<p style="color: #999;">暂无安装需求清单</p>';
    }
    
    if (order.remarks) {
        detailHtml += `
            <h3 style="margin-top: 20px; margin-bottom: 15px;">备注</h3>
            <p>${order.remarks}</p>
        `;
    }
    
    detailHtml += '</div>';
    
    document.getElementById('orderDetailContent').innerHTML = detailHtml;
    openModal('orderDetailModal');
}

// 显示发货模态框
function showShippingModal(orderId) {
    const order = currentOrders.find(o => o.id == orderId);
    
    if (!order) {
        showMessage('订单不存在', 'error');
        return;
    }
    
    const modalHtml = `
        <div id="shippingModal" class="modal active">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>录入发货信息 - 订单 #${orderId}</h2>
                    <button class="close-btn" onclick="closeModal('shippingModal'); this.closest('.modal').remove();">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="shippingForm">
                        <div class="form-group">
                            <label>是否发货</label>
                            <select id="is_shipped" required>
                                <option value="1" ${order.is_shipped == 1 ? 'selected' : ''}>已发货</option>
                                <option value="0" ${order.is_shipped == 0 ? 'selected' : ''}>未发货</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>物流单号</label>
                            <input type="text" id="tracking_number" value="${order.tracking_number || ''}">
                        </div>
                        <div class="form-group">
                            <label>是否收货</label>
                            <select id="is_received">
                                <option value="1" ${order.is_received == 1 ? 'selected' : ''}>已收货</option>
                                <option value="0" ${order.is_received == 0 ? 'selected' : ''}>未收货</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>收货数量</label>
                            <input type="number" id="received_quantity" value="${order.received_quantity || 0}" min="0">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeModal('shippingModal'); this.closest('.modal').remove();">取消</button>
                    <button class="btn btn-primary" onclick="submitShipping(${orderId})">保存</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

// 提交发货信息
async function submitShipping(orderId) {
    const data = {
        order_id: orderId,
        is_shipped: document.getElementById('is_shipped').value,
        tracking_number: document.getElementById('tracking_number').value,
        is_received: document.getElementById('is_received').value,
        received_quantity: document.getElementById('received_quantity').value
    };
    
    const result = await apiRequest('/api/orders.php', 'PUT', data);
    
    if (result.success) {
        showMessage('发货信息更新成功', 'success');
        document.getElementById('shippingModal').remove();
        loadOrderList();
        loadMapData();
    } else {
        showMessage(result.message || '更新失败', 'error');
    }
}

// 显示安装模态框
function showInstallModal(orderId) {
    const order = currentOrders.find(o => o.id == orderId);
    
    if (!order) {
        showMessage('订单不存在', 'error');
        return;
    }
    
    if (!order.install_details || order.install_details.length === 0) {
        showMessage('该订单没有安装需求清单', 'info');
        return;
    }
    
    let installItemsHtml = '';
    
    order.install_details.forEach((detail, index) => {
        installItemsHtml += `
            <div class="form-array-item" style="border-bottom: 1px solid #e0e0e0; padding-bottom: 15px; margin-bottom: 15px;">
                <input type="hidden" id="req_id_${index}" value="${detail.id}">
                <div class="form-group">
                    <label>车牌号</label>
                    <input type="text" value="${detail.vehicle_plate}" readonly>
                </div>
                <div class="form-group">
                    <label>终端编号</label>
                    <input type="text" value="${detail.terminal_number}" readonly>
                </div>
                <div class="form-group">
                    <label>设备序列号</label>
                    <input type="text" id="device_serial_${index}" value="${detail.device_serial || ''}">
                </div>
                <div class="form-group">
                    <label>SIM卡序列号</label>
                    <input type="text" id="sim_serial_${index}" value="${detail.sim_serial || ''}">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="installed_${index}" ${detail.installed == 1 ? 'checked' : ''}>
                        已安装
                    </label>
                </div>
            </div>
        `;
    });
    
    const modalHtml = `
        <div id="installModal" class="modal active">
            <div class="modal-content" style="max-width: 900px;">
                <div class="modal-header">
                    <h2>录入安装信息 - 订单 #${orderId}</h2>
                    <button class="close-btn" onclick="closeModal('installModal'); this.closest('.modal').remove();">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="installItems">
                        ${installItemsHtml}
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeModal('installModal'); this.closest('.modal').remove();">取消</button>
                    <button class="btn btn-primary" onclick="submitInstall(${orderId}, ${order.install_details.length})">保存</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

// 提交安装信息
async function submitInstall(orderId, itemCount) {
    const installItems = [];
    
    for (let i = 0; i < itemCount; i++) {
        const reqId = document.getElementById(`req_id_${i}`).value;
        const deviceSerial = document.getElementById(`device_serial_${i}`).value;
        const simSerial = document.getElementById(`sim_serial_${i}`).value;
        const installed = document.getElementById(`installed_${i}`).checked ? 1 : 0;
        
        installItems.push({
            requirement_id: reqId,
            device_serial: deviceSerial,
            sim_serial: simSerial,
            installed: installed
        });
    }
    
    const result = await apiRequest('/api/install_requirements.php', 'PUT', {
        install_items: installItems
    });
    
    if (result.success) {
        showMessage('安装信息更新成功', 'success');
        document.getElementById('installModal').remove();
        loadOrderList();
        loadMapData();
    } else {
        showMessage(result.message || '更新失败', 'error');
    }
}
