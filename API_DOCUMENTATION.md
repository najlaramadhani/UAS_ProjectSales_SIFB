# SALES DASHBOARD - BACKEND API DOCUMENTATION

## 📡 API ENDPOINTS & PARAMETERS

All endpoints are server-side PHP handling. No REST API - traditional form-based CRUD.

---

## 🔐 LOGIN ENDPOINT

**File**: `login.php`

### POST /login.php

```
Parameters:
  - username (string) - User's login username
  - password (string) - User's password (supports bcrypt & plain text)
  - login (submit button) - Form submission

Response:
  - Success: Redirect to index.php?page=dashboard + set SESSION
  - Error: Display error_message on login page
```

**Session Variables Set**:

```php
$_SESSION['user_id']    // idUser from database
$_SESSION['username']   // username
$_SESSION['user_name']  // nama (full name)
$_SESSION['email']      // email
$_SESSION['role']       // role (sales, admin, etc.)
```

---

## 📦 DISTRIBUTOR CRUD

**File**: `pages/distributor.php`

### CREATE - Add New Distributor

```
POST to: index.php?page=distributor
Method: POST
Parameters:
  - action = "tambah"
  - namaDistributor (string, required)
  - alamat (text, required)
  - kontak (string, required)
  - email (string, required)

Response:
  - Success: Redirect + $_SESSION['success_message']
  - Error: $_SESSION['error_message']
```

### READ - Get Distributors

```
Automatic on page load:
SELECT * FROM distributor WHERE idUser = ?

Returns: Array of distributors with fields:
  - noDistributor (auto-generated: DST001, DST002, etc.)
  - namaDistributor
  - alamat
  - kontak
  - email
```

### UPDATE - Edit Distributor

```
POST to: index.php?page=distributor
Method: POST
Parameters:
  - action = "ubah"
  - noDistributor (hidden field)
  - namaDistributor (string)
  - alamat (text)
  - kontak (string)
  - email (string)

Response:
  - Success: Redirect + success_message
  - Error: error_message
```

### DELETE - Remove Distributor

```
GET to: index.php?page=distributor&hapus={noDistributor}

Parameters:
  - hapus = noDistributor (via URL)

Response:
  - Success: Redirect + success_message
  - Error: error_message
```

---

## 🛒 ORDER (PESANAN) CRUD

**File**: `pages/order.php`

### CREATE - Add New Order

```
POST to: index.php?page=order
Method: POST
Parameters:
  - action = "tambah"
  - tanggalOrder (date, required) - Format: YYYY-MM-DD
  - distributor (string, required) - noDistributor
  - status (string, default: Pending)
    - Values: Pending, Dikirim, Selesai

Auto-generated: noPesanan (SOYYYYMMDDxxx format)
```

### READ - Get Orders

```
Automatic on page load:
SELECT p.*, d.namaDistributor FROM pesanan p
JOIN distributor d ON p.noDistributor = d.noDistributor
WHERE p.idUser = ?

Returns:
  - noPesanan
  - tanggalOrder
  - status
  - namaDistributor
  - noDistributor
```

### UPDATE - Edit Order

```
POST to: index.php?page=order
Parameters:
  - action = "ubah"
  - noPesanan (hidden)
  - tanggalOrder (date)
  - distributor (string)
  - status (string)
```

### DELETE - Remove Order

```
GET to: index.php?page=order&hapus={noPesanan}

Note: Also deletes all detail_pesanan items for this order
```

---

## 📋 ORDER DETAIL ITEMS (DETAIL_PESANAN) CRUD

**File**: `pages/detail_pesanan.php`

### CREATE - Add Item to Order

```
POST to: index.php?page=detail_pesanan
Parameters:
  - action = "tambah"
  - noPesanan (hidden)
  - produk (string) - idProduk
  - jumlah (integer, required) - quantity

Auto-generated: idDetail (DTL-{noPesanan}-{seq})
Auto-calculated: hargaSatuan (from produk table)
Auto-calculated: totalHarga (hargaSatuan × jumlah)
```

