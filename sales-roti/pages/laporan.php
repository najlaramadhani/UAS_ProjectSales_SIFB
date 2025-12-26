<?php
/**
 * Laporan (Report) Page - READ ONLY
 * Display sales reports and analytics with database-driven data
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$current_user_id = $_SESSION['user_id'];
$bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : date('n');
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

// Total Pesanan
$order_query = "SELECT COUNT(*) as total, SUM(1) as count FROM pesanan 
               WHERE idUser = ? AND MONTH(tanggalOrder) = ? AND YEAR(tanggalOrder) = ?";
$order_stmt = $koneksi->prepare($order_query);
$order_stmt->bind_param('sii', $current_user_id, $bulan, $tahun);
$order_stmt->execute();
$order_total = $order_stmt->get_result()->fetch_assoc()['count'];
$order_stmt->close();

// Total Pengiriman
$delivery_query = "SELECT COUNT(*) as total FROM pengiriman 
                  WHERE idUser = ? AND MONTH(tanggalKirim) = ? AND YEAR(tanggalKirim) = ?";
$delivery_stmt = $koneksi->prepare($delivery_query);
$delivery_stmt->bind_param('sii', $current_user_id, $bulan, $tahun);
$delivery_stmt->execute();
$delivery_total = $delivery_stmt->get_result()->fetch_assoc()['total'];
$delivery_stmt->close();

// Total Distributor
$distributor_query = "SELECT COUNT(DISTINCT d.noDistributor) as total FROM distributor d 
                    JOIN pesanan p ON d.noDistributor = p.noDistributor 
                    WHERE p.idUser = ? AND MONTH(p.tanggalOrder) = ? AND YEAR(p.tanggalOrder) = ?";
$distributor_stmt = $koneksi->prepare($distributor_query);
$distributor_stmt->bind_param('sii', $current_user_id, $bulan, $tahun);
$distributor_stmt->execute();
$distributor_total = $distributor_stmt->get_result()->fetch_assoc()['total'];
$distributor_stmt->close();

// Total Revenue (SUM of detail_pesanan totalHarga)
$revenue_query = "SELECT SUM(dp.totalHarga) as total_revenue FROM detail_pesanan dp 
                 JOIN pesanan p ON dp.noPesanan = p.noPesanan 
                 WHERE p.idUser = ? AND MONTH(p.tanggalOrder) = ? AND YEAR(p.tanggalOrder) = ?";
$revenue_stmt = $koneksi->prepare($revenue_query);
$revenue_stmt->bind_param('sii', $current_user_id, $bulan, $tahun);
$revenue_stmt->execute();
$revenue_row = $revenue_stmt->get_result()->fetch_assoc();
$total_revenue = $revenue_row['total_revenue'] ?? 0;
$revenue_stmt->close();

// Pengiriman Status Breakdown
$status_query = "SELECT statusPengiriman, COUNT(*) as count FROM pengiriman 
                WHERE idUser = ? AND MONTH(tanggalKirim) = ? AND YEAR(tanggalKirim) = ? 
                GROUP BY statusPengiriman";
$status_stmt = $koneksi->prepare($status_query);
$status_stmt->bind_param('sii', $current_user_id, $bulan, $tahun);
$status_stmt->execute();
$status_results = $status_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$status_stmt->close();

$status_counts = ['Selesai' => 0, 'Dikirim' => 0, 'Pending' => 0];
foreach ($status_results as $sr) {
    $key = ucfirst($sr['statusPengiriman']);
    if (isset($status_counts[$key])) {
        $status_counts[$key] = $sr['count'];
    }
}

// Top Distributors Report
$top_dist_query = "SELECT d.namaDistributor, COUNT(p.noPesanan) as order_count, SUM(dp.totalHarga) as revenue
                  FROM distributor d 
                  JOIN pesanan p ON d.noDistributor = p.noDistributor 
                  LEFT JOIN detail_pesanan dp ON p.noPesanan = dp.noPesanan 
                  WHERE p.idUser = ? AND MONTH(p.tanggalOrder) = ? AND YEAR(p.tanggalOrder) = ? 
                  GROUP BY d.namaDistributor 
                  ORDER BY revenue DESC LIMIT 10";
$top_dist_stmt = $koneksi->prepare($top_dist_query);
$top_dist_stmt->bind_param('sii', $current_user_id, $bulan, $tahun);
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

<!-- Pengiriman Status Breakdown -->
<div class="report-section">
    <h3>Status Pengiriman</h3>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Jumlah Pengiriman</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_pengiriman = array_sum($status_counts);
                foreach ($status_counts as $status => $count):
                    $persen = $total_pengiriman > 0 ? ($count / $total_pengiriman) * 100 : 0;
                ?>
                    <tr>
                        <td><strong><?php echo $status; ?></strong></td>
                        <td><?php echo $count; ?> pengiriman</td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $persen; ?>%; background: <?php 
                                    echo $status === 'Selesai' ? '#27ae60' : ($status === 'Dikirim' ? '#f39c12' : '#e74c3c'); 
                                ?>;"></div>
                            </div>
                            <?php echo round($persen, 1); ?>%
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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

<script>
function applyFilter() {
    const bulan = document.getElementById('bulanFilter').value;
    const tahun = document.getElementById('tahunFilter').value;
    window.location.href = `index.php?page=laporan&bulan=${bulan}&tahun=${tahun}`;
}

function resetFilter() {
    window.location.href = 'index.php?page=laporan';
}
</script>
