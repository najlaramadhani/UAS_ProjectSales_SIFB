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

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'sales') {
    header('Location: login.php');
    exit;
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
    // Debug: log incoming POST for diagnosis
    $logPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'order_debug.log';
    $logEntry = '[' . date('Y-m-d H:i:s') . "] POST action={$action} user={$current_user_id} POST=" . json_encode($_POST, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    @file_put_contents($logPath, $logEntry, FILE_APPEND);
    // Also capture raw request body and headers for debugging (temporary)
    $rawInput = @file_get_contents('php://input');
    if ($rawInput !== false && strlen($rawInput) > 0) {
        @file_put_contents($logPath, '[' . date('Y-m-d H:i:s') . "] RAW=" . $rawInput . PHP_EOL, FILE_APPEND);
    }
    if (function_exists('getallheaders')) {
        $hdrs = getallheaders();
        @file_put_contents($logPath, '[' . date('Y-m-d H:i:s') . "] HEADERS=" . json_encode($hdrs, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
    }
    
    if ($action === 'tambah') {
        $tanggalOrder = trim($_POST['tanggalOrder'] ?? '');
        $noDistributor = trim($_POST['distributor'] ?? '');
        $status = trim($_POST['status'] ?? 'Pending');
        
        if (empty($tanggalOrder) || empty($noDistributor)) {
            $_SESSION['error_message'] = 'Tanggal dan distributor harus diisi!';
        } else {
            // Generate order number: SOYYYYMMxxx (use MAX sequence per month to avoid duplicates)
            $dateStr = date('Ym', strtotime($tanggalOrder)); // YYYYMM
            $likePattern = 'SO' . $dateStr . '%';
            // sequence starts after 8 characters: 'SO' + YYYYMM => seq starts at position 9
            $id_query = "SELECT MAX(CAST(SUBSTRING(noPesanan, 9) AS UNSIGNED)) as max_seq FROM pesanan WHERE noPesanan LIKE ?";
            $id_stmt = $koneksi->prepare($id_query);
            $id_stmt->bind_param('s', $likePattern);
            $id_stmt->execute();
            $id_result = $id_stmt->get_result();
            $id_row = $id_result->fetch_assoc();
            $next_num = (isset($id_row['max_seq']) && $id_row['max_seq'] !== null) ? intval($id_row['max_seq']) + 1 : 1;
            $next_seq = str_pad($next_num, 3, '0', STR_PAD_LEFT);
            $noPesanan = 'SO' . $dateStr . $next_seq; // e.g., SO202510001
            $id_stmt->close();
            
            $insert_query = "INSERT INTO pesanan (noPesanan, tanggalOrder, status, idUser, noDistributor) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $koneksi->prepare($insert_query);
            $insert_stmt->bind_param('sssss', $noPesanan, $tanggalOrder, $status, $current_user_id, $noDistributor);
            
            if ($insert_stmt->execute()) {
                // log created order id
                @file_put_contents($logPath, '[' . date('Y-m-d H:i:s') . "] INSERTED noPesanan={$noPesanan}\n", FILE_APPEND);
                // If order created, process any submitted products
                if (isset($_POST['produk']) && is_array($_POST['produk'])) {
                    $produk_arr = $_POST['produk'];
                    $jumlah_arr = $_POST['jumlah'] ?? [];

                    // get current count to start sequence
                    $seq_query = "SELECT COUNT(*) as cnt FROM detail_pesanan WHERE noPesanan = ?";
                    $seq_stmt = $koneksi->prepare($seq_query);
                    $seq_stmt->bind_param('s', $noPesanan);
                    $seq_stmt->execute();
                    $seq_row = $seq_stmt->get_result()->fetch_assoc();
                    $base_seq = intval($seq_row['cnt']);
                    $seq_stmt->close();

                    $insert_detail_query = "INSERT INTO detail_pesanan (idDetail, jumlah, hargaSatuan, totalHarga, noPesanan, idProduk) VALUES (?, ?, ?, ?, ?, ?)";
                    $insert_detail_stmt = $koneksi->prepare($insert_detail_query);

                    for ($i = 0; $i < count($produk_arr); $i++) {
                        $idProduk = trim($produk_arr[$i]);
                        $qty = intval($jumlah_arr[$i] ?? 0);
                        if (empty($idProduk) || $qty <= 0) continue;

                        // Normalize idProduk: if given value is not an actual id, try to match by name or 'Name|price' legacy value
                        $hargaSatuan = null;
                        // check if id exists
                        $check_q = "SELECT idProduk, harga FROM produk WHERE idProduk = ?";
                        $check_stmt = $koneksi->prepare($check_q);
                        $check_stmt->bind_param('s', $idProduk);
                        $check_stmt->execute();
                        $check_res = $check_stmt->get_result();
                        if ($check_res->num_rows === 0) {
                            // try parse name|price or match by name
                            $parsedName = $idProduk;
                            if (strpos($idProduk, '|') !== false) {
                                $parts = explode('|', $idProduk);
                                $parsedName = trim($parts[0]);
                            }
                            $search_q = "SELECT idProduk, harga FROM produk WHERE namaProduk = ? LIMIT 1";
                            $search_stmt = $koneksi->prepare($search_q);
                            $search_stmt->bind_param('s', $parsedName);
                            $search_stmt->execute();
                            $search_res = $search_stmt->get_result();
                            if ($search_res->num_rows > 0) {
                                $prow = $search_res->fetch_assoc();
                                $idProduk = $prow['idProduk'];
                                $hargaSatuan = $prow['harga'];
                            }
                            $search_stmt->close();
                        } else {
                            $prow = $check_res->fetch_assoc();
                            $hargaSatuan = $prow['harga'];
                        }
                        $check_stmt->close();

                        if ($hargaSatuan === null) continue; // skip if still not found

                        $totalHarga = $hargaSatuan * $qty;

                        $base_seq++;
                        $seq_str = str_pad($base_seq, 2, '0', STR_PAD_LEFT);
                        $idDetail = "DTL-$noPesanan-$seq_str";

                        $insert_detail_stmt->bind_param('siddss', $idDetail, $qty, $hargaSatuan, $totalHarga, $noPesanan, $idProduk);
                        $insert_detail_stmt->execute();
                    }
                    $insert_detail_stmt->close();
                }

                $_SESSION['success_message'] = 'Order berhasil ditambahkan!';
            } else {
                $_SESSION['error_message'] = 'Gagal menambahkan order: ' . $koneksi->error;
            }
            $insert_stmt->close();
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
            $update_query = "UPDATE pesanan SET tanggalOrder = ?, status = ?, noDistributor = ? WHERE noPesanan = ?";
            $update_stmt = $koneksi->prepare($update_query);
            $update_stmt->bind_param('ssss', $tanggalOrder, $status, $noDistributor, $noPesanan);
            
            if ($update_stmt->execute()) {
                // If products were submitted with edit, replace detail_pesanan rows
                if (isset($_POST['produk']) && is_array($_POST['produk'])) {
                    // remove existing items
                    $del_stmt = $koneksi->prepare("DELETE FROM detail_pesanan WHERE noPesanan = ?");
                    $del_stmt->bind_param('s', $noPesanan);
                    $del_stmt->execute();
                    $del_stmt->close();

                    $produk_arr = $_POST['produk'];
                    $jumlah_arr = $_POST['jumlah'] ?? [];

                    // insert new items
                    $seq_query = "SELECT COUNT(*) as cnt FROM detail_pesanan WHERE noPesanan = ?";
                    $seq_stmt = $koneksi->prepare($seq_query);
                    $seq_stmt->bind_param('s', $noPesanan);
                    $seq_stmt->execute();
                    $seq_row = $seq_stmt->get_result()->fetch_assoc();
                    $base_seq = intval($seq_row['cnt']);
                    $seq_stmt->close();

                    $insert_detail_query = "INSERT INTO detail_pesanan (idDetail, jumlah, hargaSatuan, totalHarga, noPesanan, idProduk) VALUES (?, ?, ?, ?, ?, ?)";
                    $insert_detail_stmt = $koneksi->prepare($insert_detail_query);

                    for ($i = 0; $i < count($produk_arr); $i++) {
                        $idProduk = trim($produk_arr[$i]);
                        $qty = intval($jumlah_arr[$i] ?? 0);
                        if (empty($idProduk) || $qty <= 0) continue;

                        // Normalize idProduk similar to create flow
                        $hargaSatuan = null;
                        $check_q = "SELECT idProduk, harga FROM produk WHERE idProduk = ?";
                        $check_stmt = $koneksi->prepare($check_q);
                        $check_stmt->bind_param('s', $idProduk);
                        $check_stmt->execute();
                        $check_res = $check_stmt->get_result();
                        if ($check_res->num_rows === 0) {
                            $parsedName = $idProduk;
                            if (strpos($idProduk, '|') !== false) {
                                $parts = explode('|', $idProduk);
                                $parsedName = trim($parts[0]);
                            }
                            $search_q = "SELECT idProduk, harga FROM produk WHERE namaProduk = ? LIMIT 1";
                            $search_stmt = $koneksi->prepare($search_q);
                            $search_stmt->bind_param('s', $parsedName);
                            $search_stmt->execute();
                            $search_res = $search_stmt->get_result();
                            if ($search_res->num_rows > 0) {
                                $prow = $search_res->fetch_assoc();
                                $idProduk = $prow['idProduk'];
                                $hargaSatuan = $prow['harga'];
                            }
                            $search_stmt->close();
                        } else {
                            $prow = $check_res->fetch_assoc();
                            $hargaSatuan = $prow['harga'];
                        }
                        $check_stmt->close();

                        if ($hargaSatuan === null) continue;

                        $totalHarga = $hargaSatuan * $qty;

                        $base_seq++;
                        $seq_str = str_pad($base_seq, 2, '0', STR_PAD_LEFT);
                        $idDetail = "DTL-$noPesanan-$seq_str";

                        $insert_detail_stmt->bind_param('siddss', $idDetail, $qty, $hargaSatuan, $totalHarga, $noPesanan, $idProduk);
                        $insert_detail_stmt->execute();
                    }
                    $insert_detail_stmt->close();
                }

                $_SESSION['success_message'] = 'Order berhasil diperbarui!';
            } else {
                $_SESSION['error_message'] = 'Gagal memperbarui order!';
            }
            $update_stmt->close();
        }
        $check_stmt->close();
    }
    
    header('Location: index.php?page=order');
    exit;
}

// READ - Get orders for current user
$query = "SELECT p.noPesanan, p.tanggalOrder, p.status, d.namaDistributor, d.noDistributor FROM pesanan p 
          JOIN distributor d ON p.noDistributor = d.noDistributor 
          WHERE p.idUser = ? ORDER BY p.tanggalOrder DESC";
$stmt = $koneksi->prepare($query);
$stmt->bind_param('s', $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get distributors for form dropdown
$dist_query = "SELECT noDistributor, namaDistributor FROM distributor WHERE idUser = ? ORDER BY namaDistributor";
$dist_stmt = $koneksi->prepare($dist_query);
$dist_stmt->bind_param('s', $current_user_id);
$dist_stmt->execute();
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
    const formData = new FormData(form);
    formData.append('action', action);
    // collect product rows if present
    const productRows = document.querySelectorAll('.product-row');
    if (productRows && productRows.length > 0) {
        productRows.forEach(row => {
            const prod = row.querySelector('.prod-select');
            const qty = row.querySelector('.prod-qty');
            if (prod && qty && prod.value) {
                formData.append('produk[]', prod.value);
                formData.append('jumlah[]', qty.value);
            }
        });
    }
    
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
