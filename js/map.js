// 百度地图相关功能

let map = null;
let markerClusterer = null;
let markers = [];
let orderMap = null;
let orderMarker = null;

// 初始化主地图
function initMap() {
    map = new BMap.Map("map-container");
    const point = new BMap.Point(116.404, 39.915); // 默认北京
    map.centerAndZoom(point, 5);
    map.enableScrollWheelZoom(true);
    map.addControl(new BMap.NavigationControl());
    map.addControl(new BMap.ScaleControl());
    
    // 加载地图数据
    loadMapData();
}

// 加载地图数据
async function loadMapData() {
    const companyName = document.getElementById('mapFilterCompany').value;
    const isShipped = document.getElementById('mapFilterShipped').value;
    const isReceived = document.getElementById('mapFilterReceived').value;
    
    const params = new URLSearchParams();
    if (companyName) params.append('company_name', companyName);
    if (isShipped !== '') params.append('is_shipped', isShipped);
    if (isReceived !== '') params.append('is_received', isReceived);
    
    const result = await apiRequest(`/api/map_data.php?${params.toString()}`);
    
    if (result.success) {
        displayMapMarkers(result.points);
    } else {
        showMessage(result.message || '加载地图数据失败', 'error');
    }
}

// 显示地图标记
function displayMapMarkers(points) {
    // 清除旧标记
    if (markerClusterer) {
        markerClusterer.clearMarkers();
    }
    markers.forEach(marker => map.removeOverlay(marker));
    markers = [];
    
    if (points.length === 0) {
        showMessage('没有找到符合条件的订单', 'info');
        return;
    }
    
    // 创建新标记
    points.forEach(point => {
        const bPoint = new BMap.Point(point.lng, point.lat);
        const marker = new BMap.Marker(bPoint);
        
        // 设置标记标签
        const label = new BMap.Label(
            `单位数量: ${point.companies.length}<br>总数量: ${point.total_quantity}<br>已安装: ${point.total_installed}`,
            { offset: new BMap.Size(10, -20) }
        );
        label.setStyle({
            border: '1px solid #667eea',
            padding: '5px',
            borderRadius: '5px',
            background: 'white',
            fontSize: '12px',
            whiteSpace: 'nowrap'
        });
        marker.setLabel(label);
        
        // 点击事件
        marker.addEventListener('click', () => {
            showMarkerInfo(point);
        });
        
        markers.push(marker);
    });
    
    // 使用点聚合
    markerClusterer = new BMapLib.MarkerClusterer(map, {
        markers: markers,
        girdSize: 80,
        styles: [{
            url: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTMiIGhlaWdodD0iNTMiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMjYuNSIgY3k9IjI2LjUiIHI9IjI2LjUiIGZpbGw9IiM2NjdlZWEiIG9wYWNpdHk9IjAuNyIvPjwvc3ZnPg==',
            size: new BMap.Size(53, 53),
            textColor: 'white',
            textSize: 14
        }]
    });
    
    // 自动调整视野
    if (points.length > 0) {
        const bPoints = points.map(p => new BMap.Point(p.lng, p.lat));
        map.setViewport(bPoints);
    }
}

// 显示标记详情
function showMarkerInfo(point) {
    let content = `
        <div class="info-window">
            <h3>位置: ${point.address}</h3>
            <p style="margin-bottom: 10px;">
                <strong>总订单数:</strong> ${point.companies.length} |
                <strong>总数量:</strong> ${point.total_quantity} |
                <strong>已安装:</strong> ${point.total_installed}
            </p>
    `;
    
    point.companies.forEach(company => {
        content += `
            <div class="company-item">
                <div>
                    <div class="company-name">${company.company_name}</div>
                    <div class="company-stats">
                        下单: ${company.purchase_quantity} | 
                        已装: ${company.installed_count}/${company.total_install_items}
                    </div>
                </div>
                <div>
                    <a class="btn-link" onclick="showOrderDetail(${company.order_id})">查看详情</a>
                </div>
            </div>
        `;
    });
    
    content += `</div>`;
    
    const infoWindow = new BMap.InfoWindow(content, {
        width: 300,
        title: '订单信息'
    });
    
    const bPoint = new BMap.Point(point.lng, point.lat);
    map.openInfoWindow(infoWindow, bPoint);
}

// 初始化订单创建地图
function initOrderMap() {
    if (!orderMap) {
        orderMap = new BMap.Map("order-map-container");
        const point = new BMap.Point(116.404, 39.915);
        orderMap.centerAndZoom(point, 12);
        orderMap.enableScrollWheelZoom(true);
        orderMap.addControl(new BMap.NavigationControl());
        
        // 点击地图设置位置
        orderMap.addEventListener('click', (e) => {
            setOrderLocation(e.point);
        });
        
        // 添加定位控件
        const geolocationControl = new BMap.GeolocationControl();
        geolocationControl.addEventListener('locationSuccess', (e) => {
            setOrderLocation(e.point);
        });
        orderMap.addControl(geolocationControl);
    }
}

// 设置订单位置
function setOrderLocation(point) {
    // 清除旧标记
    if (orderMarker) {
        orderMap.removeOverlay(orderMarker);
    }
    
    // 添加新标记
    orderMarker = new BMap.Marker(point);
    orderMap.addOverlay(orderMarker);
    orderMap.panTo(point);
    
    // 逆地理编码获取地址
    const gc = new BMap.Geocoder();
    gc.getLocation(point, (rs) => {
        const address = rs.address || '未知地址';
        document.getElementById('install_address').value = address;
        document.getElementById('install_latitude').value = point.lat;
        document.getElementById('install_longitude').value = point.lng;
    });
}

// 页面加载完成后初始化地图
document.addEventListener('DOMContentLoaded', () => {
    // 等待百度地图API加载完成
    if (typeof BMap !== 'undefined') {
        initMap();
    } else {
        window.onBMapReady = initMap;
    }
    
    // 监听标签切换,刷新地图
    document.addEventListener('tabchange', (e) => {
        if (e.detail.tab === 'map-view' && map) {
            setTimeout(() => {
                map.reset();
                loadMapData();
            }, 100);
        }
    });
});

// 引入MarkerClusterer库
(function() {
    const script = document.createElement('script');
    script.src = 'https://api.map.baidu.com/library/MarkerClusterer/1.2/src/MarkerClusterer_min.js';
    document.head.appendChild(script);
})();
