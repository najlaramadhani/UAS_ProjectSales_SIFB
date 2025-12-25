<?php
/**
 * Header Layout Component
 */
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
$user_initials = implode('', array_map(fn($word) => $word[0], explode(' ', $user_name)));
?>
<header class="app-header">
    <div class="header-container">
        <div class="header-brand">
            <h1 class="brand-title">
                <span class="brand-icon">🍞</span>
                Sales Dashboard
            </h1>
            <p class="brand-subtitle">Sistem Penjualan Roti</p>
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
