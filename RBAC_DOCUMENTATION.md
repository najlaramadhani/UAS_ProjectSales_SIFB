# Role-Based Access Control (RBAC) Implementation - Dokumentasi

## 📋 Overview

Sistem RBAC telah diimplementasikan pada aplikasi Sales Dashboard dengan dua role utama:

-   **ADMIN** - Akses penuh ke semua menu dan fitur
-   **SALES** - Akses terbatas ke menu sales saja

## 🔐 Authentication & Role Check

### 1. Login System (login.php)

Saat user login, sistem akan:

-   Query database untuk user dengan username
-   Verify password (support bcrypt & plain text)
-   Simpan role ke session: `$_SESSION['role']`

```php
// Contoh session yang tersimpan setelah login:
$_SESSION['user_id'] = 'US...';
$_SESSION['username'] = 'admin123';
$_SESSION['user_name'] = 'Administrator';
$_SESSION['role'] = 'admin'; // atau 'sales' / 'driver'
```

## 🎨 Navigation Menu (layouts/sidebar.php)

Menu berdasarkan role:

### ADMIN Menu:

-   Dashboard
-   Order
-   Distributor
-   Pengiriman
-   Laporan
-   **Produk** ✨ (Admin only)
-   **Kelola User** ✨ (Admin only)

### SALES Menu:

-   Dashboard
-   Order
-   Distributor
-   Pengiriman
-   Laporan

Implementasi:

```php
<?php
$user_role = $_SESSION['role'] ?? 'sales';

// Semua menu ditampilkan untuk semua role
// Menu admin-only hanya jika role = 'admin':
<?php if ($user_role === 'admin'): ?>
    <li><a href="index.php?page=produk">Produk</a></li>
    <li><a href="index.php?page=user">Kelola User</a></li>
<?php endif; ?>
?>
```

## 🛡️ Backend Access Control (index.php)

**PENTING**: Selain hide menu di frontend, juga ada pengecekan di backend!

```php
// Get user role
$user_role = $_SESSION['role'] ?? 'sales';

// Define allowed pages per role
$allowed_pages_all = ['dashboard', 'order', 'distributor', 'pengiriman', 'laporan'];
$allowed_pages_admin = ['dashboard', 'order', 'distributor', 'pengiriman', 'laporan', 'produk', 'user'];

// Set allowed pages based on role
$allowed_pages = ($user_role === 'admin') ? $allowed_pages_admin : $allowed_pages_all;

// Block unauthorized pages
if (!in_array($page, $allowed_pages)) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Extra protection: block admin-only pages for non-admin
if (in_array($page, ['produk', 'user']) && $user_role !== 'admin') {
    header('Location: index.php?page=dashboard');
    exit;
}
```

## 📄 Admin-Only Pages

### pages/user.php - User Management (CRUD)

-   **Akses**: Admin only
-   **Fitur**:
    -   Tambah user baru (set username, nama, email, role, password)
    -   Edit user (ubah data tanpa mengubah password jika kosong)
    -   Hapus user (prevent self-delete)
    -   View semua users dengan role badge
    -   Statistik user per role

Security Guard:

```php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?page=dashboard');
    exit;
}
```

**Form Submission Flow**:

1. openAddUserModal() / openEditUserModal()
2. loadFormContent() render form template (username, nama, email, role, password)
3. submitUserForm('tambah' | 'ubah')
4. POST ke index.php?page=user dengan action
5. Backend validate & execute INSERT/UPDATE

### pages/produk.php - Product Management (CRUD)

-   **Akses**: Admin only
-   **Fitur**:
    -   Tambah produk baru (nama, harga, stok, deskripsi)
    -   Edit produk
    -   Hapus produk
    -   View semua produk (from all users)
    -   Inventory statistics (total stok, nilai inventory, low stock count)

Security Guard: Sama seperti user.php

## 🔒 Security Best Practices Implemented

### ✅ Backend Validation

-   Session check di setiap admin page
-   Role verification sebelum allow akses
-   Cannot self-delete user
-   Password hashing (bcrypt)
-   Prepared statements untuk prevent SQL injection

### ✅ Frontend Protection

-   Hide menu berdasarkan role
-   Form template hanya load jika role allowed
-   Redirect unauthorized page access

### ✅ Data Isolation

-   Sales data filtered by user_id (mereka hanya lihat data sendiri)
-   Admin dapat lihat semua data

## 📊 User Roles Explained

### ADMIN Role

```
Akses: ✅ PENUH
Dapat:
  ✅ CRUD User
  ✅ CRUD Produk
  ✅ CRUD Distributor
  ✅ CRUD Order
  ✅ CRUD Pengiriman
  ✅ View Laporan
  ✅ Dashboard admin (semua data)
```

