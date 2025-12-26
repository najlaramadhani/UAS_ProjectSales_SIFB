<?php
/**
 * Login System - Sales Dashboard
 * Session-based authentication with role support
 */

session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

include 'config/koneksi.php';

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password harus diisi!';
    } else {
        // Query user
        $query = "SELECT idUser, username, nama, email, password, role FROM user WHERE username = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Password verification: support both bcrypt and plain text
            $password_match = false;
            
            // Check if password is bcrypt hash
            if (substr($user['password'], 0, 4) === '$2y$' || substr($user['password'], 0, 4) === '$2a$' || substr($user['password'], 0, 4) === '$2b$') {
                $password_match = password_verify($password, $user['password']);
            } else {
                // Plain text password comparison
                $password_match = ($password === $user['password']);
            }
            
            if ($password_match) {
                // Login successful - set session
                $_SESSION['user_id'] = $user['idUser'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_name'] = $user['nama'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                header('Location: index.php?page=dashboard');
                exit;
            } else {
                $error_message = 'Username atau password salah!';
            }
        } else {
            $error_message = 'Username atau password salah!';
        }

        $stmt->close();
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sales Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Login Page Specific Styles */
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #4a90e2 0%, #2e5c99 100%);
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            font-size: 28px;
            color: #2c3e50;
            margin: 10px 0;
        }

        .login-header .login-icon {
            font-size: 40px;
            display: block;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #7f8c8d;
            font-size: 14px;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-group input {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .form-error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 6px;
            font-size: 14px;
            text-align: center;
            border-left: 4px solid #e74c3c;
        }

        .form-success {
            background: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 6px;
            font-size: 14px;
            text-align: center;
            border-left: 4px solid #27ae60;
        }

        .btn-login {
            background: #4a90e2;
            color: white;
            padding: 11px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #2e5c99;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #7f8c8d;
        }

        .demo-info {
            background: #f0f8ff;
            padding: 12px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 12px;
            border-left: 4px solid #3498db;
        }

        .demo-info strong {
            display: block;
            color: #2c3e50;
            margin-bottom: 6px;
        }

        .demo-info p {
            color: #34495e;
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <span class="login-icon">🍞</span>
                <h1>Sales Dashboard</h1>
                <p>Sistem Penjualan Roti</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="form-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>

                <button type="submit" name="login" class="btn-login">Login</button>
            </form>

            <div class="demo-info">
                <strong>Demo Account:</strong>
                <p>Username: <code>salesnajla</code></p>
                <p>Password: <code>najla10</code></p>
                <p>Role: Sales</p>
            </div>

            <div class="login-footer">
                <p>&copy; 2025 Sales Dashboard | UAS Project</p>
            </div>
        </div>
    </div>
</body>
</html>
