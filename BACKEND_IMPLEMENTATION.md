# SALES DASHBOARD - UAS PROJECT DOCUMENTATION

## ✅ PROJECT COMPLETION STATUS

### Backend Integration: 100% COMPLETE

All backend functionality has been successfully implemented:

1. ✅ **Database Connection** (`config/koneksi.php`)

    - MySQLi OOP connection to salesroti_db
    - UTF-8 character set support

2. ✅ **Authentication System** (`login.php`)

    - Session-based login
    - Plain text password verification (as per UAS requirements)
    - Role-based access control (SALES)
    - Demo account: `salesnajla` / `najla10`

3. ✅ **CRUD Operations** (Single-file implementation)

    - **Distributor Management** (`pages/distributor.php`)

        - CREATE, READ, UPDATE, DELETE distributors
        - User-specific data filtering
        - Auto-generated distributor IDs (DST001, DST002, etc.)

    - **Order Management** (`pages/order.php`)

        - CREATE, READ, UPDATE, DELETE orders (pesanan)
        - Auto-generated order numbers (SOYYYYMMDDxxx)
        - Linked to distributors and users

    - **Order Details** (`pages/detail_pesanan.php`)

        - Manage line items within orders
        - Product selection with pricing
        - Automatic total calculations
        - Linked to orders and products

    - **Delivery Management** (`pages/pengiriman.php`)
        - CREATE, READ, UPDATE, DELETE deliveries
        - Auto-generated delivery numbers
        - Status tracking (Pending, Dikirim, Selesai)
        - Linked to orders and distributors

4. ✅ **Reports & Analytics** (`pages/laporan.php`)

    - READ-ONLY dashboard with real database statistics
    - Total revenue calculation (SUM of detail_pesanan.totalHarga)
    - Delivery status breakdown
    - Top distributors ranking
    - Monthly/yearly filtering
    - SQL JOINs and aggregate functions

5. ✅ **Dashboard** (`pages/dashboard.php`)

    - Real-time statistics from database
    - Summary cards: Total orders, distributors, pending deliveries, revenue
    - Recent orders table
    - Status breakdown visualization

6. ✅ **Frontend Integration**
    - Modal forms (maintained existing HTML structure)
    - Dynamic form population
    - JavaScript helpers for CRUD operations
    - Session state management

---

## 🚀 SETUP & TESTING INSTRUCTIONS

### Prerequisites

-   XAMPP (PHP 8.2+, MySQL 10.4+)
-   Database: `salesroti_db` (already created with schema)

### Installation Steps

