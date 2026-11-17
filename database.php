<?php

// KONFIGURASI DATABASE
$host = "localhost";
$username = "root";
$password = "";
$database = "rylonacare_db";

// MEMBUAT KONEKSI
$conn = mysqli_connect(
    $host,
    $username,
    $password,
    $database
);

// MEMERIKSA KONEKSI
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// MENGATUR FORMAT KARAKTER
mysqli_set_charset($conn, "utf8mb4");

?>