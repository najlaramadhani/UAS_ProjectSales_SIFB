<?php
/**
 * Sidebar Navigation Component
 */
?>
<nav class="sidebar">
    <div class="sidebar-content">
        <ul class="nav-menu">
            <li>
                <a href="index.php?page=dashboard" class="nav-item <?php echo ($GLOBALS['page'] == 'dashboard') ? 'active' : ''; ?>">
                    <span class="nav-icon">📊</span>
                    <span class="nav-label">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=order" class="nav-item <?php echo ($GLOBALS['page'] == 'order') ? 'active' : ''; ?>">
                    <span class="nav-icon">📦</span>
                    <span class="nav-label">Order</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=distributor" class="nav-item <?php echo ($GLOBALS['page'] == 'distributor') ? 'active' : ''; ?>">
                    <span class="nav-icon">🚚</span>
                    <span class="nav-label">Distributor</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=pengiriman" class="nav-item <?php echo ($GLOBALS['page'] == 'pengiriman') ? 'active' : ''; ?>">
                    <span class="nav-icon">📮</span>
                    <span class="nav-label">Pengiriman</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=laporan" class="nav-item <?php echo ($GLOBALS['page'] == 'laporan') ? 'active' : ''; ?>">
                    <span class="nav-icon">📈</span>
                    <span class="nav-label">Laporan</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-footer">
        <div class="sidebar-info">
            <p class="info-label">Version 1.0</p>
        </div>
    </div>
</nav>
