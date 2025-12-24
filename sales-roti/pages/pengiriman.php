<?php
/**
 * Pengiriman Management Page
 * Track and manage delivery (pengiriman) information
 */
?>
<div class="page-header">
    <h2>Manajemen Pengiriman</h2>
    <p class="page-subtitle">Kelola pengiriman pesanan</p>
</div>

<div class="page-actions">
    <button class="btn btn-primary btn-lg" onclick="openModal('Buat Pengiriman Baru','pengirimanForm')">
        + Buat Pengiriman
    </button>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th style="width: 15%;">No Pengiriman</th>
                <th style="width: 15%;">Tanggal Kirim</th>
                <th style="width: 25%;">Alamat Pengiriman</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 30%;">Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>PGR-2025-0089</strong></td>
                <td>24 Des 2025</td>
                <td>Jl. Ahmad Yani No. 123, Medan</td>
                <td>
                    <span class="badge badge-success">Selesai</span>
                </td>
                <td class="action-cell">
                    <button class="btn-action btn-view" onclick="openModal('Detail Pengiriman','pengirimanDetail','PGR-2025-0089')">Detail</button>
                    <button class="btn-action btn-edit" onclick="openModal('Edit Pengiriman','pengirimanForm','PGR-2025-0089')">Edit</button>
                    <button class="btn-action btn-delete" onclick="if(confirm('Hapus pengiriman ini?')) alert('Pengiriman dihapus')">Hapus</button>
                </td>
            </tr>
            <tr>
                <td><strong>PGR-2025-0088</strong></td>
                <td>24 Des 2025</td>
                <td>Jl. Gatot Subroto No. 456, Jakarta</td>
                <td>
                    <span class="badge badge-warning">Dikirim</span>
                </td>
                <td class="action-cell">
                    <button class="btn-action btn-view" onclick="openModal('Detail Pengiriman','pengirimanDetail','PGR-2025-0088')">Detail</button>
                    <button class="btn-action btn-edit" onclick="openModal('Edit Pengiriman','pengirimanForm','PGR-2025-0088')">Edit</button>
                    <button class="btn-action btn-delete" onclick="if(confirm('Hapus pengiriman ini?')) alert('Pengiriman dihapus')">Hapus</button>
                </td>
            </tr>
            <tr>
                <td><strong>PGR-2025-0087</strong></td>
                <td>23 Des 2025</td>
                <td>Jl. Sudirman No. 789, Bandung</td>
                <td>
                    <span class="badge badge-info">Pending</span>
                </td>
                <td class="action-cell">
                    <button class="btn-action btn-view" onclick="openModal('Detail Pengiriman','pengirimanDetail','PGR-2025-0087')">Detail</button>
                    <button class="btn-action btn-edit" onclick="openModal('Edit Pengiriman','pengirimanForm','PGR-2025-0087')">Edit</button>
                    <button class="btn-action btn-delete" onclick="if(confirm('Hapus pengiriman ini?')) alert('Pengiriman dihapus')">Hapus</button>
                </td>
            </tr>
            <tr>
                <td><strong>PGR-2025-0086</strong></td>
                <td>23 Des 2025</td>
                <td>Jl. Diponegoro No. 321, Surabaya</td>
                <td>
                    <span class="badge badge-success">Selesai</span>
                </td>
                <td class="action-cell">
                    <button class="btn-action btn-view" onclick="openModal('Detail Pengiriman','pengirimanDetail','PGR-2025-0086')">Detail</button>
                    <button class="btn-action btn-edit" onclick="openModal('Edit Pengiriman','pengirimanForm','PGR-2025-0086')">Edit</button>
                    <button class="btn-action btn-delete" onclick="if(confirm('Hapus pengiriman ini?')) alert('Pengiriman dihapus')">Hapus</button>
                </td>
            </tr>
            <tr>
                <td><strong>PGR-2025-0085</strong></td>
                <td>22 Des 2025</td>
                <td>Jl. Rajawali No. 654, Yogyakarta</td>
                <td>
                    <span class="badge badge-success">Selesai</span>
                </td>
                <td class="action-cell">
                    <button class="btn-action btn-view" onclick="openModal('Detail Pengiriman','pengirimanDetail','PGR-2025-0085')">Detail</button>
                    <button class="btn-action btn-edit" onclick="openModal('Edit Pengiriman','pengirimanForm','PGR-2025-0085')">Edit</button>
                    <button class="btn-action btn-delete" onclick="if(confirm('Hapus pengiriman ini?')) alert('Pengiriman dihapus')">Hapus</button>
                </td>
            </tr>
            <tr>
                <td><strong>PGR-2025-0084</strong></td>
                <td>22 Des 2025</td>
                <td>Jl. Iswahyudi No. 987, Palembang</td>
                <td>
                    <span class="badge badge-warning">Dikirim</span>
                </td>
                <td class="action-cell">
                    <button class="btn-action btn-view" onclick="openModal('Detail Pengiriman','pengirimanDetail','PGR-2025-0084')">Detail</button>
                    <button class="btn-action btn-edit" onclick="openModal('Edit Pengiriman','pengirimanForm','PGR-2025-0084')">Edit</button>
                    <button class="btn-action btn-delete" onclick="if(confirm('Hapus pengiriman ini?')) alert('Pengiriman dihapus')">Hapus</button>
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

