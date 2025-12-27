<?php
/**
 * Debug script - check laporan data
 */

include 'sales-roti/config/koneksi.php';

echo "<h2>DEBUG LAPORAN DATA</h2>";

$current_user_id = $_SESSION['user_id'] ?? 'TEST';
$bulan = 12;  // December
$tahun = 2025;

echo "<p><strong>Filter:</strong> User = $current_user_id, Bulan = $bulan, Tahun = $tahun</p>";

// Check pesanan table
echo "<h3>1. ALL PESANAN (no filter):</h3>";
$q1 = "SELECT noPesanan, tanggalOrder, idUser FROM pesanan LIMIT 5";
$r1 = $koneksi->query($q1);
echo "Count: " . $r1->num_rows . "<br>";
while ($p = $r1->fetch_assoc()) {
    echo "- noPesanan: " . $p['noPesanan'] . " | tanggal: " . $p['tanggalOrder'] . " | idUser: " . $p['idUser'] . "<br>";
}

// Check with MONTH/YEAR filter
echo "<h3>2. PESANAN with MONTH/YEAR filter (Dec 2025):</h3>";
$q2 = "SELECT noPesanan, tanggalOrder FROM pesanan 
       WHERE MONTH(tanggalOrder) = ? AND YEAR(tanggalOrder) = ?";
$stmt2 = $koneksi->prepare($q2);
$stmt2->bind_param('ii', $bulan, $tahun);
$stmt2->execute();
$r2 = $stmt2->get_result();
echo "Count: " . $r2->num_rows . "<br>";
while ($p = $r2->fetch_assoc()) {
    echo "- tanggal: " . $p['tanggalOrder'] . " (MONTH=" . date('n', strtotime($p['tanggalOrder'])) . ", YEAR=" . date('Y', strtotime($p['tanggalOrder'])) . ")<br>";
}
$stmt2->close();

// Check revenue
echo "<h3>3. REVENUE for all pesanan:</h3>";
$q3 = "SELECT SUM(totalHarga) as total FROM detail_pesanan";
$r3 = $koneksi->query($q3);
$row3 = $r3->fetch_assoc();
echo "Total Revenue: Rp " . number_format($row3['total'], 0) . "<br>";

// Check sample dates in database
echo "<h3>4. Sample dates in PESANAN table:</h3>";
$q4 = "SELECT DISTINCT tanggalOrder FROM pesanan LIMIT 10";
$r4 = $koneksi->query($q4);
while ($p = $r4->fetch_assoc()) {
    $date = $p['tanggalOrder'];
    $month = date('n', strtotime($date));
    $year = date('Y', strtotime($date));
    echo "- $date (MONTH=$month, YEAR=$year)<br>";
}
?>
