<?php
/**
 * Header Layout Component (dynamic from database)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
$user_initials = implode('', array_map(function($w){ return $w[0]; }, explode(' ', $user_name)));

if (!isset($koneksi)) {
    include_once __DIR__ . '/../config/koneksi.php';
}

// Detect columns in kelola_website
$columns = [];
$colsStmt = $koneksi->query("SHOW COLUMNS FROM kelola_website");
if ($colsStmt) {
    while ($c = $colsStmt->fetch_assoc()) {
        $columns[] = $c['Field'];
    }
}

if (!function_exists('pickCol')) {
    function pickCol(array $cols, array $candidates) {
        foreach ($candidates as $cand) {
            if (in_array($cand, $cols)) return $cand;
        }
        return null;
    }
}

$tipeCol = pickCol($columns, ['tipe','type']);
$activeCol = pickCol($columns, ['is_active','isActive','active']);
$pageKeyCol = pickCol($columns, ['page_key','key_name','page']);
$labelCol = pickCol($columns, ['label','value_text','name','title']);

// Get header items from DB
$site_title = 'Sales Dashboard';
$site_subtitle = 'Sistem Penjualan Roti';
$location = '';

$whereActive = $activeCol ? "`$activeCol` = 1" : '1=1';
if ($tipeCol) {
    $sql = "SELECT * FROM kelola_website WHERE `$tipeCol` = 'header' AND $whereActive";
} else {
    $sql = "SELECT * FROM kelola_website WHERE $whereActive";
}

if ($stmt = $koneksi->prepare($sql)) {
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        $header_items = $res->fetch_all(MYSQLI_ASSOC);
        
        foreach ($header_items as $item) {
            $pkeyVal = $pageKeyCol ? $item[$pageKeyCol] : (isset($item['page_key']) ? $item['page_key'] : '');
            $labelVal = $labelCol ? $item[$labelCol] : (isset($item['label']) ? $item['label'] : '');
            
            if ($pkeyVal === 'site_title') $site_title = $labelVal;
            if ($pkeyVal === 'site_subtitle') $site_subtitle = $labelVal;
            if ($pkeyVal === 'location') $location = $labelVal;
        }
    }
    $stmt->close();
}
?>
<header class="app-header">
    <div class="header-container">
        <div class="header-brand">
            <h1 class="brand-title">
                <span class="brand-icon">🍞</span>
                <?php echo htmlspecialchars($site_title); ?>
            </h1>
            <p class="brand-subtitle"><?php echo htmlspecialchars($site_subtitle); ?></p>
            <?php if ($location): ?><p><?php echo htmlspecialchars($location); ?></p><?php endif; ?>
        </div>
        
        <div class="header-actions">
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                <div class="user-avatar"><?php echo htmlspecialchars(strtoupper($user_initials)); ?></div>
            </div>
            <a href="?action=logout" class="btn-logout">Logout</a>
        </div>
    </div>
</header>