<!-- Status Summary -->
<div class="status-summary">
    <div class="summary-card">
        <div class="summary-icon">✓</div>
        <div class="summary-content">
            <p class="summary-label">Selesai</p>
            <p class="summary-value">89 pengiriman</p>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon">→</div>
        <div class="summary-content">
            <p class="summary-label">Dikirim</p>
            <p class="summary-value">12 pengiriman</p>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon">⏳</div>
        <div class="summary-content">
            <p class="summary-label">Pending</p>
            <p class="summary-value">5 pengiriman</p>
        </div>
    </div>
</div>

<script>
function openPengirimanModal(title, pengirimanId = null) {
    document.getElementById('modalTitle').textContent = title;
    const formContent = document.getElementById('formContent');
    
    // prefer using global openModal('...','pengirimanForm') but keep this as fallback
    formContent.innerHTML = `
        <div class="form-group">
            <label for="noPengiriman">No Pengiriman</label>
            <input type="text" id="noPengiriman" name="noPengiriman" placeholder="Auto generated" readonly>
        </div>
        <div class="form-group">
            <label for="tanggalKirim">Tanggal Kirim</label>
            <input type="date" id="tanggalKirim" name="tanggalKirim" required>
        </div>
        <div class="form-group">
            <label for="alamatPengiriman">Alamat Pengiriman</label>
            <textarea id="alamatPengiriman" name="alamatPengiriman" placeholder="Jalan, No., Kota, Provinsi" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label for="namaDriver">Nama Driver</label>
            <input type="text" id="namaDriver" name="namaDriver" placeholder="Nama driver" required />
        </div>
        <div class="form-group">
            <label for="kontakDriver">Kontak Driver</label>
            <input type="text" id="kontakDriver" name="kontakDriver" placeholder="08xxxxxxxxxx" required />
        </div>
        <div class="form-group">
            <label for="statusPengiriman">Status Pengiriman</label>
            <select id="statusPengiriman" name="statusPengiriman" required>
                <option value="">Pilih Status</option>
                <option value="pending">Pending</option>
                <option value="dikirim">Dikirim</option>
                <option value="selesai">Selesai</option>
            </select>
        </div>
        <div class="form-group">
            <label for="noOrder">No Order Terkait</label>
            <select id="noOrder" name="noOrder" required>
                <option value="">Pilih Order</option>
                <option value="ORD-2025-0156">ORD-2025-0156 - PT Abadi Jaya</option>
                <option value="ORD-2025-0155">ORD-2025-0155 - CV Maju Bersama</option>
                <option value="ORD-2025-0154">ORD-2025-0154 - PT Bakery Nusantara</option>
            </select>
        </div>
    `;

    document.getElementById('modalForm').style.display = 'block';
}

function closeModal() {
    document.getElementById('modalForm').style.display = 'none';
}
</script>
