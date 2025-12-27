<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_role = $_SESSION['role'] ?? 'sales';
$current_page = isset($_GET['page']) ? $_GET['page'] : ($GLOBALS['page'] ?? 'dashboard');

// DB query: get sidebar items (detect actual column names using SHOW COLUMNS)
if (!isset($koneksi)) {
    include_once __DIR__ . '/../config/koneksi.php';
}

$items = [];
$columns = [];
$colsStmt = $koneksi->query("SHOW COLUMNS FROM kelola_website");
if ($colsStmt) {
    while ($c = $colsStmt->fetch_assoc()) {
        $columns[] = $c['Field'];
    }
}

// helpers to pick column variants
if (!function_exists('pickCol')) {
    function pickCol(array $cols, array $candidates) {
        foreach ($candidates as $cand) {
            if (in_array($cand, $cols)) return $cand;
        }
        return null;
    }
}

$tipeCol = pickCol($columns, ['tipe','type']);
$activeCol = pickCol($columns, ['is_active','isActive','active','isActiveFlag']);
$orderCol = pickCol($columns, ['urutan','urut','ordering','order_no','`order`']);
$pageKeyCol = pickCol($columns, ['page_key','key_name','page','pageKey','key']);
$labelCol = pickCol($columns, ['label','value_text','name','title','text']);

// Build safe query
$whereActive = $activeCol ? "`$activeCol` = 1" : '1=1';
if ($tipeCol) {
    $sql = "SELECT * FROM kelola_website WHERE `$tipeCol` = ? AND $whereActive";
} else {
    $sql = "SELECT * FROM kelola_website WHERE $whereActive"; // fallback
}
if ($orderCol) {
    $sql .= " ORDER BY `$orderCol` ASC";
}

if ($stmt = $koneksi->prepare($sql)) {
    if ($tipeCol) {
        $t = 'sidebar';
        $stmt->bind_param('s', $t);
    }
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        $items = $res->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
}

// icon mapping (HARDCODE)
$icons = [
    'dashboard' => '📊',
    'order' => '📦',
    'distributor' => '🚚',
    'pengiriman' => '📮',
    'laporan' => '📈',
    'produk' => '🏪',
    'user' => '👥'
];

// admin-only keys
$admin_only = ['produk', 'user'];
?>

<nav class="sidebar">
    <div class="sidebar-content">
        <ul class="nav-menu">
            <?php if (empty($items)): ?>
                <!-- fallback static menu if DB empty -->
                <li>
                    <a href="index.php?page=dashboard" class="nav-item <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                        <span class="nav-icon">📊</span>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </li>
            <?php else: ?>
                <?php 
                $showed_admin_divider = false;
                foreach ($items as $it):
                    $key = $it['page_key'];
                    $is_admin_only = in_array($key, $admin_only);
                    // hide admin-only items for non-admin
                    if ($is_admin_only && $user_role !== 'admin') continue;
                    // add divider before first admin item
                    if ($is_admin_only && $user_role === 'admin' && !$showed_admin_divider) {
                        echo '<li class="nav-divider"></li>';
                        echo '<li class="nav-section-title">Admin Panel</li>';
                        $showed_admin_divider = true;
                    }
                    $label = $it['label'];
                    $icon = isset($icons[$key]) ? $icons[$key] : '•';
                ?>
                    <li>
                        <a href="index.php?page=<?php echo urlencode($key); ?>" class="nav-item <?php echo ($current_page === $key) ? 'active' : ''; ?>">
                            <span class="nav-icon"><?php echo $icon; ?></span>
                            <span class="nav-label"><?php echo htmlspecialchars($label); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-info">
            <p class="info-label">Version 1.0</p>
            <p class="role-badge">Role: <strong><?php echo ucfirst(htmlspecialchars($user_role)); ?></strong></p>
        </div>
    </div>
</nav>
