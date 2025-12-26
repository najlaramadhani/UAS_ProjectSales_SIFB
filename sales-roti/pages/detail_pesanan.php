<?php
/**
 * Order Detail Items (Detail Pesanan) Management - CRUD Operations
 * Single file handling order line items
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
    $idDetail = trim($_GET['hapus']);
    
    // Verify permission through pesanan->idUser
    $check_query = "SELECT d.idDetail FROM detail_pesanan d 
                   JOIN pesanan p ON d.noPesanan = p.noPesanan 
                   WHERE d.idDetail = ? AND p.idUser = ?";
    $check_stmt = $koneksi->prepare($check_query);
    $check_stmt->bind_param('ss', $idDetail, $current_user_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        $delete_query = "DELETE FROM detail_pesanan WHERE idDetail = ?";
        $delete_stmt = $koneksi->prepare($delete_query);
        $delete_stmt->bind_param('s', $idDetail);
        if ($delete_stmt->execute()) {
            $_SESSION['success_message'] = 'Item order berhasil dihapus!';
        }
        $delete_stmt->close();
    }
    $check_stmt->close();
    
    $noPesanan = isset($_GET['pesanan']) ? trim($_GET['pesanan']) : '';
    $redirect = !empty($noPesanan) ? "index.php?page=detail_pesanan&pesanan=$noPesanan" : 'index.php?page=order';
    header('Location: ' . $redirect);
    exit;
}

// Handle CREATE/UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $noPesanan = trim($_POST['noPesanan'] ?? '');
    $idProduk = trim($_POST['produk'] ?? '');
    $jumlah = intval($_POST['jumlah'] ?? 0);
    
    // Verify order ownership
    $order_check = "SELECT noPesanan FROM pesanan WHERE noPesanan = ? AND idUser = ?";
    $order_stmt = $koneksi->prepare($order_check);
    $order_stmt->bind_param('ss', $noPesanan, $current_user_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();
    $order_stmt->close();
    
    if ($order_result->num_rows === 0) {
        $_SESSION['error_message'] = 'Order tidak ditemukan!';
        header('Location: index.php?page=order');
        exit;
    }
    
    if (empty($idProduk) || $jumlah <= 0) {
        $_SESSION['error_message'] = 'Produk dan jumlah harus valid!';
    } else {
        // Get product price
        $price_query = "SELECT harga FROM produk WHERE idProduk = ?";
        $price_stmt = $koneksi->prepare($price_query);
        $price_stmt->bind_param('s', $idProduk);
        $price_stmt->execute();
        $price_result = $price_stmt->get_result();
        
        if ($price_result->num_rows === 0) {
            $_SESSION['error_message'] = 'Produk tidak ditemukan!';
        } else {
            $price_row = $price_result->fetch_assoc();
            $hargaSatuan = $price_row['harga'];
            $totalHarga = $hargaSatuan * $jumlah;
            
            if ($action === 'tambah') {
                // Generate unique detail ID: DTL-{noPesanan}-{seq}
                $seq_query = "SELECT COUNT(*) as cnt FROM detail_pesanan WHERE noPesanan = ?";
                $seq_stmt = $koneksi->prepare($seq_query);
                $seq_stmt->bind_param('s', $noPesanan);
                $seq_stmt->execute();
                $seq_row = $seq_stmt->get_result()->fetch_assoc();
                $seq = str_pad($seq_row['cnt'] + 1, 2, '0', STR_PAD_LEFT);
                $idDetail = "DTL-$noPesanan-$seq";
                
                $insert_query = "INSERT INTO detail_pesanan (idDetail, jumlah, hargaSatuan, totalHarga, noPesanan, idProduk) VALUES (?, ?, ?, ?, ?, ?)";
                $insert_stmt = $koneksi->prepare($insert_query);
                $insert_stmt->bind_param('siddsss', $idDetail, $jumlah, $hargaSatuan, $totalHarga, $noPesanan, $idProduk);
                
                if ($insert_stmt->execute()) {
                    $_SESSION['success_message'] = 'Item order berhasil ditambahkan!';
                } else {
                    $_SESSION['error_message'] = 'Gagal menambahkan item: ' . $koneksi->error;
                }
                $insert_stmt->close();
            } elseif ($action === 'ubah') {
                $idDetail = trim($_POST['idDetail'] ?? '');
                
                $update_query = "UPDATE detail_pesanan SET jumlah = ?, hargaSatuan = ?, totalHarga = ?, idProduk = ? WHERE idDetail = ? AND noPesanan = ?";
                $update_stmt = $koneksi->prepare($update_query);
                $update_stmt->bind_param('iddsss', $jumlah, $hargaSatuan, $totalHarga, $idProduk, $idDetail, $noPesanan);
                
                if ($update_stmt->execute()) {
                    $_SESSION['success_message'] = 'Item order berhasil diperbarui!';
                } else {
                    $_SESSION['error_message'] = 'Gagal memperbarui item!';
                }
                $update_stmt->close();
            }
        }
        $price_stmt->close();
    }
    
    header('Location: index.php?page=detail_pesanan&pesanan=' . $noPesanan);
    exit;
}

// READ - Get order details
$noPesanan = isset($_GET['pesanan']) ? trim($_GET['pesanan']) : '';

// JSON endpoint for AJAX: return detail items for a given order
if (isset($_GET['fetch']) && $_GET['fetch'] === 'json' && !empty($noPesanan)) {
    // verify ownership
    $check = "SELECT noPesanan FROM pesanan WHERE noPesanan = ? AND idUser = ?";
    $cstmt = $koneksi->prepare($check);
    $cstmt->bind_param('ss', $noPesanan, $current_user_id);
    $cstmt->execute();
    $cres = $cstmt->get_result();
    $cstmt->close();

    if ($cres->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Order not found or no permission']);
        exit;
    }

        $dq = "SELECT dp.idDetail, dp.idProduk, dp.jumlah, dp.hargaSatuan, dp.totalHarga, pr.namaProduk 
            FROM detail_pesanan dp LEFT JOIN produk pr ON dp.idProduk = pr.idProduk 
            WHERE dp.noPesanan = ? ORDER BY dp.idDetail";
    $dstmt = $koneksi->prepare($dq);
    $dstmt->bind_param('s', $noPesanan);
    $dstmt->execute();
    $dres = $dstmt->get_result();
    $items = $dres->fetch_all(MYSQLI_ASSOC);
    $dstmt->close();

    header('Content-Type: application/json');
    echo json_encode($items);
    exit;
}

if (empty($noPesanan)) {
    // Show list of all orders if no pesanan specified
    $query = "SELECT p.noPesanan, p.tanggalOrder, d.namaDistributor FROM pesanan p 
              JOIN distributor d ON p.noDistributor = d.noDistributor 
              WHERE p.idUser = ? ORDER BY p.tanggalOrder DESC";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param('s', $current_user_id);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $details = [];
} else {
    // Get order details
    $query = "SELECT dp.idDetail, dp.idProduk, dp.jumlah, dp.hargaSatuan, dp.totalHarga, pr.namaProduk 
              FROM detail_pesanan dp 
              JOIN produk pr ON dp.idProduk = pr.idProduk 
              WHERE dp.noPesanan = ? ORDER BY dp.idDetail";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param('s', $noPesanan);
    $stmt->execute();
    $details = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Get order header
    $header_query = "SELECT p.noPesanan, p.tanggalOrder, d.namaDistributor, p.status 
                    FROM pesanan p 
                    JOIN distributor d ON p.noDistributor = d.noDistributor 
                    WHERE p.noPesanan = ? AND p.idUser = ?";
    $header_stmt = $koneksi->prepare($header_query);
    $header_stmt->bind_param('ss', $noPesanan, $current_user_id);
    $header_stmt->execute();
    $order_header = $header_stmt->get_result()->fetch_assoc();
    $header_stmt->close();
}

// Get products for dropdown (fetch all products from DB)
$prod_query = "SELECT idProduk, namaProduk, harga FROM produk ORDER BY namaProduk";
$prod_stmt = $koneksi->prepare($prod_query);
$prod_stmt->execute();
$products = $prod_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$prod_stmt->close();
?>

<div class="page-header">
    <h2><?php echo !empty($noPesanan) ? 'Detail Item Order' : 'Kelola Item Order'; ?></h2>
    <p class="page-subtitle"><?php echo !empty($noPesanan) ? 'Kelola produk dalam order ' . htmlspecialchars($noPesanan) : 'Pilih order untuk mengelola item'; ?></p>
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

<?php if (!empty($noPesanan) && !empty($order_header)): ?>
    <div class="order-summary" style="background: #f9f9f9; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
            <div>
                <p style="color: #999; font-size: 12px; margin: 0;">No Order</p>
                <p style="font-size: 16px; font-weight: bold;"><?php echo htmlspecialchars($order_header['noPesanan']); ?></p>
            </div>
            <div>
                <p style="color: #999; font-size: 12px; margin: 0;">Distributor</p>
                <p style="font-size: 16px; font-weight: bold;"><?php echo htmlspecialchars($order_header['namaDistributor']); ?></p>
            </div>
            <div>
                <p style="color: #999; font-size: 12px; margin: 0;">Tanggal</p>
                <p style="font-size: 16px; font-weight: bold;"><?php echo date('d M Y', strtotime($order_header['tanggalOrder'])); ?></p>
            </div>
        </div>
    </div>

    <div class="page-actions">
        <button class="btn btn-primary btn-lg" onclick="openAddDetailModal()">
            + Tambah Item
        </button>
        <a href="index.php?page=order" class="btn btn-secondary" style="text-decoration: none;">← Kembali ke Order</a>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga Satuan</th>
                    <th>Jumlah</th>
                    <th>Total Harga</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($details)): ?>
                    <tr><td colspan="5" style="text-align:center;color:#999;">Belum ada item dalam order ini</td></tr>
                <?php else: ?>
                    <?php $total_order = 0; ?>
                    <?php foreach ($details as $detail): ?>
                        <?php $total_order += $detail['totalHarga']; ?>
                        <?php
                            // Fallback: if namaProduk is missing (join failed), try to parse idProduk value
                            $displayName = $detail['namaProduk'] ?? '';
                            if (empty($displayName)) {
                                $raw = $detail['idProduk'];
                                if (strpos($raw, '|') !== false) {
                                    $parts = explode('|', $raw);
                                    $displayName = trim($parts[0]);
                                } else {
                                    $displayName = $raw;
                                }
                            }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($displayName); ?></td>
                            <td>Rp <?php echo number_format($detail['hargaSatuan'], 0, ',', '.'); ?></td>
                            <td><?php echo $detail['jumlah']; ?></td>
                            <td><strong>Rp <?php echo number_format($detail['totalHarga'], 0, ',', '.'); ?></strong></td>
                            <td class="action-cell">
                                <button class="btn-action btn-edit" onclick="openEditDetailModal('<?php echo htmlspecialchars($detail['idDetail']); ?>', '<?php echo htmlspecialchars($detail['idProduk']); ?>', '<?php echo $detail['jumlah']; ?>')">Edit</button>
                                <button class="btn-action btn-delete" onclick="if(confirm('Hapus item ini?')) window.location.href='index.php?page=detail_pesanan&hapus=<?php echo htmlspecialchars($detail['idDetail']); ?>&pesanan=<?php echo htmlspecialchars($noPesanan); ?>'">Hapus</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background: #f0f0f0; font-weight: bold;">
                        <td colspan="3" style="text-align: right;">Total Order:</td>
                        <td>Rp <?php echo number_format($total_order, 0, ',', '.'); ?></td>
                        <td></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No Order</th>
                    <th>Tanggal</th>
                    <th>Distributor</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="4" style="text-align:center;color:#999;">Belum ada order</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($order['noPesanan']); ?></strong></td>
                            <td><?php echo date('d M Y', strtotime($order['tanggalOrder'])); ?></td>
                            <td><?php echo htmlspecialchars($order['namaDistributor']); ?></td>
                            <td class="action-cell">
                                <a href="index.php?page=detail_pesanan&pesanan=<?php echo htmlspecialchars($order['noPesanan']); ?>" class="btn-action btn-view">Lihat Item</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<script>
window.productsData = <?php echo json_encode($products); ?>;
window.currentNoPesanan = '<?php echo htmlspecialchars($noPesanan); ?>';

function openAddDetailModal() {
    openModal('Tambah Item Order', 'detailPesananForm');
    document.getElementById('dynamicForm').reset();
    
    let idField = document.getElementById('idDetailHidden');
    if (idField) idField.remove();
    
    updateProductSelect();
    
    document.getElementById('dynamicForm').onsubmit = function(e) {
        e.preventDefault();
        submitDetailForm('tambah');
    };
}

function openEditDetailModal(idDetail, idProduk, jumlah) {
    openModal('Edit Item Order', 'detailPesananForm');
    
    setTimeout(() => {
        updateProductSelect(idProduk);
        document.getElementById('produk').value = idProduk;
        document.getElementById('jumlah').value = jumlah;
        
        let form = document.getElementById('dynamicForm');
        let idField = document.getElementById('idDetailHidden');
        if (!idField) {
            idField = document.createElement('input');
            idField.type = 'hidden';
            idField.id = 'idDetailHidden';
            idField.name = 'idDetail';
            form.appendChild(idField);
        }
        idField.value = idDetail;
        
        form.onsubmit = function(e) {
            e.preventDefault();
            submitDetailForm('ubah');
        };
    }, 50);
}

function updateProductSelect(selectedId = null) {
    const select = document.getElementById('produk');
    if (!select) return;
    
    select.innerHTML = '<option value="">Pilih Produk</option>';
    window.productsData.forEach(p => {
        select.innerHTML += `<option value="${p.idProduk}">${p.namaProduk} (Rp ${parseFloat(p.harga).toLocaleString('id-ID')})</option>`;
    });
    if (selectedId) select.value = selectedId;
}

function submitDetailForm(action) {
    const form = document.getElementById('dynamicForm');
    const formData = new FormData(form);
    formData.append('action', action);
    formData.append('noPesanan', window.currentNoPesanan);
    
    const actualForm = document.createElement('form');
    actualForm.method = 'POST';
    actualForm.action = 'index.php?page=detail_pesanan';
    
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
