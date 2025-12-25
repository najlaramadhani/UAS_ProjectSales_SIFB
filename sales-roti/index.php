<?php
/**
 * Main Entry Point - Sales Dashboard for Bread Sales System
 * Modern, responsive layout with semantic HTML
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Determine current page
$page = isset($_GET['page']) ? trim($_GET['page']) : 'dashboard';
$allowed_pages = ['dashboard', 'order', 'distributor', 'pengiriman', 'laporan'];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Dashboard - Sistem Penjualan Roti</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <?php include 'layouts/header.php'; ?>
        
        <div class="main-content">
            <!-- Sidebar Navigation -->
            <?php include 'layouts/sidebar.php'; ?>
            
            <!-- Main Article Content -->
            <article class="main-article">
                <?php include "pages/{$page}.php"; ?>
            </article>
            
            <!-- Right Aside Panel -->
            <?php include 'layouts/aside.php'; ?>
        </div>
        
        <!-- Footer -->
        <?php include 'layouts/footer.php'; ?>
    </div>
    
    <!-- Modals -->
    <div id="modalForm" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Form</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="dynamicForm">
                    <div id="formContent"></div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="assets/js/app.js"></script>
</body>
</html>
