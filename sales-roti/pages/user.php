<?php
/**
 * User Management - CRUD Operations (Admin Only)
 * Handle user creation, editing, deletion
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

// Handle DELETE
if (isset($_GET['hapus'])) {
    $idUser = trim($_GET['hapus']);
    
    // Prevent self-deletion
    if ($idUser === $_SESSION['user_id']) {
        $_SESSION['error_message'] = 'Anda tidak dapat menghapus akun sendiri!';
    } else {
        $delete_query = "DELETE FROM user WHERE idUser = ?";
        $delete_stmt = $koneksi->prepare($delete_query);
        $delete_stmt->bind_param('s', $idUser);
        if ($delete_stmt->execute()) {
            $_SESSION['success_message'] = 'User berhasil dihapus!';
        } else {
            $_SESSION['error_message'] = 'Gagal menghapus user!';
        }
        $delete_stmt->close();
    }
    
    header('Location: index.php?page=user');
    exit;
}

// Handle CREATE/UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $username = trim($_POST['username'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? 'sales');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($nama) || empty($email) || empty($role)) {
        $_SESSION['error_message'] = 'Semua field harus diisi!';
    } else {
        if ($action === 'tambah') {
            if (empty($password)) {
                $_SESSION['error_message'] = 'Password harus diisi untuk user baru!';
            } else {
                // Check username exist
                $check_query = "SELECT idUser FROM user WHERE username = ?";
                $check_stmt = $koneksi->prepare($check_query);
                $check_stmt->bind_param('s', $username);
                $check_stmt->execute();
                
                if ($check_stmt->get_result()->num_rows > 0) {
                    $_SESSION['error_message'] = 'Username sudah digunakan!';
                } else {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    
                    // Generate ID based on role: US for Sales, AD for Admin
                    $prefix = ($role === 'admin') ? 'AD' : 'US';
                    $id_max_query = "SELECT MAX(CAST(SUBSTRING(idUser, " . (strlen($prefix) + 1) . ") AS UNSIGNED)) as max_num FROM user WHERE idUser LIKE '{$prefix}%'";
                    $id_max_result = $koneksi->query($id_max_query);
                    $id_max_row = $id_max_result->fetch_assoc();
                    $next_num = ($id_max_row['max_num'] ?? 0) + 1;
                    $idUser = $prefix . str_pad($next_num, 3, '0', STR_PAD_LEFT);
                    
                    $insert_query = "INSERT INTO user (idUser, username, nama, email, password, role) VALUES (?, ?, ?, ?, ?, ?)";
                    $insert_stmt = $koneksi->prepare($insert_query);
                    $insert_stmt->bind_param('ssssss', $idUser, $username, $nama, $email, $hashed_password, $role);
                    
                    if ($insert_stmt->execute()) {
                        $_SESSION['success_message'] = 'User berhasil ditambahkan!';
                    } else {
                        $_SESSION['error_message'] = 'Gagal menambahkan user!';
                    }
                    $insert_stmt->close();
                }
                $check_stmt->close();
            }
        } elseif ($action === 'ubah') {
            $idUser = trim($_POST['idUser'] ?? '');
            
            // Check username uniqueness (excluding current user)
            $check_query = "SELECT idUser FROM user WHERE username = ? AND idUser != ?";
            $check_stmt = $koneksi->prepare($check_query);
            $check_stmt->bind_param('ss', $username, $idUser);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows > 0) {
                $_SESSION['error_message'] = 'Username sudah digunakan user lain!';
            } else {
                if (!empty($password)) {
                    // Update dengan password baru
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $update_query = "UPDATE user SET username = ?, nama = ?, email = ?, role = ?, password = ? WHERE idUser = ?";
                    $update_stmt = $koneksi->prepare($update_query);
                    $update_stmt->bind_param('ssssss', $username, $nama, $email, $role, $hashed_password, $idUser);
                } else {
                    // Update tanpa password
                    $update_query = "UPDATE user SET username = ?, nama = ?, email = ?, role = ? WHERE idUser = ?";
                    $update_stmt = $koneksi->prepare($update_query);
                    $update_stmt->bind_param('sssss', $username, $nama, $email, $role, $idUser);
                }
                
                if ($update_stmt->execute()) {
                    $_SESSION['success_message'] = 'User berhasil diperbarui!';
                } else {
                    $_SESSION['error_message'] = 'Gagal memperbarui user!';
                }
                $update_stmt->close();
            }
            $check_stmt->close();
        }
    }
    
    header('Location: index.php?page=user');
    exit;
}

// READ - Get all users
$query = "SELECT idUser, username, nama, email, role FROM user ORDER BY nama";
$stmt = $koneksi->prepare($query);
$stmt->execute();
$user_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Count users by role
$role_counts = ['admin' => 0, 'sales' => 0, 'driver' => 0];
foreach ($user_list as $u) {
    if (isset($role_counts[$u['role']])) {
        $role_counts[$u['role']]++;
    }
}
?>

<div class="page-header">
    <h2>Kelola User</h2>
    <p class="page-subtitle">Manajemen user sistem</p>
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
    <button class="btn btn-primary btn-lg" onclick="openAddUserModal()">
        + Tambah User
    </button>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th style="width: 15%;">ID User</th>
                <th style="width: 15%;">Username</th>
                <th style="width: 20%;">Nama</th>
                <th style="width: 20%;">Email</th>
                <th style="width: 12%;">Role</th>
                <th style="width: 18%;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($user_list)): ?>
                <tr><td colspan="6" style="text-align:center;color:#999;">Belum ada user</td></tr>
            <?php else: ?>
                <?php foreach ($user_list as $u): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($u['idUser']); ?></strong></td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['nama']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><span class="badge badge-<?php echo strtolower($u['role']); ?>"><?php echo ucfirst($u['role']); ?></span></td>
                        <td class="action-cell">
                            <button class="btn-action btn-edit" onclick="openEditUserModal('<?php echo htmlspecialchars($u['idUser']); ?>', '<?php echo htmlspecialchars($u['username']); ?>', '<?php echo htmlspecialchars($u['nama']); ?>', '<?php echo htmlspecialchars($u['email']); ?>', '<?php echo htmlspecialchars($u['role']); ?>')">Edit</button>
                            <?php if ($u['idUser'] !== $_SESSION['user_id']): ?>
                                <button class="btn-action btn-delete" onclick="if(confirm('Hapus user ini?')) window.location.href='index.php?page=user&hapus=<?php echo htmlspecialchars($u['idUser']); ?>'">Hapus</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- User Statistics -->
<div class="status-summary">
    <div class="summary-card">
        <div class="summary-icon">👨‍💼</div>
        <div class="summary-content">
            <p class="summary-label">Admin</p>
            <p class="summary-value"><?php echo $role_counts['admin']; ?> user</p>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon">👤</div>
        <div class="summary-content">
            <p class="summary-label">Sales</p>
            <p class="summary-value"><?php echo $role_counts['sales']; ?> user</p>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon">🚗</div>
        <div class="summary-content">
            <p class="summary-label">Driver</p>
            <p class="summary-value"><?php echo $role_counts['driver']; ?> user</p>
        </div>
    </div>
</div>

<script>
function openAddUserModal() {
    openModal('Tambah User Baru', 'userForm');
    
    setTimeout(() => {
        const form = document.getElementById('dynamicForm');
        if (form) form.reset();
        
        let idField = document.getElementById('idUserHidden');
        if (idField) idField.remove();
        
        document.getElementById('password').style.display = 'block';
        document.getElementById('passwordLabel').style.display = 'block';
        
        form.onsubmit = function(e) {
            e.preventDefault();
            submitUserForm('tambah');
        };
    }, 100);
}

function openEditUserModal(idUser, username, nama, email, role) {
    openModal('Edit User', 'userForm');
    
    setTimeout(() => {
        document.getElementById('username').value = username;
        document.getElementById('nama').value = nama;
        document.getElementById('email').value = email;
        document.getElementById('role').value = role;
        document.getElementById('password').value = '';
        document.getElementById('password').style.display = 'block';
        document.getElementById('passwordLabel').style.display = 'block';
        
        let form = document.getElementById('dynamicForm');
        let idField = document.getElementById('idUserHidden');
        if (!idField) {
            idField = document.createElement('input');
            idField.type = 'hidden';
            idField.id = 'idUserHidden';
            idField.name = 'idUser';
            form.appendChild(idField);
        }
        idField.value = idUser;
        
        form.onsubmit = function(e) {
            e.preventDefault();
            submitUserForm('ubah');
        };
    }, 50);
}

function submitUserForm(action) {
    const form = document.getElementById('dynamicForm');
    const formData = new FormData(form);
    formData.append('action', action);
    
    const actualForm = document.createElement('form');
    actualForm.method = 'POST';
    actualForm.action = 'index.php?page=user';
    
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
