<?php
/**
 * Distributor Management - CRUD Operations
 * Single file handling all distributor operations
 * Accessible only to SALES role
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/koneksi.php';

// Check authentication and authorization
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'sales') {
    header('Location: login.php');
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Handle DELETE operation
if (isset($_GET['hapus'])) {
    $noDistributor = trim($_GET['hapus']);

    // Verify distributor belongs to current user
    $check_query = "SELECT noDistributor FROM distributor WHERE noDistributor = ? AND idUser = ?";
    $check_stmt = $koneksi->prepare($check_query);
    $check_stmt->bind_param('ss', $noDistributor, $current_user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $delete_query = "DELETE FROM distributor WHERE noDistributor = ?";
        $delete_stmt = $koneksi->prepare($delete_query);
        $delete_stmt->bind_param('s', $noDistributor);

        if ($delete_stmt->execute()) {
            $_SESSION['success_message'] = 'Distributor berhasil dihapus!';
        } else {
            $_SESSION['error_message'] = 'Gagal menghapus distributor: ' . $koneksi->error;
        }
        $delete_stmt->close();
    } else {
        $_SESSION['error_message'] = 'Distributor tidak ditemukan!';
    }

    $check_stmt->close();
    header('Location: index.php?page=distributor');
    exit;
}

// Handle CREATE/UPDATE operation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'tambah') {
        // CREATE new distributor
        $namaDistributor = trim($_POST['namaDistributor'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $kontak = trim($_POST['kontak'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($namaDistributor) || empty($alamat) || empty($kontak) || empty($email)) {
            $_SESSION['error_message'] = 'Semua field harus diisi!';
        } else {
            // Generate new distributor ID
            $id_query = "SELECT MAX(CAST(SUBSTRING(noDistributor, 4) AS UNSIGNED)) as max_id FROM distributor";
            $id_result = $koneksi->query($id_query);
            $id_row = $id_result->fetch_assoc();
            $next_id = ($id_row['max_id'] ?? 0) + 1;
            $noDistributor = 'DST' . str_pad($next_id, 3, '0', STR_PAD_LEFT);

            $insert_query = "INSERT INTO distributor (noDistributor, namaDistributor, alamat, kontak, email, idUser) VALUES (?, ?, ?, ?, ?, ?)";
            $insert_stmt = $koneksi->prepare($insert_query);
            $insert_stmt->bind_param('ssssss', $noDistributor, $namaDistributor, $alamat, $kontak, $email, $current_user_id);

            if ($insert_stmt->execute()) {
                $_SESSION['success_message'] = 'Distributor berhasil ditambahkan!';
            } else {
                $_SESSION['error_message'] = 'Gagal menambahkan distributor: ' . $koneksi->error;
            }
            $insert_stmt->close();
        }
    } elseif ($action === 'ubah') {
        // UPDATE existing distributor
        $noDistributor = trim($_POST['noDistributor'] ?? '');
        $namaDistributor = trim($_POST['namaDistributor'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $kontak = trim($_POST['kontak'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($noDistributor) || empty($namaDistributor) || empty($alamat) || empty($kontak) || empty($email)) {
            $_SESSION['error_message'] = 'Semua field harus diisi!';
        } else {
            // Verify ownership
            $check_query = "SELECT noDistributor FROM distributor WHERE noDistributor = ? AND idUser = ?";
            $check_stmt = $koneksi->prepare($check_query);
            $check_stmt->bind_param('ss', $noDistributor, $current_user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $update_query = "UPDATE distributor SET namaDistributor = ?, alamat = ?, kontak = ?, email = ? WHERE noDistributor = ?";
                $update_stmt = $koneksi->prepare($update_query);
                $update_stmt->bind_param('sssss', $namaDistributor, $alamat, $kontak, $email, $noDistributor);

                if ($update_stmt->execute()) {
                    $_SESSION['success_message'] = 'Distributor berhasil diperbarui!';
                } else {
                    $_SESSION['error_message'] = 'Gagal memperbarui distributor: ' . $koneksi->error;
                }
                $update_stmt->close();
            } else {
                $_SESSION['error_message'] = 'Distributor tidak ditemukan atau Anda tidak memiliki akses!';
            }
            $check_stmt->close();
        }
    }

    header('Location: index.php?page=distributor');
    exit;
}

// READ - Get all distributors for current user
$query = "SELECT noDistributor, namaDistributor, alamat, kontak, email FROM distributor WHERE idUser = ? ORDER BY noDistributor DESC";
$stmt = $koneksi->prepare($query);
$stmt->bind_param('s', $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
$distributors = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="page-header">
    <h2>Manajemen Distributor</h2>
    <p class="page-subtitle">Kelola data distributor dan kontak</p>
</div>

<?php
// Display success/error messages
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
    <button class="btn btn-primary btn-lg" onclick="openAddDistributorModal()">
        + Tambah Distributor
    </button>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>No Distributor</th>
                <th>Nama Distributor</th>
                <th>Alamat</th>
                <th>Kontak</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($distributors)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #999;">Belum ada distributor</td>
                </tr>
            <?php else: ?>
                <?php foreach ($distributors as $dist): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($dist['noDistributor']); ?></strong></td>
                        <td><?php echo htmlspecialchars($dist['namaDistributor']); ?></td>
                        <td><?php echo htmlspecialchars($dist['alamat']); ?></td>
                        <td><?php echo htmlspecialchars($dist['kontak']); ?></td>
                        <td><?php echo htmlspecialchars($dist['email']); ?></td>
                        <td class="action-cell">
                            <button class="btn-action btn-edit" onclick="openEditDistributorModal('<?php echo htmlspecialchars($dist['noDistributor']); ?>', '<?php echo htmlspecialchars(str_replace("'", "\\'", $dist['namaDistributor'])); ?>', '<?php echo htmlspecialchars(str_replace("'", "\\'", $dist['alamat'])); ?>', '<?php echo htmlspecialchars($dist['kontak']); ?>', '<?php echo htmlspecialchars($dist['email']); ?>')">Edit</button>
                            <button class="btn-action btn-delete" onclick="if(confirm('Hapus distributor ini?')) window.location.href='index.php?page=distributor&hapus=<?php echo htmlspecialchars($dist['noDistributor']); ?>'">Hapus</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function openAddDistributorModal() {
    openModal('Tambah Distributor', 'distributorForm');
    document.getElementById('dynamicForm').reset();
    
    let idField = document.getElementById('noDistributorHidden');
    if (idField) idField.remove();
    
    document.getElementById('dynamicForm').onsubmit = function(e) {
        e.preventDefault();
        submitDistributorForm('tambah');
    };
}

function openEditDistributorModal(noDistributor, nama, alamat, kontak, email) {
    openModal('Edit Distributor', 'distributorForm');
    
    document.getElementById('namaDistributor').value = nama;
    document.getElementById('alamat').value = alamat;
    document.getElementById('kontak').value = kontak;
    document.getElementById('email').value = email;
    
    let form = document.getElementById('dynamicForm');
    let idField = document.getElementById('noDistributorHidden');
    
    if (!idField) {
        idField = document.createElement('input');
        idField.type = 'hidden';
        idField.id = 'noDistributorHidden';
        idField.name = 'noDistributor';
        form.appendChild(idField);
    }
    idField.value = noDistributor;
    
    form.onsubmit = function(e) {
        e.preventDefault();
        submitDistributorForm('ubah');
    };
}

function submitDistributorForm(action) {
    const form = document.getElementById('dynamicForm');
    const formData = new FormData(form);
    formData.append('action', action);
    
    const actualForm = document.createElement('form');
    actualForm.method = 'POST';
    actualForm.action = 'index.php?page=distributor';
    
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
