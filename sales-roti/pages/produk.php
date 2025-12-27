<?php
/**
 * Product Management - CRUD Operations (Admin Only)
 * Handle product creation, editing, deletion
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/koneksi.php';

// SECURITY: Only admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?page=dashboard');
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Handle DELETE
if (isset($_GET['hapus'])) {
    $idProduk = trim($_GET['hapus']);
    
    $delete_query = "DELETE FROM produk WHERE idProduk = ?";
    $delete_stmt = $koneksi->prepare($delete_query);
    $delete_stmt->bind_param('s', $idProduk);
    if ($delete_stmt->execute()) {
        $_SESSION['success_message'] = 'Produk berhasil dihapus!';
    } else {
        $_SESSION['error_message'] = 'Gagal menghapus produk!';
    }
    $delete_stmt->close();
    
    header('Location: index.php?page=produk');
    exit;
}

// Handle CREATE/UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $namaProduk = trim($_POST['namaProduk'] ?? '');
    $harga = trim($_POST['harga'] ?? '');
    $stok = trim($_POST['stok'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    
    if (empty($namaProduk) || empty($harga) || empty($stok)) {
        $_SESSION['error_message'] = 'Nama produk, harga, dan stok harus diisi!';
    } else {
        $harga = floatval($harga);
        $stok = intval($stok);
        
        if ($harga < 0 || $stok < 0) {
            $_SESSION['error_message'] = 'Harga dan stok tidak boleh negatif!';
        } else {
            if ($action === 'tambah') {
                // Generate ID: RTI + 5 digit sequential (RTI01001, RTI01002, dst)
                $id_max_query = "SELECT MAX(CAST(SUBSTRING(idProduk, 4) AS UNSIGNED)) as max_num FROM produk WHERE idProduk LIKE 'RTI%'";
                $id_max_result = $koneksi->query($id_max_query);
                $id_max_row = $id_max_result->fetch_assoc();
                $next_num = ($id_max_row['max_num'] ?? 0) + 1;
                $idProduk = 'RTI' . str_pad($next_num, 5, '0', STR_PAD_LEFT);
                
                $insert_query = "INSERT INTO produk (idProduk, namaProduk, harga, stok, deskripsi, idUser) VALUES (?, ?, ?, ?, ?, ?)";
                $insert_stmt = $koneksi->prepare($insert_query);
                $insert_stmt->bind_param('ssidss', $idProduk, $namaProduk, $harga, $stok, $deskripsi, $current_user_id);
                
                if ($insert_stmt->execute()) {
                    $_SESSION['success_message'] = 'Produk berhasil ditambahkan!';
                } else {
                    $_SESSION['error_message'] = 'Gagal menambahkan produk!';
                }
                $insert_stmt->close();
            } elseif ($action === 'ubah') {
                $idProduk = trim($_POST['idProduk'] ?? '');
                
                $update_query = "UPDATE produk SET namaProduk = ?, harga = ?, stok = ?, deskripsi = ? WHERE idProduk = ?";
                $update_stmt = $koneksi->prepare($update_query);
                $update_stmt->bind_param('sidss', $namaProduk, $harga, $stok, $deskripsi, $idProduk);
                
                if ($update_stmt->execute()) {
                    $_SESSION['success_message'] = 'Produk berhasil diperbarui!';
                } else {
                    $_SESSION['error_message'] = 'Gagal memperbarui produk!';
                }
                $update_stmt->close();
            }
        }
    }
    
    header('Location: index.php?page=produk');
    exit;
}

// READ - Get all products
$query = "SELECT pr.idProduk, pr.namaProduk, pr.harga, pr.stok, pr.deskripsi, u.nama as pencatat, pr.idUser
          FROM produk pr
          JOIN user u ON pr.idUser = u.idUser
          ORDER BY pr.namaProduk";
$stmt = $koneksi->prepare($query);
$stmt->execute();
$produk_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate statistics
$total_stok = 0;
$total_nilai = 0;
$low_stok_count = 0;

foreach ($produk_list as $p) {
    $total_stok += $p['stok'];
    $total_nilai += $p['harga'] * $p['stok'];
    if ($p['stok'] <= 10) {
        $low_stok_count++;
    }
}

function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
?>

<div class="page-header">
    <h2>Manajemen Produk</h2>
    <p class="page-subtitle">Kelola semua produk di sistem</p>
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
    <button class="btn btn-primary btn-lg" onclick="openAddProdukModal()">
        + Tambah Produk
    </button>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th style="width: 12%;">ID Produk</th>
                <th style="width: 18%;">Nama Produk</th>
                <th style="width: 12%;">Harga</th>
                <th style="width: 8%;">Stok</th>
                <th style="width: 15%;">Nilai Stok</th>
                <th style="width: 15%;">Pencatat</th>
                <th style="width: 20%;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($produk_list)): ?>
                <tr><td colspan="7" style="text-align:center;color:#999;">Belum ada produk</td></tr>
            <?php else: ?>
                <?php foreach ($produk_list as $p): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($p['idProduk']); ?></strong></td>
                        <td><?php echo htmlspecialchars($p['namaProduk']); ?></td>
                        <td><?php echo formatCurrency($p['harga']); ?></td>
                        <td>
                            <?php echo $p['stok']; ?>
                            <?php if ($p['stok'] <= 10): ?>
                                <span class="badge badge-warning" style="margin-left: 5px;">Rendah</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatCurrency($p['harga'] * $p['stok']); ?></td>
                        <td><?php echo htmlspecialchars($p['pencatat']); ?></td>
                        <td class="action-cell">
                            <button class="btn-action btn-edit" onclick="openEditProdukModal('<?php echo htmlspecialchars($p['idProduk']); ?>', '<?php echo htmlspecialchars($p['namaProduk']); ?>', '<?php echo htmlspecialchars($p['harga']); ?>', '<?php echo htmlspecialchars($p['stok']); ?>', '<?php echo htmlspecialchars(str_replace("'", "\\'", $p['deskripsi'])); ?>')">Edit</button>
                            <button class="btn-action btn-delete" onclick="if(confirm('Hapus produk ini?')) window.location.href='index.php?page=produk&hapus=<?php echo htmlspecialchars($p['idProduk']); ?>'">Hapus</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Inventory Statistics -->
<div class="status-summary">
    <div class="summary-card">
        <div class="summary-icon">📦</div>
        <div class="summary-content">
            <p class="summary-label">Total Produk</p>
            <p class="summary-value"><?php echo count($produk_list); ?> item</p>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon">📊</div>
        <div class="summary-content">
            <p class="summary-label">Total Stok</p>
            <p class="summary-value"><?php echo number_format($total_stok, 0); ?> unit</p>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon">💰</div>
        <div class="summary-content">
            <p class="summary-label">Nilai Inventory</p>
            <p class="summary-value"><?php echo formatCurrency($total_nilai); ?></p>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon">⚠️</div>
        <div class="summary-content">
            <p class="summary-label">Stok Rendah</p>
            <p class="summary-value"><?php echo $low_stok_count; ?> produk</p>
        </div>
    </div>
</div>

<script>
function openAddProdukModal() {
    openModal('Tambah Produk Baru', 'produkForm');
    
    setTimeout(() => {
        const form = document.getElementById('dynamicForm');
        if (form) form.reset();
        
        let idField = document.getElementById('idProdukHidden');
        if (idField) idField.remove();
        
        form.onsubmit = function(e) {
            e.preventDefault();
            submitProdukForm('tambah');
        };
    }, 100);
}

function openEditProdukModal(idProduk, namaProduk, harga, stok, deskripsi) {
    openModal('Edit Produk', 'produkForm');
    
    setTimeout(() => {
        document.getElementById('namaProduk').value = namaProduk;
        document.getElementById('harga').value = harga;
        document.getElementById('stok').value = stok;
        document.getElementById('deskripsi').value = deskripsi;
        
        let form = document.getElementById('dynamicForm');
        let idField = document.getElementById('idProdukHidden');
        if (!idField) {
            idField = document.createElement('input');
            idField.type = 'hidden';
            idField.id = 'idProdukHidden';
            idField.name = 'idProduk';
            form.appendChild(idField);
        }
        idField.value = idProduk;
        
        form.onsubmit = function(e) {
            e.preventDefault();
            submitProdukForm('ubah');
        };
    }, 50);
}

function submitProdukForm(action) {
    const form = document.getElementById('dynamicForm');
    const formData = new FormData(form);
    formData.append('action', action);
    
    const actualForm = document.createElement('form');
    actualForm.method = 'POST';
    actualForm.action = 'index.php?page=produk';
    
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
