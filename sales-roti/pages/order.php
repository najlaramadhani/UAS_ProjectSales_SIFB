<?php
/**
 * Order (Pesanan) Management - CRUD Operations
 * Single file handling all order operations
 * Accessible only to SALES role
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/koneksi.php';

// For JSON endpoint, check session but don't redirect - return JSON error instead
$is_json_request = isset($_GET['fetch']) && $_GET['fetch'] === 'json';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'sales') {
    if ($is_json_request) {
        // Return JSON error for AJAX requests
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized - please login']);
        exit;
    } else {
        // Redirect for regular page requests
        header('Location: login.php');
        exit;
    }
}

$current_user_id = $_SESSION['user_id'];

// Handle DELETE
if (isset($_GET['hapus'])) {
    $noPesanan = trim($_GET['hapus']);
    $check_query = "SELECT noPesanan FROM pesanan WHERE noPesanan = ? AND idUser = ?";
    $check_stmt = $koneksi->prepare($check_query);
    $check_stmt->bind_param('ss', $noPesanan, $current_user_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        // Delete detail_pesanan first (FK constraint)
        $koneksi->query("DELETE FROM detail_pesanan WHERE noPesanan = '$noPesanan'");
        // Then delete pesanan
        $delete_query = "DELETE FROM pesanan WHERE noPesanan = ?";
        $delete_stmt = $koneksi->prepare($delete_query);
        $delete_stmt->bind_param('s', $noPesanan);
        if ($delete_stmt->execute()) {
            $_SESSION['success_message'] = 'Order berhasil dihapus!';
        }
        $delete_stmt->close();
    }
    $check_stmt->close();
    header('Location: index.php?page=order');
    exit;
}

// Handle CREATE/UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'tambah') {
        $tanggalOrder = trim($_POST['tanggalOrder'] ?? '');
        $noDistributor = trim($_POST['distributor'] ?? '');
        $status = trim($_POST['status'] ?? 'Pending');
        
        if (empty($tanggalOrder) || empty($noDistributor)) {
            $_SESSION['error_message'] = 'Tanggal dan distributor harus diisi!';
        } else if (!isset($_POST['produk']) || !is_array($_POST['produk']) || count($_POST['produk']) === 0) {
            $_SESSION['error_message'] = 'Minimal harus ada satu produk!';
        } else {
            // ========================================
            // START DATABASE TRANSACTION
            // ========================================
            $koneksi->begin_transaction();
            
            try {
                // ========================================
                // STEP 1: Generate nomor pesanan
                // ========================================
                $dateStr = date('Ym', strtotime($tanggalOrder));
                $likePattern = 'SO' . $dateStr . '%';
                
                $id_query = "SELECT MAX(CAST(SUBSTRING(noPesanan, 9) AS UNSIGNED)) as max_seq FROM pesanan WHERE noPesanan LIKE ? AND idUser = ?";
                $id_stmt = $koneksi->prepare($id_query);
                if (!$id_stmt) throw new Exception("Prepare failed: " . $koneksi->error);
                
                $id_stmt->bind_param('ss', $likePattern, $current_user_id);
                if (!$id_stmt->execute()) throw new Exception("Execute failed: " . $id_stmt->error);
                
                $id_result = $id_stmt->get_result();
                $id_row = $id_result->fetch_assoc();
                $next_num = (isset($id_row['max_seq']) && $id_row['max_seq'] !== null) ? intval($id_row['max_seq']) + 1 : 1;
                $next_seq = str_pad($next_num, 3, '0', STR_PAD_LEFT);
                $noPesanan = 'SO' . $dateStr . $next_seq;
                $id_stmt->close();
                
                // ========================================
                // STEP 2: INSERT ke tabel PESANAN
                // ========================================
                $insert_query = "INSERT INTO pesanan (noPesanan, tanggalOrder, status, idUser, noDistributor) VALUES (?, ?, ?, ?, ?)";
                $insert_stmt = $koneksi->prepare($insert_query);
                if (!$insert_stmt) throw new Exception("Prepare failed: " . $koneksi->error);
                
                $insert_stmt->bind_param('sssss', $noPesanan, $tanggalOrder, $status, $current_user_id, $noDistributor);
                if (!$insert_stmt->execute()) throw new Exception("Insert pesanan failed: " . $insert_stmt->error);
                $insert_stmt->close();
                
                // ========================================
                // STEP 3: INSERT ke tabel DETAIL_PESANAN
                // ========================================
                $produk_arr = $_POST['produk'];
                $jumlah_arr = $_POST['jumlah'] ?? [];
                $detail_seq = 1;
                
                foreach ($produk_arr as $i => $idProduk) {
                    $idProduk = trim($idProduk);
                    $jumlah = intval($jumlah_arr[$i] ?? 0);
                    
                    if (empty($idProduk) || $jumlah <= 0) continue;
                    
                    // Ambil harga dari database produk
                    $price_query = "SELECT harga FROM produk WHERE idProduk = ?";
                    $price_stmt = $koneksi->prepare($price_query);
                    if (!$price_stmt) throw new Exception("Prepare failed: " . $koneksi->error);
                    
                    $price_stmt->bind_param('s', $idProduk);
                    if (!$price_stmt->execute()) throw new Exception("Execute failed: " . $price_stmt->error);
                    
                    $price_res = $price_stmt->get_result();
                    if ($price_res->num_rows === 0) {
                        $price_stmt->close();
                        throw new Exception("Produk dengan ID $idProduk tidak ditemukan!");
                    }
                    
                    $price_row = $price_res->fetch_assoc();
                    $hargaSatuan = floatval($price_row['harga']);
                    $price_stmt->close();

                    $totalHarga = $hargaSatuan * $jumlah;

                    $seq_str = str_pad($detail_seq, 2, '0', STR_PAD_LEFT);
                    $idDetail = "DTL-$noPesanan-$seq_str";

                    // Insert setiap produk yang diinput
                    $insert_detail_query = "INSERT INTO detail_pesanan (idDetail, noPesanan, idProduk, jumlah, hargaSatuan, totalHarga) VALUES (?, ?, ?, ?, ?, ?)";
                    $insert_detail_stmt = $koneksi->prepare($insert_detail_query);
                    $insert_detail_stmt->bind_param('sssidd', $idDetail, $noPesanan, $idProduk, $jumlah, $hargaSatuan, $totalHarga);
                    if (!$insert_detail_stmt->execute()) throw new Exception("Insert detail failed: " . $insert_detail_stmt->error);
                    $insert_detail_stmt->close();
                    
                    $detail_seq++;
                }
                
                // ========================================
                // STEP 4: COMMIT TRANSACTION
                // ========================================
                $koneksi->commit();
                $_SESSION['success_message'] = "Pesanan $noPesanan berhasil dibuat dengan " . ($detail_seq - 1) . " item!";
                
            } catch (Exception $e) {
                $koneksi->rollback();
                $_SESSION['error_message'] = 'Gagal membuat pesanan: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'ubah') {
        $noPesanan = trim($_POST['noPesanan'] ?? '');
        $tanggalOrder = trim($_POST['tanggalOrder'] ?? '');
        $noDistributor = trim($_POST['distributor'] ?? '');
        $status = trim($_POST['status'] ?? 'Pending');
        
        $check_query = "SELECT noPesanan FROM pesanan WHERE noPesanan = ? AND idUser = ?";
        $check_stmt = $koneksi->prepare($check_query);
        $check_stmt->bind_param('ss', $noPesanan, $current_user_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            // START TRANSACTION untuk update
            $koneksi->begin_transaction();
            
            try {
                $update_query = "UPDATE pesanan SET tanggalOrder = ?, status = ?, noDistributor = ? WHERE noPesanan = ?";
                $update_stmt = $koneksi->prepare($update_query);
                if (!$update_stmt) throw new Exception("Prepare failed: " . $koneksi->error);
                
                $update_stmt->bind_param('ssss', $tanggalOrder, $status, $noDistributor, $noPesanan);
                if (!$update_stmt->execute()) throw new Exception("Update pesanan failed: " . $update_stmt->error);
                $update_stmt->close();
                
                // If products were submitted with edit, replace detail_pesanan rows
                if (isset($_POST['produk']) && is_array($_POST['produk'])) {
                    // Delete existing items
                    $del_stmt = $koneksi->prepare("DELETE FROM detail_pesanan WHERE noPesanan = ?");
                    if (!$del_stmt) throw new Exception("Prepare failed: " . $koneksi->error);
                    
                    $del_stmt->bind_param('s', $noPesanan);
                    if (!$del_stmt->execute()) throw new Exception("Delete detail failed: " . $del_stmt->error);
                    $del_stmt->close();

                    // Insert new items
                    $produk_arr = $_POST['produk'];
                    $jumlah_arr = $_POST['jumlah'] ?? [];
                    $detail_seq = 1;
                    
                    foreach ($produk_arr as $i => $idProduk) {
                        $idProduk = trim($idProduk);
                        $jumlah = intval($jumlah_arr[$i] ?? 0);
                        
                        if (empty($idProduk) || $jumlah <= 0) continue;

                        // Ambil harga dari database produk
                        $price_query = "SELECT harga FROM produk WHERE idProduk = ?";
                        $price_stmt = $koneksi->prepare($price_query);
                        if (!$price_stmt) throw new Exception("Prepare failed: " . $koneksi->error);
                        
                        $price_stmt->bind_param('s', $idProduk);
                        if (!$price_stmt->execute()) throw new Exception("Execute failed: " . $price_stmt->error);
                        
                        $price_res = $price_stmt->get_result();
                        if ($price_res->num_rows === 0) {
                            $price_stmt->close();
                            throw new Exception("Produk dengan ID $idProduk tidak ditemukan!");
                        }
                        
                        $price_row = $price_res->fetch_assoc();
                        $hargaSatuan = floatval($price_row['harga']);
                        $price_stmt->close();

                        $totalHarga = $hargaSatuan * $jumlah;

                        $seq_str = str_pad($detail_seq, 2, '0', STR_PAD_LEFT);
                        $idDetail = "DTL-$noPesanan-$seq_str";

                        // Insert setiap produk yang diinput
                        $insert_detail_query = "INSERT INTO detail_pesanan (idDetail, noPesanan, idProduk, jumlah, hargaSatuan, totalHarga) VALUES (?, ?, ?, ?, ?, ?)";
                        $insert_detail_stmt = $koneksi->prepare($insert_detail_query);
                        $insert_detail_stmt->bind_param('sssidd', $idDetail, $noPesanan, $idProduk, $jumlah, $hargaSatuan, $totalHarga);
                        if (!$insert_detail_stmt->execute()) throw new Exception("Insert detail failed: " . $insert_detail_stmt->error);
                        $insert_detail_stmt->close();
                        
                        $detail_seq++;
                    }
                }

                // COMMIT TRANSACTION
                $koneksi->commit();
                $_SESSION['success_message'] = 'Pesanan berhasil diperbarui!';
                
            } catch (Exception $e) {
                $koneksi->rollback();
                $_SESSION['error_message'] = 'Gagal memperbarui pesanan: ' . $e->getMessage();
            }
        }
        $check_stmt->close();
    }
    
    header('Location: index.php?page=order');
    exit;
}

// READ - Get orders (all for admin, only current user's for sales)
$user_role = $_SESSION['role'] ?? 'sales';
if ($user_role === 'admin') {
    // Admin: lihat semua orders
    $query = "SELECT p.noPesanan, p.tanggalOrder, p.status, d.namaDistributor, d.noDistributor, u.nama as user_name FROM pesanan p 
              JOIN distributor d ON p.noDistributor = d.noDistributor 
              JOIN user u ON p.idUser = u.idUser
              ORDER BY p.tanggalOrder DESC";
    $stmt = $koneksi->prepare($query);
    $stmt->execute();
} else {
    // Sales: hanya lihat order mereka sendiri
    $query = "SELECT p.noPesanan, p.tanggalOrder, p.status, d.namaDistributor, d.noDistributor, u.nama as user_name FROM pesanan p 
              JOIN distributor d ON p.noDistributor = d.noDistributor 
              JOIN user u ON p.idUser = u.idUser
              WHERE p.idUser = ? ORDER BY p.tanggalOrder DESC";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param('s', $current_user_id);
    $stmt->execute();
}

$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get distributors for form dropdown
if ($user_role === 'admin') {
    // Admin: lihat semua distributor
    $dist_query = "SELECT noDistributor, namaDistributor FROM distributor ORDER BY namaDistributor";
    $dist_stmt = $koneksi->prepare($dist_query);
    $dist_stmt->execute();
} else {
    // Sales: hanya distributor mereka
    $dist_query = "SELECT noDistributor, namaDistributor FROM distributor WHERE idUser = ? ORDER BY namaDistributor";
    $dist_stmt = $koneksi->prepare($dist_query);
    $dist_stmt->bind_param('s', $current_user_id);
    $dist_stmt->execute();
}
$dist_result = $dist_stmt->get_result();
$distributors = $dist_result->fetch_all(MYSQLI_ASSOC);
$dist_stmt->close();

// Get products for order form (to allow adding items during order creation)
$prod_query = "SELECT idProduk, namaProduk, harga FROM produk ORDER BY namaProduk";
$prod_stmt = $koneksi->prepare($prod_query);
$prod_stmt->execute();
$prod_result = $prod_stmt->get_result();
$products = $prod_result->fetch_all(MYSQLI_ASSOC);
$prod_stmt->close();
?>

<div class="page-header">
    <h2>Manajemen Order</h2>
    <p class="page-subtitle">Kelola pesanan dari distributor</p>
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
    <button class="btn btn-primary btn-lg" onclick="openAddOrderModal()">
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
            <?php if (empty($orders)): ?>
                <tr><td colspan="5" style="text-align:center;color:#999;">Belum ada order</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($order['noPesanan']); ?></strong></td>
                        <td><?php echo date('d M Y', strtotime($order['tanggalOrder'])); ?></td>
                        <td><?php echo htmlspecialchars($order['namaDistributor']); ?></td>
                        <td><span class="badge badge-<?php echo strtolower(str_replace(' ', '', $order['status'])); ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        <td class="action-cell">
                            <button class="btn-action btn-view" onclick="openOrderDetailModal('<?php echo htmlspecialchars($order['noPesanan']); ?>')">Detail</button>
                            <button class="btn-action btn-edit" onclick="openEditOrderModal('<?php echo htmlspecialchars($order['noPesanan']); ?>', '<?php echo htmlspecialchars($order['tanggalOrder']); ?>', '<?php echo htmlspecialchars($order['noDistributor']); ?>', '<?php echo htmlspecialchars($order['status']); ?>')">Edit</button>
                            <button class="btn-action btn-delete" onclick="if(confirm('Hapus order ini?')) window.location.href='index.php?page=order&hapus=<?php echo htmlspecialchars($order['noPesanan']); ?>'">Hapus</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
window.distributorsData = <?php echo json_encode($distributors); ?>;
window.productsData = <?php echo json_encode($products); ?>;

function openAddOrderModal() {
    openModal('Tambah Order', 'orderForm');
    document.getElementById('dynamicForm').reset();
    
    let idField = document.getElementById('noPesananHidden');
    if (idField) idField.remove();
    
    updateDistributorSelect();
    
    document.getElementById('dynamicForm').onsubmit = function(e) {
        e.preventDefault();
        submitOrderForm('tambah');
    };
}

function openEditOrderModal(noPesanan, tanggal, noDistributor, status) {
    openModal('Edit Order', 'orderForm');
    
    setTimeout(() => {
        updateDistributorSelect();
        document.getElementById('tanggalOrder').value = tanggal;
        document.getElementById('distributor').value = noDistributor;
        document.getElementById('status').value = status;
        // populate existing items for this order into productList
        if (typeof populateOrderItems === 'function') {
            populateOrderItems(noPesanan);
        }
        
        let form = document.getElementById('dynamicForm');
        let idField = document.getElementById('noPesananHidden');
        if (!idField) {
            idField = document.createElement('input');
            idField.type = 'hidden';
            idField.id = 'noPesananHidden';
            idField.name = 'noPesanan';
            form.appendChild(idField);
        }
        idField.value = noPesanan;
        
        form.onsubmit = function(e) {
            e.preventDefault();
            submitOrderForm('ubah');
        };
    }, 50);
}

function openOrderDetailModal(noPesanan) {
    openModal('Detail Order', 'orderDetail', noPesanan);
}

function updateDistributorSelect() {
    const select = document.getElementById('distributor');
    if (!select) return;
    
    select.innerHTML = '<option value="">Pilih Distributor</option>';
    window.distributorsData.forEach(d => {
        select.innerHTML += `<option value="${d.noDistributor}">${d.namaDistributor}</option>`;
    });
}

function submitOrderForm(action) {
    const form = document.getElementById('dynamicForm');
    
    // Collect form data manually to avoid duplicates
    const formData = new FormData();
    formData.append('action', action);
    formData.append('tanggalOrder', document.getElementById('tanggalOrder').value);
    formData.append('distributor', document.getElementById('distributor').value);
    formData.append('status', document.getElementById('status').value);
    
    // Add noPesananHidden if exists (untuk edit mode)
    const noPesananHidden = document.getElementById('noPesananHidden');
    if (noPesananHidden) {
        formData.append('noPesanan', noPesananHidden.value);
    }
    
    // Collect product rows - hanya yang punya produk (tidak kosong)
    const productRows = document.querySelectorAll('.product-row');
    if (productRows && productRows.length > 0) {
        productRows.forEach(row => {
            const prod = row.querySelector('.prod-select');
            const qty = row.querySelector('.prod-qty');
            // Hanya append jika produk dipilih DAN qty > 0
            if (prod && qty && prod.value && parseInt(qty.value) > 0) {
                formData.append('produk[]', prod.value);
                formData.append('jumlah[]', qty.value);
            }
        });
    }
    
    // Submit via hidden form
    const actualForm = document.createElement('form');
    actualForm.method = 'POST';
    actualForm.action = 'index.php?page=order';
    
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

<script>
// expose products data for order form
window.productsData = <?php echo json_encode($products ?? []); ?>;
</script>