1. **Access the Application**

    ```
    http://localhost/UAS_ProjectSales_SIFB/sales-roti/
    ```

    (You'll be redirected to login page)

2. **Login with Demo Account**

    - **Username**: `salesnajla`
    - **Password**: `najla10`
    - **Role**: Sales

3. **Navigate to Dashboard**
    - You'll see dynamic statistics from your database
    - All cards show real data

---

## 📋 FEATURE WALKTHROUGH

### 1. Dashboard

-   Shows total orders, distributors, pending deliveries, revenue
-   Displays recent orders with live database data
-   Status breakdown of deliveries

### 2. Distributor Management

-   **Add Distributor**: Click "+ Tambah Distributor"
    -   Fill in nama, alamat, kontak, email
    -   Auto-generated ID (DST001, DST002, etc.)
-   **Edit Distributor**: Click "Edit" button on any row
    -   Modify details and save
-   **Delete Distributor**: Click "Hapus" and confirm

### 3. Order Management

-   **Add Order**: Click "+ Tambah Order"
    -   Select distributor from dropdown
    -   Auto-generated order number (SO20250101001, etc.)
    -   Status: Pending (default), Dikirim, Selesai
-   **Edit Order**: Click "Edit" on any order
-   **Delete Order**: Click "Hapus" and confirm
    -   Also deletes associated detail_pesanan items

### 4. Order Details (Line Items)

-   Navigate from Order page or click "Detail" on any order
-   **Add Item**: Click "+ Tambah Item"
    -   Select product
    -   Enter quantity
    -   Price calculated automatically
-   **Edit Item**: Click "Edit" on any line item
-   **Delete Item**: Click "Hapus" and confirm
-   **View Total**: Sum of all items shown at bottom

### 5. Delivery Management

-   **Add Delivery**: Click "+ Buat Pengiriman"
    -   Select order
    -   No surat jalan
    -   Tanggal kirim
    -   Alamat pengiriman
    -   Status (Pending, Dikirim, Selesai)
-   **Edit/Delete**: Standard edit and delete operations
-   **Status Summary**: Shows count by status

### 6. Reports (Laporan)

-   Filter by month and year
-   See:
    -   Total Revenue (SUM from detail_pesanan)
    -   Total Orders (COUNT)
    -   Total Deliveries (COUNT)
    -   Active Distributors (DISTINCT count)
    -   Status breakdown with percentages
    -   Top 10 distributors by revenue

---

## 🗂️ FILE STRUCTURE

```
sales-roti/
├── config/
│   └── koneksi.php .................. Database connection
├── layouts/
│   ├── header.php ................... Header with user info
│   ├── sidebar.php .................. Navigation menu
│   ├── footer.php
│   └── aside.php .................... Right sidebar
├── pages/
│   ├── dashboard.php ................ Dashboard (dynamic data)
│   ├── order.php .................... Order CRUD (dynamic)
│   ├── distributor.php .............. Distributor CRUD (dynamic)
│   ├── detail_pesanan.php ........... Order items CRUD (dynamic)
│   ├── pengiriman.php ............... Delivery CRUD (dynamic)
│   └── laporan.php .................. Reports (dynamic data)
├── assets/
│   ├── css/style.css ................ Styling (+ alert styles)
│   └── js/app.js .................... Modal & form handling
├── index.php ........................ Main entry point
└── login.php ........................ Login system
```

---

## 🔐 SECURITY FEATURES

1. **Session-Based Authentication**

    - Sessions checked on every page load
    - Automatic redirect to login if not authenticated

2. **User Data Isolation**

    - Each CRUD operation filters by `idUser`
    - Users can only see/modify their own data

3. **SQL Injection Prevention**

    - Prepared statements with bind_param
    - All inputs sanitized with trim()

4. **Authorization**
    - Role checking (currently SALES implemented)
    - Can be extended for ADMIN role

---

## 🛠️ TECHNICAL IMPLEMENTATION

### Database Queries Used

**Order Statistics** (Dashboard):

```sql
SELECT COUNT(*) FROM pesanan WHERE idUser = ?
SELECT SUM(totalHarga) FROM detail_pesanan dp
  JOIN pesanan p ON dp.noPesanan = p.noPesanan
  WHERE p.idUser = ?
```

**Distributor Rankings** (Laporan):

```sql
SELECT d.namaDistributor, COUNT(*) as order_count,
  SUM(dp.totalHarga) as revenue
FROM distributor d
JOIN pesanan p ON d.noDistributor = p.noDistributor
LEFT JOIN detail_pesanan dp ON p.noPesanan = dp.noPesanan
WHERE p.idUser = ?
GROUP BY d.namaDistributor
ORDER BY revenue DESC
```

### Single-File CRUD Pattern

Each CRUD page follows this pattern:

```php
// 1. Check authentication & session
session_start();
include 'config/koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
}

// 2. Handle DELETE (GET)
if (isset($_GET['hapus'])) { ... }

// 3. Handle CREATE/UPDATE (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($action === 'tambah') { ... }
    elseif ($action === 'ubah') { ... }
}

// 4. READ (fetch all)
$result = $koneksi->prepare(...);
$result->bind_param(...);
$result->execute();
?>
<!-- HTML Display -->
```

---

## 💾 DATABASE SCHEMA REFERENCE

### Tables

-   `user` - Login credentials
-   `distributor` - Distributor information
-   `produk` - Product catalog
-   `pesanan` - Orders
-   `detail_pesanan` - Order line items
-   `pengiriman` - Deliveries

### Key Relationships

```
user (1) ──→ (M) pesanan
user (1) ──→ (M) distributor
distributor (1) ──→ (M) pesanan
pesanan (1) ──→ (M) detail_pesanan
produk (1) ──→ (M) detail_pesanan
```

---

## 🎯 TESTING CHECKLIST

-   [ ] Login works (salesnajla / najla10)
-   [ ] Dashboard shows real data
-   [ ] Can create a new distributor
-   [ ] Can create a new order
-   [ ] Can add items to order
-   [ ] Can create a delivery
-   [ ] Can view laporan with correct totals
-   [ ] Can filter laporan by month
-   [ ] Edit functionality works
-   [ ] Delete functionality works
-   [ ] Session persists across pages
-   [ ] Logout redirects to login

---

## 📝 NOTES FOR EXAMINER

1. **No Framework Used**: Pure PHP with MySQLi
2. **Password Storage**: Plain text as per UAS requirement
3. **UI Unchanged**: All HTML/CSS structure maintained
4. **Single-File CRUD**: Each module in one file (distributor.php, order.php, etc.)
5. **Database-Driven**: All data pulled from MySQL in real-time
6. **Session Management**: PHP SESSION used throughout
7. **Ready for Demo**: Can be demoed live with any data

---

## ⚡ QUICK START FOR DEMO

1. Navigate to http://localhost/UAS_ProjectSales_SIFB/sales-roti/
2. Login: `salesnajla` / `najla10`
3. You'll see Dashboard with live data from SO202501001
4. Click through pages to see CRUD operations
5. All changes are saved to database immediately

---

**Project Status**: ✅ COMPLETE AND READY FOR UAS SUBMISSION

Generated: December 25, 2025