### SALES Role

```
Akses: ❌ TERBATAS
Dapat:
  ❌ TIDAK bisa CRUD User
  ❌ TIDAK bisa CRUD Produk
  ✅ CRUD Distributor (mereka)
  ✅ CRUD Order (mereka)
  ✅ CRUD Pengiriman (mereka)
  ✅ View Laporan (mereka)
  ✅ Dashboard (data mereka)

Pembatasan:
  - Hanya lihat data dengan idUser = $_SESSION['user_id']
```

### DRIVER Role

```
Akses: ❌ TERBATAS
Dapat:
  ❌ TIDAK bisa akses dashboard
  ✅ Dipilih saat membuat pengiriman
  ✅ Dapat melihat pengiriman mereka
```

## 🧪 Testing Scenarios

### Test 1: Login Sebagai Admin

1. Login dengan username admin, password admin123
2. Sidebar akan menampilkan menu: Dashboard, Order, Distributor, Pengiriman, Laporan, **Produk**, **Kelola User**
3. Klik Produk → bisa CRUD produk
4. Klik Kelola User → bisa CRUD user
5. Coba akses `index.php?page=produk` direct via URL → berhasil (role admin)

### Test 2: Login Sebagai Sales

1. Login dengan username sales, password sales123
2. Sidebar hanya menampilkan: Dashboard, Order, Distributor, Pengiriman, Laporan
3. Menu Produk & Kelola User TIDAK tampil
4. Coba akses `index.php?page=produk` direct via URL → redirect ke dashboard
5. Coba akses `index.php?page=user` direct via URL → redirect ke dashboard

### Test 3: User Self-Delete Protection

1. Login sebagai admin
2. Buka Kelola User
3. Tombol "Hapus" untuk user sendiri tidak ada (disabled)
4. Admin lain dapat dihapus normal

## 📝 Database Schema

### Tabel user

```sql
CREATE TABLE user (
    idUser VARCHAR(50) PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'sales', 'driver') DEFAULT 'sales'
);
```

### Tabel produk

```sql
CREATE TABLE produk (
    idProduk VARCHAR(50) PRIMARY KEY,
    namaProduk VARCHAR(100) NOT NULL,
    harga DECIMAL(10, 2) NOT NULL,
    stok INT NOT NULL,
    deskripsi TEXT,
    idUser VARCHAR(50),
    FOREIGN KEY (idUser) REFERENCES user(idUser)
);
```

## 🎯 File yang Dimodifikasi/Dibuat

### Baru Dibuat:

-   ✨ `pages/user.php` - User management CRUD
-   ✨ `pages/produk.php` - Product management CRUD

### Dimodifikasi:

-   📝 `index.php` - Add role-based routing & access control
-   📝 `layouts/sidebar.php` - Add conditional menu based on role
-   📝 `assets/js/app.js` - Add userForm & produkForm templates
-   📝 `assets/css/style.css` - Add role badge colors & nav styling

### Existing (No Changes):

-   ✅ `login.php` - Already captures role
-   ✅ `pages/order.php` - Already role-protected (sales only)
-   ✅ `pages/pengiriman.php` - Already role-protected (sales only)
-   ✅ `pages/laporan.php` - Already role-protected (sales only)
-   ✅ `pages/distributor.php` - Already role-protected (sales only)

## 🔧 Konfigurasi

### Menambah Role Baru

Jika ingin tambah role baru (misal: 'supervisor'):

1. Update enum di database:

```sql
ALTER TABLE user MODIFY COLUMN role ENUM('admin', 'sales', 'driver', 'supervisor');
```

2. Update sidebar.php:

```php
<?php if ($user_role === 'supervisor'): ?>
    <!-- supervisor menu -->
<?php endif; ?>
```

3. Update index.php allowed_pages:

```php
$allowed_pages_supervisor = [...];
```

## 📞 Contoh Implementasi Role Check di Custom Page

Jika ingin buat page baru yang hanya admin akses:

```php
<?php
// pages/custom_admin_page.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?page=dashboard');
    exit;
}

// Page content...
?>
```

## ✅ Checklist

-   ✅ Login captures role
-   ✅ Session stores role
-   ✅ Frontend menu based on role
-   ✅ Backend access control (no redirect bypass)
-   ✅ Self-delete protection
-   ✅ User CRUD (admin only)
-   ✅ Product CRUD (admin only)
-   ✅ Sales data isolation (per user)
-   ✅ Admin full access
-   ✅ Role badge styling
-   ✅ Form templates untuk user & produk

---

**Last Updated**: 2025-12-27
**Version**: 1.0 - RBAC Implementation Complete
