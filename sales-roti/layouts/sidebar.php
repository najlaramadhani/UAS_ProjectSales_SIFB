<?php
/**
 * Sidebar Navigation Component
 * Shows different menus based on user role (admin vs sales)
 */

// Get user role from session
$user_role = $_SESSION['role'] ?? 'sales';
?>
<nav class="sidebar">
    <div class="sidebar-content">
        <ul class="nav-menu">
            <!-- Menu untuk semua role -->
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

            <!-- Menu khusus Admin -->
            <?php if ($user_role === 'admin'): ?>
            <li class="nav-divider"></li>
            <li class="nav-section-title">Admin Panel</li>
            <li>
                <a href="index.php?page=produk" class="nav-item <?php echo ($GLOBALS['page'] == 'produk') ? 'active' : ''; ?>">
                    <span class="nav-icon">🏪</span>
                    <span class="nav-label">Produk</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=user" class="nav-item <?php echo ($GLOBALS['page'] == 'user') ? 'active' : ''; ?>">
                    <span class="nav-icon">👥</span>
                    <span class="nav-label">Kelola User</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
    
    <div class="sidebar-footer">
        <div class="sidebar-info">
            <p class="info-label">Version 1.0</p>
            <p class="role-badge">Role: <strong><?php echo ucfirst($user_role); ?></strong></p>
        </div>
    </div>
</nav>
