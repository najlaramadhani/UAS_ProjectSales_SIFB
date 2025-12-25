<?php
/**
 * Laporan (Report) Page
 * Display sales reports and analytics
 */
?>
<div class="page-header">
    <h2>Laporan Penjualan</h2>
    <p class="page-subtitle">Analisis dan laporan penjualan</p>
</div>

<!-- Report Filters -->
<div class="filter-section">
    <div class="filter-group">
        <label>Periode Laporan:</label>
        <div class="filter-inputs">
            <input type="date" placeholder="Dari tanggal">
            <span class="filter-separator">-</span>
            <input type="date" placeholder="Sampai tanggal">
            <button class="btn btn-secondary">Filter</button>
            <button class="btn btn-outline">Reset</button>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="report-summary">
    <div class="summary-card summary-large">
        <div class="summary-icon" style="color: #4a90e2;">💰</div>
        <div class="summary-content">
            <p class="summary-label">Total Revenue Bulan Ini</p>
            <p class="summary-value">Rp 125.5M</p>
            <p class="summary-change">+15% dari bulan lalu</p>
        </div>
    </div>
    
    <div class="summary-card summary-large">
        <div class="summary-icon" style="color: #27ae60;">📦</div>
        <div class="summary-content">
            <p class="summary-label">Total Order Bulan Ini</p>
            <p class="summary-value">234 pesanan</p>
            <p class="summary-change">+8% dari bulan lalu</p>
        </div>
    </div>
    
    <div class="summary-card summary-large">
        <div class="summary-icon" style="color: #f39c12;">📮</div>
        <div class="summary-content">
            <p class="summary-label">Total Pengiriman</p>
            <p class="summary-value">212 pengiriman</p>
            <p class="summary-change">92% delivery rate</p>
        </div>
    </div>
    
    <div class="summary-card summary-large">
        <div class="summary-icon" style="color: #9b59b6;">👥</div>
        <div class="summary-content">
            <p class="summary-label">Distributor Aktif</p>
            <p class="summary-value">24 distributor</p>
            <p class="summary-change">+2 distributor baru</p>
        </div>
    </div>
</div>

<!-- Top Distributors Report -->
<div class="report-section">
    <h3>Top 10 Distributor Penjualan</h3>
    <div class="table-container">
        <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">Rank</th>
                <th style="width: 30%;">Nama Distributor</th>
                <th style="width: 20%;">Total Order</th>
                <th style="width: 20%;">Total Revenue</th>
                <th style="width: 25%;">Progress</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>1</strong></td>
                <td>PT Abadi Jaya</td>
                <td>45</td>
                <td>Rp 28.5M</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 95%;"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong>2</strong></td>
                <td>CV Maju Bersama</td>
                <td>38</td>
                <td>Rp 24.2M</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 85%;"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong>3</strong></td>
                <td>PT Bakery Nusantara</td>
                <td>35</td>
                <td>Rp 22.1M</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 78%;"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong>4</strong></td>
                <td>PT Toko Segar</td>
                <td>32</td>
                <td>Rp 20.5M</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 72%;"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong>5</strong></td>
                <td>CV Berkah Makmur</td>
                <td>28</td>
                <td>Rp 17.8M</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 62%;"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong>6</strong></td>
                <td>PT Roti Berkualitas</td>
                <td>25</td>
                <td>Rp 15.2M</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 54%;"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong>7</strong></td>
                <td>Toko Roti Enak</td>
                <td>18</td>
                <td>Rp 11.5M</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 40%;"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong>8</strong></td>
                <td>CV Ceria Jaya</td>
                <td>15</td>
                <td>Rp 9.8M</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 35%;"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong>9</strong></td>
                <td>PT Aneka Roti</td>
                <td>12</td>
                <td>Rp 7.2M</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 25%;"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong>10</strong></td>
                <td>Toko Tradisional</td>
                <td>8</td>
                <td>Rp 5.1M</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 18%;"></div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    </div>
</div>

<!-- Export Options -->
<div class="export-section">
    <h3>Ekspor Laporan</h3>
    <div class="export-buttons">
        <button class="btn btn-secondary">📥 Export PDF</button>
        <button class="btn btn-secondary">📊 Export Excel</button>
        <button class="btn btn-secondary">🖨️ Print Preview</button>
    </div>
</div>
