<?php
/**
 * Pengiriman (Delivery) Management - CRUD Operations
 * Single file handling all delivery operations
 * Accessible only to SALES role
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'sales') {
    header('Location: login.php');
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Handle DELETE
if (isset($_GET['hapus'])) {
    $noPengiriman = trim($_GET['hapus']);
    
    $check_query = "SELECT pg.noPengiriman FROM pengiriman pg 
                   WHERE pg.noPengiriman = ? AND pg.idUser = ?";
    $check_stmt = $koneksi->prepare($check_query);
    $check_stmt->bind_param('ss', $noPengiriman, $current_user_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        $delete_query = "DELETE FROM pengiriman WHERE noPengiriman = ?";
        $delete_stmt = $koneksi->prepare($delete_query);
        $delete_stmt->bind_param('s', $noPengiriman);
        if ($delete_stmt->execute()) {
            $_SESSION['success_message'] = 'Pengiriman berhasil dihapus!';
        }
        $delete_stmt->close();
    }
    $check_stmt->close();
    
    header('Location: index.php?page=pengiriman');
    exit;
}

// Handle CREATE/UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $noSuratJalan = trim($_POST['noSuratJalan'] ?? '');
    $tanggalKirim = trim($_POST['tanggalKirim'] ?? '');
    $alamatPengiriman = trim($_POST['alamatPengiriman'] ?? '');
    $statusPengiriman = trim($_POST['statusPengiriman'] ?? 'Pending');
    $noPesanan = trim($_POST['noPesanan'] ?? '');
    $noDistributor = trim($_POST['noDistributor'] ?? '');
    $idDriver = trim($_POST['idDriver'] ?? '');
    
    if (empty($noSuratJalan) || empty($tanggalKirim) || empty($alamatPengiriman) || empty($noPesanan)) {
        $_SESSION['error_message'] = 'Semua field harus diisi!';
    } else {
        if ($action === 'tambah') {
            // Generate pengiriman ID: DO-SO[orderid]-01
            // Get order info to get distributor and noPesanan
            $order_query = "SELECT noDistributor FROM pesanan WHERE noPesanan = ? AND idUser = ?";
            $order_stmt = $koneksi->prepare($order_query);
            $order_stmt->bind_param('ss', $noPesanan, $current_user_id);
            $order_stmt->execute();
            $order_result = $order_stmt->get_result();
            
            if ($order_result->num_rows === 0) {
                $_SESSION['error_message'] = 'Order tidak ditemukan!';
            } else {
                $order_row = $order_result->fetch_assoc();
                $noDistributor = $order_row['noDistributor'];
                
                // Generate ID: count existing pengiriman for this order
                $id_query = "SELECT COUNT(*) as cnt FROM pengiriman WHERE noPesanan = ?";
                $id_stmt = $koneksi->prepare($id_query);
                $id_stmt->bind_param('s', $noPesanan);
                $id_stmt->execute();
                $id_row = $id_stmt->get_result()->fetch_assoc();
                $next_seq = str_pad($id_row['cnt'] + 1, 2, '0', STR_PAD_LEFT);
                $noPengiriman = 'DO-' . $noPesanan . '-' . $next_seq;
                $id_stmt->close();
                
                $insert_query = "INSERT INTO pengiriman (noPengiriman, noSuratJalan, tanggalKirim, alamatPengiriman, statusPengiriman, noPesanan, noDistributor, idDriver, idUser) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $koneksi->prepare($insert_query);
                $idDriverVal = !empty($idDriver) ? $idDriver : NULL;
                $insert_stmt->bind_param('sssssssss', $noPengiriman, $noSuratJalan, $tanggalKirim, $alamatPengiriman, $statusPengiriman, $noPesanan, $noDistributor, $idDriverVal, $current_user_id);
                
                if ($insert_stmt->execute()) {
                    $_SESSION['success_message'] = 'Pengiriman berhasil ditambahkan!';
                } else {
                    $_SESSION['error_message'] = 'Gagal menambahkan pengiriman!';
                }
                $insert_stmt->close();
            }
            $order_stmt->close();
        } elseif ($action === 'ubah') {
            $noPengiriman = trim($_POST['noPengiriman'] ?? '');
            
            $check_query = "SELECT noPengiriman FROM pengiriman WHERE noPengiriman = ? AND idUser = ?";
            $check_stmt = $koneksi->prepare($check_query);
            $check_stmt->bind_param('ss', $noPengiriman, $current_user_id);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows > 0) {
                $update_query = "UPDATE pengiriman SET noSuratJalan = ?, tanggalKirim = ?, alamatPengiriman = ?, statusPengiriman = ?, idDriver = ? WHERE noPengiriman = ?";
                $update_stmt = $koneksi->prepare($update_query);
                $idDriverVal = !empty($idDriver) ? $idDriver : NULL;
                $update_stmt->bind_param('ssssss', $noSuratJalan, $tanggalKirim, $alamatPengiriman, $statusPengiriman, $idDriverVal, $noPengiriman);
                
                if ($update_stmt->execute()) {
                    $_SESSION['success_message'] = 'Pengiriman berhasil diperbarui!';
                } else {
                    $_SESSION['error_message'] = 'Gagal memperbarui pengiriman!';
                }
                $update_stmt->close();
            }
            $check_stmt->close();
        }
    }
    
    header('Location: index.php?page=pengiriman');
    exit;
}

// READ - Get pengiriman (all for admin, current user's for sales)
$user_role = $_SESSION['role'] ?? 'sales';
if ($user_role === 'admin') {
    // Admin: lihat semua pengiriman
    $query = "SELECT p.noPengiriman, p.noSuratJalan, p.tanggalKirim, p.alamatPengiriman, p.statusPengiriman, p.noPesanan, p.idDriver, d.namaDistributor, u.nama as user_name
              FROM pengiriman p 
              JOIN distributor d ON p.noDistributor = d.noDistributor 
              JOIN user u ON p.idUser = u.idUser
              ORDER BY p.tanggalKirim DESC";
    $stmt = $koneksi->prepare($query);
    $stmt->execute();
} else {
    // Sales: hanya pengiriman mereka
    $query = "SELECT p.noPengiriman, p.noSuratJalan, p.tanggalKirim, p.alamatPengiriman, p.statusPengiriman, p.noPesanan, p.idDriver, d.namaDistributor 
              FROM pengiriman p 
              JOIN distributor d ON p.noDistributor = d.noDistributor 
              WHERE p.idUser = ? ORDER BY p.tanggalKirim DESC";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param('s', $current_user_id);
    $stmt->execute();
}
$pengiriman_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get orders for dropdown
if ($user_role === 'admin') {
    // Admin: lihat semua orders
    $order_query = "SELECT DISTINCT p.noPesanan, d.namaDistributor FROM pesanan p 
                   JOIN distributor d ON p.noDistributor = d.noDistributor 
                   ORDER BY p.noPesanan DESC";
    $order_stmt = $koneksi->prepare($order_query);
    $order_stmt->execute();
} else {
    // Sales: hanya orders mereka
    $order_query = "SELECT DISTINCT p.noPesanan, d.namaDistributor FROM pesanan p 
                   JOIN distributor d ON p.noDistributor = d.noDistributor 
                   WHERE p.idUser = ? ORDER BY p.noPesanan DESC";
    $order_stmt = $koneksi->prepare($order_query);
    $order_stmt->bind_param('s', $current_user_id);
    $order_stmt->execute();
}
$orders = $order_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$order_stmt->close();

// Get drivers for dropdown (users with role='driver')
$driver_query = "SELECT idUser, nama, email FROM user WHERE role = 'driver' ORDER BY nama";
$driver_stmt = $koneksi->prepare($driver_query);
$driver_stmt->execute();
$drivers = $driver_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$driver_stmt->close();

// Calculate status counts
$status_counts = ['Pending' => 0, 'Dikirim' => 0, 'Selesai' => 0];
foreach ($pengiriman_list as $pg) {
    $status = ucfirst($pg['statusPengiriman']);
    if (isset($status_counts[$status])) {
        $status_counts[$status]++;
    }
}
?>

<div class="page-header">
    <h2>Manajemen Pengiriman</h2>
    <p class="page-subtitle">Kelola pengiriman pesanan</p>
</div>

<?php
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']);
}
?>

<div class="page-actions">
    <button class="btn btn-primary btn-lg" onclick="openAddPengirimanModal()">
        + Buat Pengiriman
    </button>
</div>

<!-- Status Summary -->
<div class="status-summary">
    <div class="summary-card">
        <div class="summary-icon">✓</div>
        <div class="summary-content">
            <p class="summary-label">Selesai</p>
            <p class="summary-value"><?php echo $status_counts['Selesai']; ?> pengiriman</p>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon">→</div>
        <div class="summary-content">
            <p class="summary-label">Dikirim</p>
            <p class="summary-value"><?php echo $status_counts['Dikirim']; ?> pengiriman</p>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon">⏳</div>
        <div class="summary-content">
            <p class="summary-label">Pending</p>
            <p class="summary-value"><?php echo $status_counts['Pending']; ?> pengiriman</p>
        </div>
    </div>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th style="width: 15%;">No Pengiriman</th>
                <th style="width: 15%;">No Surat Jalan</th>
                <th style="width: 15%;">Tanggal Kirim</th>
                <th style="width: 25%;">Alamat Pengiriman</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 30%;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pengiriman_list)): ?>
                <tr><td colspan="6" style="text-align:center;color:#999;">Belum ada pengiriman</td></tr>
            <?php else: ?>
                <?php foreach ($pengiriman_list as $pg): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($pg['noPengiriman']); ?></strong></td>
                        <td><?php echo htmlspecialchars($pg['noSuratJalan']); ?></td>
                        <td><?php echo date('d M Y', strtotime($pg['tanggalKirim'])); ?></td>
                        <td><?php echo htmlspecialchars($pg['alamatPengiriman']); ?></td>
                        <td><span class="badge badge-<?php echo strtolower(str_replace(' ', '', $pg['statusPengiriman'])); ?>"><?php echo ucfirst($pg['statusPengiriman']); ?></span></td>
                        <td class="action-cell">
                            <button class="btn-action btn-edit" onclick="openEditPengirimanModal('<?php echo htmlspecialchars($pg['noPengiriman']); ?>', '<?php echo htmlspecialchars($pg['noSuratJalan']); ?>', '<?php echo htmlspecialchars($pg['tanggalKirim']); ?>', '<?php echo htmlspecialchars(str_replace("'", "\\\"", $pg['alamatPengiriman'])); ?>', '<?php echo htmlspecialchars($pg['statusPengiriman']); ?>', '<?php echo htmlspecialchars($pg['noPesanan']); ?>', '<?php echo htmlspecialchars($pg['idDriver'] ?? ''); ?>')">Edit</button>
                            <button class="btn-action btn-delete" onclick="if(confirm('Hapus pengiriman ini?')) window.location.href='index.php?page=pengiriman&hapus=<?php echo htmlspecialchars($pg['noPengiriman']); ?>'">Hapus</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
window.ordersData = <?php echo json_encode($orders); ?>;
window.driversData = <?php echo json_encode($drivers); ?>;

function openAddPengirimanModal() {
    openModal('Buat Pengiriman Baru', 'pengirimanForm');
    
    setTimeout(() => {
        const form = document.getElementById('dynamicForm');
        if (form) form.reset();
        
        let idField = document.getElementById('noPengirimanHidden');
        if (idField) idField.remove();
        
        updateOrderSelect();
        updateDriverSelect();
    }, 120);
    
    setTimeout(() => {
        document.getElementById('dynamicForm').onsubmit = function(e) {
            e.preventDefault();
            submitPengirimanForm('tambah');
        };
    }, 130);
}

function openEditPengirimanModal(noPengiriman, noSuratJalan, tanggalKirim, alamatPengiriman, statusPengiriman, noPesanan, idDriver) {
    openModal('Edit Pengiriman', 'pengirimanForm');
    
    setTimeout(() => {
        updateOrderSelect(noPesanan);
        updateDriverSelect(idDriver);
        document.getElementById('noSuratJalan').value = noSuratJalan;
        document.getElementById('tanggalKirim').value = tanggalKirim;
        document.getElementById('alamatPengiriman').value = alamatPengiriman;
        document.getElementById('statusPengiriman').value = statusPengiriman;
        document.getElementById('noPesanan').value = noPesanan;
        
        let form = document.getElementById('dynamicForm');
        let idField = document.getElementById('noPengirimanHidden');
        if (!idField) {
            idField = document.createElement('input');
            idField.type = 'hidden';
            idField.id = 'noPengirimanHidden';
            idField.name = 'noPengiriman';
            form.appendChild(idField);
        }
        idField.value = noPengiriman;
        
        form.onsubmit = function(e) {
            e.preventDefault();
            submitPengirimanForm('ubah');
        };
    }, 50);
}

function updateOrderSelect(selectedId = null) {
    const select = document.getElementById('noPesanan');
    if (!select) return;
    
    select.innerHTML = '<option value="">Pilih Order</option>';
    window.ordersData.forEach(o => {
        select.innerHTML += `<option value="${o.noPesanan}">${o.noPesanan} - ${o.namaDistributor}</option>`;
    });
    if (selectedId) select.value = selectedId;
}

function updateDriverSelect(selectedId = null) {
    const select = document.getElementById('idDriver');
    if (!select) return;

    // Default option
    select.innerHTML = '<option value="">Pilih Driver</option>';

    // If no driver data available, show disabled placeholder and disable select
    if (!window.driversData || !Array.isArray(window.driversData) || window.driversData.length === 0) {
        select.innerHTML = '<option value="" disabled selected>Data driver belum tersedia</option>';
        select.disabled = true;
        return;
    }

    // Populate drivers and enable select
    select.disabled = false;
    window.driversData.forEach(d => {
        const option = document.createElement('option');
        option.value = d.idUser;
        option.textContent = d.nama + ' (' + d.email + ')';
        select.appendChild(option);
    });
    if (selectedId) select.value = selectedId;
}

function submitPengirimanForm(action) {
    const form = document.getElementById('dynamicForm');
    const formData = new FormData(form);
    formData.append('action', action);
    
    const actualForm = document.createElement('form');
    actualForm.method = 'POST';
    actualForm.action = 'index.php?page=pengiriman';
    
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        actualForm.appendChild(input);
    }
    
    document.body.appendChild(actualForm);
    actualForm.submit();
}
</script>
