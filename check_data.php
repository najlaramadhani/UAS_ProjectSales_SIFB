<?php
/**
 * Test script to check if database has pesanan and detail_pesanan data
 */

include 'sales-roti/config/koneksi.php';

echo "<h2 style='color:blue;'>Database Data Check</h2>";

// Check pesanan table
echo "<h3>1. Check PESANAN table:</h3>";
$q1 = "SELECT COUNT(*) as cnt FROM pesanan";
$r1 = $koneksi->query($q1);
if ($r1) {
    $row = $r1->fetch_assoc();
    echo "Total pesanan: " . $row['cnt'] . "<br>";
    
    if ($row['cnt'] > 0) {
        echo "<ul>";
        $q2 = "SELECT noPesanan, tanggalOrder, idUser, noDistributor, status FROM pesanan LIMIT 3";
        $r2 = $koneksi->query($q2);
        while ($p = $r2->fetch_assoc()) {
            echo "<li>noPesanan: <strong>" . $p['noPesanan'] . "</strong> | ";
            echo "idUser: " . $p['idUser'] . " | ";
            echo "Distributor: " . $p['noDistributor'] . " | ";
            echo "Tanggal: " . $p['tanggalOrder'] . " | ";
            echo "Status: " . $p['status'] . "</li>";
        }
        echo "</ul>";
    }
}

// Check detail_pesanan table
echo "<h3>2. Check DETAIL_PESANAN table:</h3>";
$q3 = "SELECT COUNT(*) as cnt FROM detail_pesanan";
$r3 = $koneksi->query($q3);
if ($r3) {
    $row = $r3->fetch_assoc();
    echo "Total detail_pesanan: " . $row['cnt'] . "<br>";
    
    if ($row['cnt'] > 0) {
        echo "<ul>";
        $q4 = "SELECT idDetail, noPesanan, idProduk, jumlah, hargaSatuan, totalHarga FROM detail_pesanan LIMIT 5";
        $r4 = $koneksi->query($q4);
        while ($d = $r4->fetch_assoc()) {
            echo "<li>noPesanan: <strong>" . $d['noPesanan'] . "</strong> | ";
            echo "Produk: " . $d['idProduk'] . " | ";
            echo "Jumlah: " . $d['jumlah'] . " | ";
            echo "Harga: Rp " . number_format($d['hargaSatuan'], 0) . "</li>";
        }
        echo "</ul>";
    }
}

// Check produk table
echo "<h3>3. Check PRODUK table:</h3>";
$q5 = "SELECT COUNT(*) as cnt FROM produk";
$r5 = $koneksi->query($q5);
if ($r5) {
    $row = $r5->fetch_assoc();
    echo "Total produk: " . $row['cnt'] . "<br>";
}

// Check distributor table
echo "<h3>4. Check DISTRIBUTOR table:</h3>";
$q6 = "SELECT COUNT(*) as cnt FROM distributor";
$r6 = $koneksi->query($q6);
if ($r6) {
    $row = $r6->fetch_assoc();
    echo "Total distributor: " . $row['cnt'] . "<br>";
}

// Check user table
echo "<h3>5. Check USER table (role='sales'):</h3>";
$q7 = "SELECT COUNT(*) as cnt FROM user WHERE role='sales'";
$r7 = $koneksi->query($q7);
if ($r7) {
    $row = $r7->fetch_assoc();
    echo "Total sales users: " . $row['cnt'] . "<br>";
    
    if ($row['cnt'] > 0) {
        echo "<ul>";
        $q8 = "SELECT idUser, nama FROM user WHERE role='sales' LIMIT 3";
        $r8 = $koneksi->query($q8);
        while ($u = $r8->fetch_assoc()) {
            echo "<li>idUser: <strong>" . $u['idUser'] . "</strong> | Nama: " . $u['nama'] . "</li>";
        }
        echo "</ul>";
    }
}

echo "<hr>";
echo "<p style='color:green;'><strong>Jika semua table kosong, tambahkan test data dulu!</strong></p>";
?>
