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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            position: relative;
            overflow: hidden;
        }

        .login-page::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -250px;
            left: -250px;
        }

        .login-page::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -200px;
            right: -200px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-icon {
            font-size: 60px;
            display: block;
            margin-bottom: 16px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .login-header h1 {
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .login-header p {
            color: #7f8c8d;
            font-size: 15px;
            letter-spacing: 0.5px;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .form-group input {
            padding: 12px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-error {
            background: #fee;
            color: #c33;
            padding: 14px;
            border-radius: 10px;
            font-size: 14px;
            text-align: center;
            border-left: 4px solid #e74c3c;
            margin-bottom: 16px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 13px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
            letter-spacing: 0.5px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 28px;
            font-size: 12px;
            color: #95a5a6;
            letter-spacing: 0.5px;
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

            <div class="login-footer">
                <p>&copy; 2025 Sales Dashboard | UAS Project</p>
            </div>
        </div>
    </div>
</body>
</html>
