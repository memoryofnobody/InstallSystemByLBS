<?php
require_once __DIR__ . '/../config/config.php';

checkLogin();

$order_id = $_GET['order_id'] ?? '';
$company_name = $_GET['company_name'] ?? '';

if (empty($order_id) && empty($company_name)) {
    die('请指定订单ID或单位名称');
}

try {
    $pdo = getDbConnection();
    
    // 构建查询
    $where = [];
    $params = [];
    
    if (!empty($order_id)) {
        $where[] = "o.id = ?";
        $params[] = $order_id;
    }
    
    if (!empty($company_name)) {
        $where[] = "o.company_name = ?";
        $params[] = $company_name;
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $where);
    
    $sql = "
        SELECT 
            o.company_name,
            o.install_address,
            ir.vehicle_plate,
            ir.terminal_number,
            ir.device_serial,
            ir.sim_serial,
            ir.engineer_name,
            ir.install_time,
            ir.installed
        FROM orders o
        LEFT JOIN install_requirements ir ON o.id = ir.order_id
        $whereClause
        ORDER BY o.company_name, ir.installed DESC, ir.vehicle_plate
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    // 设置CSV文件头
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="install_list_' . date('YmdHis') . '.csv"');
    
    // 输出BOM头以支持Excel正确显示中文
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    
    // 写入表头
    fputcsv($output, ['单位名称', '安装地址', '车牌号', '终端编号', '设备序列号', 'SIM卡序列号', '安装工程师', '安装时间', '安装状态']);
    
    // 写入数据
    foreach ($results as $row) {
        fputcsv($output, [
            $row['company_name'],
            $row['install_address'],
            $row['vehicle_plate'],
            $row['terminal_number'],
            $row['device_serial'] ?? '',
            $row['sim_serial'] ?? '',
            $row['engineer_name'] ?? '',
            $row['install_time'] ?? '',
            $row['installed'] ? '已安装' : '未安装'
        ]);
    }
    
    fclose($output);
    exit;
    
} catch (Exception $e) {
    die('导出失败: ' . $e->getMessage());
}
?>
