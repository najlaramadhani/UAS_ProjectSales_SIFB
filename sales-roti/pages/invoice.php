<?php
require_once '../config/koneksi.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

// Get order number from GET parameter
$noPesanan = $_GET['noPesanan'] ?? '';
if (empty($noPesanan)) {
    die("No order number provided");
}

// Get order details with distributor info
$query = "SELECT p.*, d.namaDistributor, d.alamat as alamatDistributor, d.kontak, d.email 
          FROM pesanan p 
          LEFT JOIN distributor d ON p.noDistributor = d.noDistributor 
          WHERE p.noPesanan = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param('s', $noPesanan);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found");
}

$order = $result->fetch_assoc();
$stmt->close();

// Get order details (products)
$detail_query = "SELECT dp.*, pr.namaProduk 
                 FROM detail_pesanan dp 
                 LEFT JOIN produk pr ON dp.idProduk = pr.idProduk 
                 WHERE dp.noPesanan = ?";
$detail_stmt = $koneksi->prepare($detail_query);
$detail_stmt->bind_param('s', $noPesanan);
$detail_stmt->execute();
$detail_result = $detail_stmt->get_result();
$details = [];
while ($row = $detail_result->fetch_assoc()) {
    $details[] = $row;
}
$detail_stmt->close();

// Calculate total
$grandTotal = 0;
foreach ($details as $item) {
    $grandTotal += floatval($item['totalHarga']);
}

$koneksi->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice - <?php echo htmlspecialchars($noPesanan); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: white;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 30px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-box {
            width: 48%;
        }
        .info-box h3 {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-box p {
            font-size: 13px;
            line-height: 1.6;
            color: #333;
        }
        .info-box strong {
            display: inline-block;
            width: 120px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table thead {
            background: #f5f5f5;
        }
        table th {
            padding: 12px 8px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #ddd;
        }
        table td {
            padding: 10px 8px;
            font-size: 13px;
            color: #555;
            border-bottom: 1px solid #eee;
        }
        table th.text-right,
        table td.text-right {
            text-align: right;
        }
        table th.text-center,
        table td.text-center {
            text-align: center;
        }
        .totals {
            float: right;
            width: 300px;
        }
        .totals table {
            margin-bottom: 10px;
        }
        .totals table td {
            border: none;
            padding: 8px;
        }
        .totals .grand-total {
            font-size: 16px;
            font-weight: bold;
            background: #f5f5f5;
            border-top: 2px solid #333;
        }
        .footer {
            clear: both;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .print-btn:hover {
            background: #45a049;
        }
        @media print {
            body {
                padding: 0;
            }
            .print-btn {
                display: none;
            }
            .invoice-container {
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ Print Invoice</button>
    
    <div class="invoice-container">
        <div class="header">
            <h1>INVOICE</h1>
            <p>Sales Management System - Roti</p>
        </div>
        
        <div class="info-section">
            <div class="info-box">
                <h3>Invoice Details</h3>
                <p><strong>No. Invoice:</strong> <?php echo htmlspecialchars($noPesanan); ?></p>
                <p><strong>Tanggal Order:</strong> <?php echo date('d F Y', strtotime($order['tanggalOrder'])); ?></p>
                <p><strong>Status:</strong> <span style="color: green; font-weight: bold;"><?php echo strtoupper($order['status']); ?></span></p>
            </div>
            
            <div class="info-box">
                <h3>Distributor</h3>
                <p><strong>Nama:</strong> <?php echo htmlspecialchars($order['namaDistributor']); ?></p>
                <p><strong>Alamat:</strong> <?php echo htmlspecialchars($order['alamatDistributor']); ?></p>
                <p><strong>Kontak:</strong> <?php echo htmlspecialchars($order['kontak']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 40%;">Produk</th>
                    <th class="text-center" style="width: 15%;">Jumlah</th>
                    <th class="text-right" style="width: 20%;">Harga Satuan</th>
                    <th class="text-right" style="width: 20%;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($details as $item): 
                ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($item['namaProduk']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($item['jumlah']); ?></td>
                    <td class="text-right">Rp <?php echo number_format($item['hargaSatuan'], 0, ',', '.'); ?></td>
                    <td class="text-right">Rp <?php echo number_format($item['totalHarga'], 0, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="totals">
            <table>
                <tr class="grand-total">
                    <td><strong>GRAND TOTAL</strong></td>
                    <td class="text-right"><strong>Rp <?php echo number_format($grandTotal, 0, ',', '.'); ?></strong></td>
                </tr>
            </table>
        </div>
        
        <div class="footer">
            <p>Terima kasih atas pesanan Anda!</p>
            <p>Invoice ini dicetak secara otomatis oleh sistem.</p>
        </div>
    </div>
</body>
</html>
