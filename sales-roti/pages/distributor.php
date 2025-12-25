<?php
/**
 * Distributor Management Page
 * Manage distributor data and information
 */
?>
<div class="page-header">
    <h2>Manajemen Distributor</h2>
    <p class="page-subtitle">Kelola data distributor dan kontak</p>
</div>

<div class="page-actions">
    <button class="btn btn-primary btn-lg" onclick="openModal('Tambah Distributor','distributorForm')">
        + Tambah Distributor
    </button>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Distributor</th>
                <th>Alamat</th>
                <th>Kontak</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>DST001</td>
                <td>Holland Roti</td>
                <td>Jl. Ahmad Yani No. 123, Medan</td>
                <td>08126789012</td>
                <td>info@abadi.com</td>
                <td class="action-cell">
                    <button class="btn-action btn-edit" onclick="openModal('Edit Distributor','distributorForm')">Edit</button>
                    <button class="btn-action btn-delete" onclick="if(confirm('Hapus distributor ini?')) alert('Distributor dihapus')">Hapus</button>
                </td>
            </tr>
            <tr>
                <td>DST002</td>
                <td>Roti Jaya</td>
                <td>Jl. Gatot Subroto No. 456, Jakarta</td>
                <td>08123456789</td>
                <td>contact@maju.co.id</td>
                <td class="action-cell">
                    <button class="btn-action btn-edit" onclick="openModal('Edit Distributor','distributorForm')">Edit</button>
                    <button class="btn-action btn-delete" onclick="if(confirm('Hapus distributor ini?')) alert('Distributor dihapus')">Hapus</button>
                </td>
            </tr>
            <tr>
                <td>DST003</td>
                <td>PT Bakery Nusantara</td>
                <td>Jl. Sudirman No. 789, Bandung</td>
                <td>08129876543</td>
                <td>sales@bakery-nusantara.com</td>
                <td class="action-cell">
                    <button class="btn-action btn-edit" onclick="openModal('Edit Distributor','distributorForm')">Edit</button>
                    <button class="btn-action btn-delete" onclick="if(confirm('Hapus distributor ini?')) alert('Distributor dihapus')">Hapus</button>
                </td>
            </tr>
            <tr>
                <td>DST004</td>
                <td>PT Toko Segar</td>
                <td>Jl. Diponegoro No. 321, Surabaya</td>
                <td>08118765432</td>
                <td>toko@segar.id</td>
                <td class="action-cell">
                    <button class="btn-action btn-edit" onclick="openModal('Edit Distributor','distributorForm')">Edit</button>
                    <button class="btn-action btn-delete" onclick="if(confirm('Hapus distributor ini?')) alert('Distributor dihapus')">Hapus</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- distributor form handled by global modal via openModal('...','distributorForm') -->
