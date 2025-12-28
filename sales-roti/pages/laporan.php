<?php
/**
 * Laporan (Report) Page - READ ONLY
 * Display sales reports and analytics with database-driven data
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/koneksi.php';

// For JSON endpoint, check session but don't redirect - return JSON error instead
$is_json_request = isset($_GET['fetch']) && $_GET['fetch'] === 'json';

if (!isset($_SESSION['user_id'])) {
    if ($is_json_request) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized - please login']);
        exit;
    } else {
        header('Location: login.php');
        exit;
    }
}

$current_user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'sales';
$bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : date('n');
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

// Total Pesanan (admin: all, sales: current user)
if ($user_role === 'admin') {
    $order_query = "SELECT COUNT(*) as total FROM pesanan";
    $order_stmt = $koneksi->prepare($order_query);
} else {
    $order_query = "SELECT COUNT(*) as total FROM pesanan WHERE idUser = ?";
    $order_stmt = $koneksi->prepare($order_query);
    $order_stmt->bind_param('s', $current_user_id);
}
$order_stmt->execute();
$order_result = $order_stmt->get_result()->fetch_assoc();
$order_total = $order_result['total'] ?? 0;
$order_stmt->close();

// Total Pengiriman
if ($user_role === 'admin') {
    $delivery_query = "SELECT COUNT(*) as total FROM pengiriman";
    $delivery_stmt = $koneksi->prepare($delivery_query);
} else {
    $delivery_query = "SELECT COUNT(*) as total FROM pengiriman WHERE idUser = ?";
    $delivery_stmt = $koneksi->prepare($delivery_query);
    $delivery_stmt->bind_param('s', $current_user_id);
}
$delivery_stmt->execute();
$delivery_total = $delivery_stmt->get_result()->fetch_assoc()['total'];
$delivery_stmt->close();

// Total Distributor
if ($user_role === 'admin') {
    $distributor_query = "SELECT COUNT(DISTINCT d.noDistributor) as total FROM distributor d 
                        JOIN pesanan p ON d.noDistributor = p.noDistributor";
    $distributor_stmt = $koneksi->prepare($distributor_query);
} else {
    $distributor_query = "SELECT COUNT(DISTINCT d.noDistributor) as total FROM distributor d 
                        JOIN pesanan p ON d.noDistributor = p.noDistributor 
                        WHERE p.idUser = ?";
    $distributor_stmt = $koneksi->prepare($distributor_query);
    $distributor_stmt->bind_param('s', $current_user_id);
}
$distributor_stmt->execute();
$distributor_total = $distributor_stmt->get_result()->fetch_assoc()['total'];
$distributor_stmt->close();

// Total Revenue
if ($user_role === 'admin') {
    $revenue_query = "SELECT SUM(dp.totalHarga) as total_revenue FROM detail_pesanan dp 
                     JOIN pesanan p ON dp.noPesanan = p.noPesanan";
    $revenue_stmt = $koneksi->prepare($revenue_query);
} else {
    $revenue_query = "SELECT SUM(dp.totalHarga) as total_revenue FROM detail_pesanan dp 
                     JOIN pesanan p ON dp.noPesanan = p.noPesanan 
                     WHERE p.idUser = ?";
    $revenue_stmt = $koneksi->prepare($revenue_query);
    $revenue_stmt->bind_param('s', $current_user_id);
}
$revenue_stmt->execute();
$revenue_row = $revenue_stmt->get_result()->fetch_assoc();
$total_revenue = $revenue_row['total_revenue'] ?? 0;
$revenue_stmt->close();

// Top Distributors Report
if ($user_role === 'admin') {
    $top_dist_query = "SELECT d.namaDistributor, COUNT(p.noPesanan) as order_count, SUM(dp.totalHarga) as revenue
                      FROM distributor d 
                      JOIN pesanan p ON d.noDistributor = p.noDistributor 
                      LEFT JOIN detail_pesanan dp ON p.noPesanan = dp.noPesanan 
                      GROUP BY d.namaDistributor 
                      ORDER BY revenue DESC LIMIT 10";
    $top_dist_stmt = $koneksi->prepare($top_dist_query);
} else {
    $top_dist_query = "SELECT d.namaDistributor, COUNT(p.noPesanan) as order_count, SUM(dp.totalHarga) as revenue
                      FROM distributor d 
                      JOIN pesanan p ON d.noDistributor = p.noDistributor 
                      LEFT JOIN detail_pesanan dp ON p.noPesanan = dp.noPesanan 
                      WHERE p.idUser = ? 
                      GROUP BY d.namaDistributor 
                      ORDER BY revenue DESC LIMIT 10";
    $top_dist_stmt = $koneksi->prepare($top_dist_query);
    $top_dist_stmt->bind_param('s', $current_user_id);
}
$top_dist_stmt->execute();
$top_distributors = $top_dist_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$top_dist_stmt->close();

// Find max revenue for progress bar calculation
$max_revenue = 0;
foreach ($top_distributors as $td) {
    if ($td['revenue'] > $max_revenue) {
        $max_revenue = $td['revenue'];
    }
}

// Daily Sales Chart Data
$chart_start_date = date('Y-m-01', mktime(0, 0, 0, $bulan, 1, $tahun));
$chart_end_date = date('Y-m-t', mktime(0, 0, 0, $bulan, 1, $tahun));

if ($user_role === 'admin') {
    $chart_query = "SELECT DATE(p.tanggalOrder) as tanggal, SUM(dp.totalHarga) as daily_revenue, COUNT(p.noPesanan) as daily_orders
                   FROM pesanan p 
                   LEFT JOIN detail_pesanan dp ON p.noPesanan = dp.noPesanan 
                   WHERE DATE(p.tanggalOrder) BETWEEN ? AND ?
                   GROUP BY DATE(p.tanggalOrder)
                   ORDER BY tanggal ASC";
    $chart_stmt = $koneksi->prepare($chart_query);
    $chart_stmt->bind_param('ss', $chart_start_date, $chart_end_date);
} else {
    $chart_query = "SELECT DATE(p.tanggalOrder) as tanggal, SUM(dp.totalHarga) as daily_revenue, COUNT(p.noPesanan) as daily_orders
                   FROM pesanan p 
                   LEFT JOIN detail_pesanan dp ON p.noPesanan = dp.noPesanan 
                   WHERE p.idUser = ? AND DATE(p.tanggalOrder) BETWEEN ? AND ?
                   GROUP BY DATE(p.tanggalOrder)
                   ORDER BY tanggal ASC";
    $chart_stmt = $koneksi->prepare($chart_query);
    $chart_stmt->bind_param('sss', $current_user_id, $chart_start_date, $chart_end_date);
}
$chart_stmt->execute();
$chart_data_result = $chart_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$chart_stmt->close();

// Format chart data for Chart.js
$chart_labels = [];
$chart_revenue = [];
$chart_orders = [];

// Create all days in month with 0 values
$days_in_month = date('t', mktime(0, 0, 0, $bulan, 1, $tahun));
for ($d = 1; $d <= $days_in_month; $d++) {
    $chart_labels[] = $d;
    $chart_revenue[] = 0;
    $chart_orders[] = 0;
}

// Fill with actual data
foreach ($chart_data_result as $row) {
    $day = intval(date('d', strtotime($row['tanggal'])));
    $chart_revenue[$day - 1] = floatval($row['daily_revenue'] ?? 0);
    $chart_orders[$day - 1] = intval($row['daily_orders'] ?? 0);
}

// Handle export request
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Laporan_Penjualan_' . date('Y-m-d_H-i-s') . '.xls"');
    
    echo "LAPORAN PENJUALAN\n";
    echo "Bulan/Tahun: " . date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)) . "\n";
    echo "Tanggal Export: " . date('d-m-Y H:i:s') . "\n";
    echo "\n\n";
    
    echo "RINGKASAN\n";
    echo "Total Revenue\tRp " . number_format($total_revenue, 0, ',', '.') . "\n";
    echo "Total Order\t" . $order_total . "\n";
    echo "Total Pengiriman\t" . $delivery_total . "\n";
    echo "Distributor Aktif\t" . $distributor_total . "\n";
    echo "\n\n";
    
    echo "TOP DISTRIBUTOR PENJUALAN\n";
    echo "Rank\tNama Distributor\tTotal Order\tTotal Revenue\n";
    foreach ($top_distributors as $index => $td) {
        echo ($index + 1) . "\t" . htmlspecialchars($td['namaDistributor']) . "\t" . $td['order_count'] . "\tRp " . number_format($td['revenue'] ?? 0, 0, ',', '.') . "\n";
    }
    
    exit;
}
?>

<div class="page-header">
    <h2>Laporan Penjualan</h2>
    <p class="page-subtitle">Analisis dan laporan penjualan</p>
</div>

<!-- Report Filters -->
<div class="filter-section">
    <div class="filter-group">
        <label>Filter Bulan & Tahun:</label>
        <div class="filter-inputs" style="display: flex; gap: 10px; align-items: center;">
            <select id="bulanFilter" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo $m; ?>" <?php echo $m == $bulan ? 'selected' : ''; ?>>
                        <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                    </option>
                <?php endfor; ?>
            </select>
            <select id="tahunFilter" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <?php for ($y = date('Y') - 2; $y <= date('Y'); $y++): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $tahun ? 'selected' : ''; ?>>
                        <?php echo $y; ?>
                    </option>
                <?php endfor; ?>
            </select>
            <button class="btn btn-secondary" onclick="applyFilter()">Filter</button>
            <button class="btn btn-outline" onclick="resetFilter()">Reset</button>
            <button class="btn btn-success" onclick="exportToExcel()" title="Export to Excel">📥 Export Excel</button>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="report-summary">
    <div class="summary-card summary-large">
        <div class="summary-icon" style="color: #4a90e2;">💰</div>
        <div class="summary-content">
            <p class="summary-label">Total Revenue</p>
            <p class="summary-value">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></p>
            <p class="summary-change"><?php echo date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)); ?></p>
        </div>
    </div>
    
    <div class="summary-card summary-large">
        <div class="summary-icon" style="color: #27ae60;">📦</div>
        <div class="summary-content">
            <p class="summary-label">Total Order</p>
            <p class="summary-value"><?php echo $order_total; ?> pesanan</p>
            <p class="summary-change"><?php echo date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)); ?></p>
        </div>
    </div>
    
    <div class="summary-card summary-large">
        <div class="summary-icon" style="color: #f39c12;">📮</div>
        <div class="summary-content">
            <p class="summary-label">Total Pengiriman</p>
            <p class="summary-value"><?php echo $delivery_total; ?> pengiriman</p>
            <p class="summary-change"><?php $rate = $order_total > 0 ? round(($delivery_total / $order_total) * 100) : 0; echo $rate; ?>% dari order</p>
        </div>
    </div>
    
    <div class="summary-card summary-large">
        <div class="summary-icon" style="color: #9b59b6;">👥</div>
        <div class="summary-content">
            <p class="summary-label">Distributor Aktif</p>
            <p class="summary-value"><?php echo $distributor_total; ?> distributor</p>
            <p class="summary-change"><?php echo date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)); ?></p>
        </div>
    </div>
</div>

<!-- Sales Chart -->
<div class="report-section">
    <h3>📊 Grafik Penjualan - <?php echo date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)); ?></h3>
    <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
        <canvas id="salesChart" style="max-height: 400px;"></canvas>
    </div>
</div>

<!-- Top Distributors Report -->
<div class="report-section">
    <h3>Top Distributor Penjualan</h3>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 5%;">Rank</th>
                    <th style="width: 35%;">Nama Distributor</th>
                    <th style="width: 15%;">Total Order</th>
                    <th style="width: 20%;">Total Revenue</th>
                    <th style="width: 25%;">Progress</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($top_distributors)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #999;">Belum ada data penjualan</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($top_distributors as $index => $td): ?>
                        <?php 
                            $progress = $max_revenue > 0 ? ($td['revenue'] / $max_revenue) * 100 : 0;
                        ?>
                        <tr>
                            <td><strong><?php echo $index + 1; ?></strong></td>
                            <td><?php echo htmlspecialchars($td['namaDistributor']); ?></td>
                            <td><?php echo $td['order_count']; ?></td>
                            <td><strong>Rp <?php echo number_format($td['revenue'] ?? 0, 0, ',', '.'); ?></strong></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
// Chart Data
const chartLabels = [<?php echo implode(',', $chart_labels); ?>];
const chartRevenueData = [<?php echo implode(',', $chart_revenue); ?>];
const chartOrdersData = [<?php echo implode(',', $chart_orders); ?>];

// Initialize Chart
const ctx = document.getElementById('salesChart');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Revenue (Rp)',
                    data: chartRevenueData,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y'
                },
                {
                    label: 'Orders',
                    data: chartOrdersData,
                    borderColor: '#2196F3',
                    backgroundColor: 'rgba(33, 150, 243, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Revenue (Rp)',
                        color: '#4CAF50',
                        font: { weight: 'bold' }
                    },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID', {
                                maximumFractionDigits: 0
                            }).format(value);
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Jumlah Order',
                        color: '#2196F3',
                        font: { weight: 'bold' }
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.yAxisID === 'y') {
                                label += 'Rp ' + new Intl.NumberFormat('id-ID', {
                                    maximumFractionDigits: 0
                                }).format(context.parsed.y);
                            } else {
                                label += context.parsed.y + ' pesanan';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
}

function applyFilter() {
    const bulan = document.getElementById('bulanFilter').value;
    const tahun = document.getElementById('tahunFilter').value;
    window.location.href = `index.php?page=laporan&bulan=${bulan}&tahun=${tahun}`;
}

function resetFilter() {
    window.location.href = 'index.php?page=laporan';
}

function exportToExcel() {
    const bulan = document.getElementById('bulanFilter').value;
    const tahun = document.getElementById('tahunFilter').value;
    window.location.href = `index.php?page=laporan&bulan=${bulan}&tahun=${tahun}&export=excel`;
}
</script>
