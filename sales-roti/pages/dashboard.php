<?php
/**
 * Dashboard Page
 * Main summary and overview of sales metrics
 */
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
                <p class="card-value">156</p>
                <p class="card-change">+12% dari bulan lalu</p>
            </div>
        </div>
        
        <div class="card card-success">
            <div class="card-icon">🚚</div>
            <div class="card-content">
                <p class="card-label">Total Distributor</p>
                <p class="card-value">24</p>
                <p class="card-change">2 distributor baru</p>
            </div>
        </div>
        
        <div class="card card-warning">
            <div class="card-icon">📮</div>
            <div class="card-content">
                <p class="card-label">Pengiriman Pending</p>
                <p class="card-value">8</p>
                <p class="card-change">Proses pengiriman</p>
            </div>
        </div>
        
        <div class="card card-info">
            <div class="card-icon">💰</div>
            <div class="card-content">
                <p class="card-label">Total Revenue</p>
                <p class="card-value">Rp 45.2M</p>
                <p class="card-change">+8% dari target</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="dashboard-charts">
    <div class="chart-card">
        <h3>Penjualan Bulanan</h3>
        <div class="chart-placeholder">
            <svg viewBox="0 0 400 200" style="width: 100%; height: 200px;">
                <!-- Simple bar chart -->
                <rect x="20" y="120" width="30" height="50" fill="#4a90e2" rx="4"/>
                <rect x="60" y="100" width="30" height="70" fill="#4a90e2" rx="4"/>
                <rect x="100" y="80" width="30" height="90" fill="#4a90e2" rx="4"/>
                <rect x="140" y="90" width="30" height="80" fill="#4a90e2" rx="4"/>
                <rect x="180" y="70" width="30" height="100" fill="#4a90e2" rx="4"/>
                <rect x="220" y="60" width="30" height="110" fill="#4a90e2" rx="4"/>
                <rect x="260" y="40" width="30" height="130" fill="#4a90e2" rx="4"/>
                <rect x="300" y="30" width="30" height="140" fill="#4a90e2" rx="4"/>
                <rect x="340" y="50" width="30" height="120" fill="#4a90e2" rx="4"/>
            </svg>
        </div>
    </div>
    
    <div class="chart-card">
        <h3>Status Pengiriman</h3>
        <div class="status-breakdown">
            <div class="status-item">
                <span class="status-label">Selesai</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 70%; background: #27ae60;"></div>
                </div>
                <span class="status-count">112 pengiriman</span>
            </div>
            <div class="status-item">
                <span class="status-label">Dikirim</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 20%; background: #f39c12;"></div>
                </div>
                <span class="status-count">32 pengiriman</span>
            </div>
            <div class="status-item">
                <span class="status-label">Pending</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 10%; background: #e74c3c;"></div>
                </div>
                <span class="status-count">12 pengiriman</span>
            </div>
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
            <tr>
                <td>ORD-2025-0156</td>
                <td>24 Des 2025</td>
                <td>PT Abadi Jaya</td>
                <td><span class="badge badge-success">Selesai</span></td>
                <td class="action-cell">
                    <button class="btn-action btn-view">Lihat</button>
                </td>
            </tr>
            <tr>
                <td>ORD-2025-0155</td>
                <td>24 Des 2025</td>
                <td>CV Maju Bersama</td>
                <td><span class="badge badge-warning">Dikirim</span></td>
                <td class="action-cell">
                    <button class="btn-action btn-view">Lihat</button>
                </td>
            </tr>
            <tr>
                <td>ORD-2025-0154</td>
                <td>23 Des 2025</td>
                <td>PT Bakery Nusantara</td>
                <td><span class="badge badge-info">Pending</span></td>
                <td class="action-cell">
                    <button class="btn-action btn-view">Lihat</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