### READ - Get Order Items

```
GET Parameter: pesanan={noPesanan}

If no pesanan specified: Show list of all orders
If pesanan specified:
SELECT dp.*, pr.namaProduk FROM detail_pesanan dp
JOIN produk pr ON dp.idProduk = pr.idProduk
WHERE dp.noPesanan = ?

Returns:
  - idDetail
  - idProduk
  - namaProduk
  - jumlah
  - hargaSatuan
  - totalHarga
```

### UPDATE - Edit Item

```
POST to: index.php?page=detail_pesanan
Parameters:
  - action = "ubah"
  - noPesanan (hidden)
  - idDetail (hidden)
  - produk (string)
  - jumlah (integer)

Auto-recalculated: hargaSatuan, totalHarga
```

### DELETE - Remove Item

```
GET to: index.php?page=detail_pesanan?hapus={idDetail}&pesanan={noPesanan}
```

---

## 🚚 DELIVERY (PENGIRIMAN) CRUD

**File**: `pages/pengiriman.php`

### CREATE - Add New Delivery

```
POST to: index.php?page=pengiriman
Parameters:
  - action = "tambah"
  - noPesanan (string, required) - Order reference
  - noSuratJalan (string, required) - Waybill number
  - tanggalKirim (date, required)
  - alamatPengiriman (text, required)
  - statusPengiriman (string, default: Pending)
    - Values: Pending, Dikirim, Selesai

Auto-generated: noPengiriman (PGYYYYMMDDxxx)
Auto-fetched: noDistributor (from pesanan)
```

### READ - Get Deliveries

```
Automatic on page load:
SELECT p.*, d.namaDistributor
FROM pengiriman p
JOIN distributor d ON p.noDistributor = d.noDistributor
WHERE p.idUser = ?
ORDER BY p.tanggalKirim DESC

Returns:
  - noPengiriman
  - noSuratJalan
  - tanggalKirim
  - alamatPengiriman
  - statusPengiriman
  - noPesanan
  - namaDistributor
```

### UPDATE - Edit Delivery

```
POST to: index.php?page=pengiriman
Parameters:
  - action = "ubah"
  - noPengiriman (hidden)
  - noSuratJalan (string)
  - tanggalKirim (date)
  - alamatPengiriman (text)
  - statusPengiriman (string)
  - noPesanan (hidden)
```

### DELETE - Remove Delivery

```
GET to: index.php?page=pengiriman&hapus={noPengiriman}
```

---

## 📊 REPORTS (LAPORAN)

**File**: `pages/laporan.php`

### Query Parameters

```
GET Parameters (optional):
  - bulan (1-12) - default: current month
  - tahun (YYYY) - default: current year
```

### Data Returned

**1. Total Revenue**

```sql
SELECT SUM(dp.totalHarga) as total_revenue FROM detail_pesanan dp
JOIN pesanan p ON dp.noPesanan = p.noPesanan
WHERE p.idUser = ? AND MONTH(p.tanggalOrder) = ? AND YEAR(p.tanggalOrder) = ?
```

**2. Total Orders**

```sql
SELECT COUNT(*) FROM pesanan
WHERE idUser = ? AND MONTH(tanggalOrder) = ? AND YEAR(tanggalOrder) = ?
```

**3. Total Deliveries**

```sql
SELECT COUNT(*) FROM pengiriman
WHERE idUser = ? AND MONTH(tanggalKirim) = ? AND YEAR(tanggalKirim) = ?
```

**4. Active Distributors**

```sql
SELECT COUNT(DISTINCT d.noDistributor) FROM distributor d
JOIN pesanan p ON d.noDistributor = p.noDistributor
WHERE p.idUser = ? AND MONTH(p.tanggalOrder) = ? AND YEAR(p.tanggalOrder) = ?
```

