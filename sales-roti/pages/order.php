<?php
/**
 * Order Management Page
 * Display and manage pesanan/orders
 */
?>
<div class="page-header">
    <h2>Manajemen Order</h2>
    <p class="page-subtitle">Kelola pesanan dari distributor</p>
</div>

<div class="page-actions">
    <button class="btn btn-primary btn-lg" onclick="openModal('Tambah Order', 'orderForm')">
        + Tambah Order
    </button>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th style="width: 15%;">No Order</th>
                <th style="width: 15%;">Tanggal Order</th>
                <th style="width: 25%;">Distributor</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 30%;">Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>SO202501001</strong></td>
                <td>24 Des 2025</td>
                <td>Holland Roti</td>
                <td>
                    <span class="badge badge-success">Selesai</span>
                </td>
                <td class="action-cell">
                    <button class="btn-action btn-view" onclick="openModal('Detail Order','orderDetail','SO202501001')">Detail</button>
                    <button class="btn-action btn-edit" onclick="openModal('Edit Order', 'orderForm', 'SO202501001')">Edit</button>
                    <button class="btn-action btn-delete" onclick="if(confirm('Hapus order ini?')) alert('Order dihapus')">Hapus</button>
                </td>
            </tr>
            <tr>
                <td><strong>SO202501002</strong></td>
                <td>24 Des 2025</td>
                <td>Roti Jaya</td>
                <td>
                    <span class="badge badge-warning">Dikirim</span>
                </td>
                <td class="action-cell">
                    <button class="btn-action btn-view" onclick="openModal('Detail Order','orderDetail','SO202501002')">Detail</button>
                    <button class="btn-action btn-edit" onclick="openModal('Edit Order', 'orderForm', 'SO202501002')">Edit</button>
                    <button class="btn-action btn-delete" onclick="if(confirm('Hapus order ini?')) alert('Order dihapus')">Hapus</button>
                </td>
            </tr>
            <tr>
                <td><strong>SO202501003</strong></td>
                <td>23 Des 2025</td>
                <td>PT Bakery Nusantara</td>
                <td>
                    <span class="badge badge-info">Pending</span>
                </td>
                <td class="action-cell">
                    <button class="btn-action btn-view" onclick="openModal('Detail Order','orderDetail','SO202501003')">Detail</button>
                    <button class="btn-action btn-edit" onclick="openModal('Edit Order', 'orderForm', 'SO202501003')">Edit</button>
                    <button class="btn-action btn-delete" onclick="if(confirm('Hapus order ini?')) alert('Order dihapus')">Hapus</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="pagination">
    <button class="btn-page" disabled>&laquo; Previous</button>
    <button class="btn-page active">1</button>
    <button class="btn-page">2</button>
    <button class="btn-page">3</button>
    <button class="btn-page">Next &raquo;</button>
</div>

<!-- Modal handled by global openModal / closeModal in assets/js/app.js -->
