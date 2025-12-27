<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Get footer items
$footer_items = [];
$whereActive = $activeCol ? "`$activeCol` = 1" : '1=1';
if ($tipeCol) {
    $sql = "SELECT * FROM kelola_website WHERE `$tipeCol` = 'footer' AND $whereActive";
} else {
    $sql = "SELECT * FROM kelola_website WHERE $whereActive";
}

if ($stmt = $koneksi->prepare($sql)) {
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        $footer_items = $res->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
}

// Organize items by category
$left_items = [];
$center_text = "&copy; " . date('Y') . " Sales Dashboard - Sistem Penjualan Roti. All rights reserved.";
$right_items = [];

foreach ($footer_items as $item) {
    $pkeyVal = $pageKeyCol ? $item[$pageKeyCol] : (isset($item['page_key']) ? $item['page_key'] : '');
    $labelVal = $labelCol ? $item[$labelCol] : (isset($item['label']) ? $item['label'] : '');
    
    if (strpos($pkeyVal, 'socmed') === 0) {
        $left_items[] = ['key' => $pkeyVal, 'label' => $labelVal];
    } elseif ($pkeyVal === 'copyright') {
        $center_text = $labelVal;
    } elseif (strpos($pkeyVal, 'website') === 0 || strpos($pkeyVal, 'slogan') === 0) {
        $right_items[] = ['key' => $pkeyVal, 'label' => $labelVal];
    }
}
?>

<footer class="app-footer">
    <div class="footer-content" style="display: flex; justify-content: space-between; align-items: center; padding: 20px;">
        <!-- Left: Social Media -->
        <div class="footer-left" style="flex: 1; text-align: left;">
            <?php foreach ($left_items as $item): ?>
                <span class="footer-item" style="margin-right: 15px; font-size: 14px;">📱 <?php echo htmlspecialchars($item['label']); ?></span>
            <?php endforeach; ?>
        </div>
        
        <!-- Center: Copyright -->
        <div class="footer-center" style="flex: 1; text-align: center;">
            <p class="footer-text" style="margin: 0; font-size: 14px;"><?php echo $center_text; ?></p>
        </div>
        
        <!-- Right: Website Name & Slogan -->
        <div class="footer-right" style="flex: 1; text-align: right;">
            <?php foreach ($right_items as $item): ?>
                <p class="footer-item" style="margin: 0; font-size: 14px;"><?php echo htmlspecialchars($item['label']); ?></p>
            <?php endforeach; ?>
        </div>
    </div>
</footer>