**5. Delivery Status Breakdown**

```sql
SELECT statusPengiriman, COUNT(*) as count FROM pengiriman
WHERE idUser = ? AND MONTH(tanggalKirim) = ? AND YEAR(tanggalKirim) = ?
GROUP BY statusPengiriman
```

**6. Top Distributors (Ranked by Revenue)**

```sql
SELECT d.namaDistributor, COUNT(p.noPesanan) as order_count, SUM(dp.totalHarga) as revenue
FROM distributor d
JOIN pesanan p ON d.noDistributor = p.noDistributor
LEFT JOIN detail_pesanan dp ON p.noPesanan = dp.noPesanan
WHERE p.idUser = ? AND MONTH(p.tanggalOrder) = ? AND YEAR(p.tanggalOrder) = ?
GROUP BY d.namaDistributor
ORDER BY revenue DESC LIMIT 10
```

---

## 📈 DASHBOARD

**File**: `pages/dashboard.php`

### Auto-Calculated Statistics

```
- Total Orders: COUNT(*)
- Total Distributors: COUNT(DISTINCT)
- Pending Deliveries: COUNT(WHERE statusPengiriman='Pending')
- Total Revenue: SUM(totalHarga)
- Status Breakdown: GROUP BY statusPengiriman
- Recent Orders: LIMIT 5 ORDER BY tanggalOrder DESC
```

---

## 🔄 ID GENERATION PATTERNS

| Entity       | Pattern                 | Example               |
| ------------ | ----------------------- | --------------------- |
| Distributor  | DST{001-999}            | DST001, DST002        |
| Order        | SO{YYYYMMDD}{001-999}   | SO202501001001        |
| Order Detail | DTL-{noPesanan}-{01-99} | DTL-SO202501001001-01 |
| Delivery     | PG{YYYYMMDD}{001-999}   | PG202501001001        |

**Generation Method**:

```php
// Find max existing ID for that date/prefix
SELECT MAX(CAST(SUBSTRING(...) AS UNSIGNED)) as max_id
$next_id = ($max_id ?? 0) + 1;
$new_id = prefix . str_pad($next_id, 3, '0', STR_PAD_LEFT);
```

---

## 🛡️ AUTHORIZATION & FILTERING

**All CRUD operations filter by `idUser`**:

```php
// Example: Only show distributor if owned by user
$query = "SELECT * FROM distributor WHERE noDistributor = ? AND idUser = ?";
$stmt->bind_param('ss', $noDistributor, $current_user_id);
```

This ensures:

-   Users can ONLY see their own data
-   No cross-user data leakage
-   Secure multi-tenant system

---

## 📝 ERROR HANDLING

All errors stored in `$_SESSION['error_message']` and displayed on redirect:

```php
// On error
$_SESSION['error_message'] = 'Error description';
header('Location: index.php?page=...');

// On page load
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-error">
        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>
```

---

## 🔗 FORM SUBMISSION FLOW

1. **Modal Form Opens** (JavaScript in app.js)

    - Form HTML generated dynamically
    - Pre-filled with existing data (if edit mode)

2. **User Submits Form** (JavaScript handler)

    - Creates hidden form with action=POST
    - Submits to index.php?page={module}

3. **Backend Processes** (PHP in pages/{module}.php)

    - Validates input
    - Executes database operation
    - Sets success/error message in session
    - Redirects back to same page

4. **Page Reloads** (index.php)
    - Includes module page
    - Module displays alert message
    - Fetches fresh data from database
    - Clears session message variable

---

## 🚀 PERFORMANCE NOTES

-   All queries use prepared statements (prevent SQL injection)
-   User filtering on every query (security + performance)
-   Indexes on primary/foreign keys (database level)
-   Minimal joins (3-4 tables max per query)
-   No N+1 query problems (single fetch per operation)

---

**Last Updated**: December 25, 2025
**Version**: 1.0 (Final)
