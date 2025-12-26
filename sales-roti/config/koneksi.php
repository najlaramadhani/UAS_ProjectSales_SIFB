<?php
/**
 * Database Connection - MySQL (Procedural MySQLi)
 * Database: salesroti_db
 * Host: localhost
 */

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'salesroti_db';

// Create connection
$koneksi = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Set character set
$koneksi->set_charset("utf8mb4");
?>
