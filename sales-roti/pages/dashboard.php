<?php
/**
 * Dashboard Page
 * Main summary and overview of sales metrics with real database data
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

// Get total orders
$order_query = "SELECT COUNT(*) as total FROM pesanan WHERE idUser = ?";
$order_stmt = $koneksi->prepare($order_query);
$order_stmt->bind_param('s', $current_user_id);
$order_stmt->execute();
$order_total = $order_stmt->get_result()->fetch_assoc()['total'];
$order_stmt->close();

// Get total distributors
$dist_query = "SELECT COUNT(*) as total FROM distributor WHERE idUser = ?";
$dist_stmt = $koneksi->prepare($dist_query);
$dist_stmt->bind_param('s', $current_user_id);
$dist_stmt->execute();
$dist_total = $dist_stmt->get_result()->fetch_assoc()['total'];
$dist_stmt->close();

// Get pending deliveries
$pending_query = "SELECT COUNT(*) as total FROM pengiriman WHERE idUser = ? AND statusPengiriman = 'Pending'";
$pending_stmt = $koneksi->prepare($pending_query);
$pending_stmt->bind_param('s', $current_user_id);
$pending_stmt->execute();
$pending_total = $pending_stmt->get_result()->fetch_assoc()['total'];
$pending_stmt->close();

// Get total revenue
$revenue_query = "SELECT SUM(dp.totalHarga) as total FROM detail_pesanan dp 
                 JOIN pesanan p ON dp.noPesanan = p.noPesanan 
                 WHERE p.idUser = ?";
$revenue_stmt = $koneksi->prepare($revenue_query);
$revenue_stmt->bind_param('s', $current_user_id);
$revenue_stmt->execute();
$revenue_row = $revenue_stmt->get_result()->fetch_assoc();
$total_revenue = $revenue_row['total'] ?? 0;
$revenue_stmt->close();

// Get top selling products
$product_query = "SELECT pr.namaProduk, SUM(dp.jumlah) as total_qty, SUM(dp.totalHarga) as total_revenue
                 FROM detail_pesanan dp 
                 JOIN produk pr ON dp.idProduk = pr.idProduk
                 JOIN pesanan p ON dp.noPesanan = p.noPesanan
                 WHERE p.idUser = ?
                 GROUP BY pr.namaProduk
                 ORDER BY total_revenue DESC LIMIT 5";
$product_stmt = $koneksi->prepare($product_query);
$product_stmt->bind_param('s', $current_user_id);
$product_stmt->execute();
$top_products = $product_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$product_stmt->close();

// Find max revenue for progress bar
$max_product_revenue = 0;
foreach ($top_products as $tp) {
    if ($tp['total_revenue'] > $max_product_revenue) {
        $max_product_revenue = $tp['total_revenue'];
    }
}

// Get recent orders
$recent_query = "SELECT p.noPesanan, p.tanggalOrder, d.namaDistributor, p.status 
                FROM pesanan p 
                JOIN distributor d ON p.noDistributor = d.noDistributor 
                WHERE p.idUser = ? 
                ORDER BY p.tanggalOrder DESC LIMIT 5";
$recent_stmt = $koneksi->prepare($recent_query);
$recent_stmt->bind_param('s', $current_user_id);
$recent_stmt->execute();
$recent_orders = $recent_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$recent_stmt->close();
?>

<div class="page-header">
    <h2>Dashboard</h2>
    <p class="page-subtitle">Ringkasan penjualan dan statistik</p>
</div>

<div class="dashboard-grid">
    <!-- Summary Cards -->
    <div class="card-container">
        <div class="card card-primary">
            <div class="card-icon">📦</div>
            <div class="card-content">
                <p class="card-label">Total Order</p>
                <p class="card-value"><?php echo $order_total; ?></p>
                <p class="card-change">Semua order</p>
            </div>
        </div>
        
        <div class="card card-success">
            <div class="card-icon">🚚</div>
            <div class="card-content">
                <p class="card-label">Total Distributor</p>
                <p class="card-value"><?php echo $dist_total; ?></p>
                <p class="card-change">Distributor aktif</p>
            </div>
        </div>
        
        <div class="card card-warning">
            <div class="card-icon">📮</div>
            <div class="card-content">
                <p class="card-label">Pengiriman Pending</p>
                <p class="card-value"><?php echo $pending_total; ?></p>
                <p class="card-change">Sedang diproses</p>
            </div>
        </div>
        
        <div class="card card-info">
            <div class="card-icon">💰</div>
            <div class="card-content">
                <p class="card-label">Total Revenue</p>
                <p class="card-value">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></p>
                <p class="card-change">Semua waktu</p>
            </div>
        </div>
    </div>
</div>

<!-- Grafik Penjualan -->
<div class="dashboard-charts">
    <div class="chart-card">
        <h3>Grafik Penjualan - Produk Terlaris</h3>
        <div class="sales-chart">
            <?php if (empty($top_products)): ?>
                <p style="text-align: center; color: #999; padding: 20px;">Belum ada data penjualan</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <?php foreach ($top_products as $product): 
                        $progress = $max_product_revenue > 0 ? ($product['total_revenue'] / $max_product_revenue) * 100 : 0;
                    ?>
                    <tr style="border-bottom: 1px solid #eee; padding: 10px 0;">
                        <td style="padding: 10px; width: 30%;"><strong><?php echo htmlspecialchars($product['namaProduk']); ?></strong></td>
                        <td style="padding: 10px; width: 50%;">
                            <div style="background: #f0f0f0; border-radius: 4px; overflow: hidden; height: 30px;">
                                <div style="background: linear-gradient(90deg, #4a90e2, #357abd); height: 100%; width: <?php echo $progress; ?>%; display: flex; align-items: center; justify-content: flex-end; padding-right: 10px; color: white; font-size: 12px;">
                                    <?php echo round($progress, 0); ?>%
                                </div>
                            </div>
                        </td>
                        <td style="padding: 10px; text-align: right; width: 20%;"><strong>Rp <?php echo number_format($product['total_revenue'], 0, ',', '.'); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="recent-orders">
    <div class="section-header">
        <h3>Order Terbaru</h3>
        <a href="index.php?page=order" class="link-more">Lihat semua →</a>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>No Order</th>
                <th>Tanggal</th>
                <th>Distributor</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recent_orders)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #999;">Belum ada order</td>
                </tr>
            <?php else: ?>
                <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($order['noPesanan']); ?></strong></td>
                        <td><?php echo date('d M Y', strtotime($order['tanggalOrder'])); ?></td>
                        <td><?php echo htmlspecialchars($order['namaDistributor']); ?></td>
                        <td>
                            <span class="badge badge-<?php 
                                echo strtolower(str_replace(' ', '', $order['status'])); 
                            ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td class="action-cell">
                            <a href="index.php?page=detail_pesanan&pesanan=<?php echo htmlspecialchars($order['noPesanan']); ?>" class="btn-action btn-view">Lihat</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
