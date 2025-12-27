<?php
/**
 * Right Aside Panel Component - dynamic counts and recent activity
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ensure DB connection is available
if (!isset($koneksi)) {
    include_once __DIR__ . '/../config/koneksi.php';
}

$current_user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? 'sales';

// Pesanan hari ini
if ($user_role === 'admin') {
    $today_orders_q = "SELECT COUNT(*) as cnt FROM pesanan WHERE DATE(tanggalOrder) = CURDATE()";
    $stmt = $koneksi->prepare($today_orders_q);
    $stmt->execute();
} else {
    $today_orders_q = "SELECT COUNT(*) as cnt FROM pesanan WHERE DATE(tanggalOrder) = CURDATE() AND idUser = ?";
    $stmt = $koneksi->prepare($today_orders_q);
    $stmt->bind_param('s', $current_user_id);
    $stmt->execute();
}
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$today_orders = intval($row['cnt'] ?? 0);
$stmt->close();

// Pengiriman pending (status not finished)
if ($user_role === 'admin') {
    $pending_q = "SELECT COUNT(*) as cnt FROM pengiriman WHERE statusPengiriman <> 'Selesai'";
    $pstmt = $koneksi->prepare($pending_q);
    $pstmt->execute();
} else {
    $pending_q = "SELECT COUNT(*) as cnt FROM pengiriman pr JOIN pesanan p ON pr.noPesanan = p.noPesanan WHERE pr.statusPengiriman <> 'Selesai' AND p.idUser = ?";
    $pstmt = $koneksi->prepare($pending_q);
    $pstmt->bind_param('s', $current_user_id);
    $pstmt->execute();
}
$pres = $pstmt->get_result();
$prow = $pres->fetch_assoc();
$pending_count = intval($prow['cnt'] ?? 0);
$pstmt->close();

// Total distributors
if ($user_role === 'admin') {
    $dist_q = "SELECT COUNT(*) as cnt FROM distributor";
    $dstmt = $koneksi->prepare($dist_q);
    $dstmt->execute();
} else {
    $dist_q = "SELECT COUNT(*) as cnt FROM distributor WHERE idUser = ?";
    $dstmt = $koneksi->prepare($dist_q);
    $dstmt->bind_param('s', $current_user_id);
    $dstmt->execute();
}
$dres = $dstmt->get_result();
$drow = $dres->fetch_assoc();
$dist_count = intval($drow['cnt'] ?? 0);
$dstmt->close();

// Recent activity: latest 3 orders (role-aware)
if ($user_role === 'admin') {
    $act_q = "SELECT p.noPesanan, p.tanggalOrder, d.namaDistributor, p.status, u.nama as user_name FROM pesanan p JOIN distributor d ON p.noDistributor = d.noDistributor JOIN user u ON p.idUser = u.idUser ORDER BY p.tanggalOrder DESC LIMIT 3";
    $astmt = $koneksi->prepare($act_q);
    $astmt->execute();
} else {
    $act_q = "SELECT p.noPesanan, p.tanggalOrder, d.namaDistributor, p.status FROM pesanan p JOIN distributor d ON p.noDistributor = d.noDistributor WHERE p.idUser = ? ORDER BY p.tanggalOrder DESC LIMIT 3";
    $astmt = $koneksi->prepare($act_q);
    $astmt->bind_param('s', $current_user_id);
    $astmt->execute();
}
$ares = $astmt->get_result();
$activities = $ares->fetch_all(MYSQLI_ASSOC);
$astmt->close();
?>

<aside class="aside-panel">
    <div class="aside-content">
        <!-- Quick Stats -->
        <div class="aside-section">
            <h3 class="aside-title">Quick Stats</h3>
            <div class="quick-stat">
                <span class="stat-label">Pesanan Hari Ini</span>
                <span class="stat-value"><?php echo $today_orders; ?></span>
            </div>
            <div class="quick-stat">
                <span class="stat-label">Pengiriman Pending</span>
                <span class="stat-value"><?php echo $pending_count; ?></span>
            </div>
            <div class="quick-stat">
                <span class="stat-label">Total Distributor</span>
                <span class="stat-value"><?php echo $dist_count; ?></span>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="aside-section">
            <h3 class="aside-title">Aktivitas Terbaru</h3>
            <?php if (empty($activities)): ?>
                <div class="activity-item">
                    <div class="activity-text">
                        <p class="activity-desc">Belum ada aktivitas terbaru</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($activities as $act): ?>
                    <div class="activity-item">
                        <div class="activity-dot"></div>
                        <div class="activity-text">
                            <?php if (isset($act['user_name'])): ?>
                                <p class="activity-desc">Order <strong><?php echo htmlspecialchars($act['noPesanan']); ?></strong> oleh <?php echo htmlspecialchars($act['user_name']); ?></p>
                            <?php else: ?>
                                <p class="activity-desc">Order <strong><?php echo htmlspecialchars($act['noPesanan']); ?></strong></p>
                            <?php endif; ?>
                            <span class="activity-time"><?php echo date('d M Y', strtotime($act['tanggalOrder'])); ?> — <?php echo htmlspecialchars($act['namaDistributor']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</aside>
