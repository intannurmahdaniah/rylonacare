<?php
session_start();

require_once '../config/database.php';

// Mengaktifkan laporan error MySQL
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Fungsi menampilkan pesan
function tampilkanPesan($judul, $pesan, $berhasil = false)
{
    $warna = $berhasil ? '#168b83' : '#dc3545';
    $link = $berhasil
        ? '<a href="../login.php">Masuk ke Akun</a>'
        : '<a href="../register.php">Kembali ke Registrasi</a>';

    echo '
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Informasi Registrasi | rylonaCare+</title>
        <link rel="stylesheet" href="../assets/css/style.css">
        <style>
            body {
                margin: 0;
                font-family: Poppins, sans-serif;
                background: #f5f8fa;
            }

            .message-container {
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
                box-sizing: border-box;
            }

            .message-card {
                width: 100%;
                max-width: 450px;
                background: white;
                padding: 40px;
                border-radius: 16px;
                text-align: center;
                box-shadow: 0 10px 35px rgba(0,0,0,.05);
                box-sizing: border-box;
            }

            .message-card h2 {
                color: ' . $warna . ';
            }

            .message-card p {
                color: #64748b;
                line-height: 1.7;
            }

            .message-card a {
                display: inline-block;
                margin-top: 15px;
                padding: 12px 25px;
                background: ' . $warna . ';
                color: white;
                text-decoration: none;
                border-radius: 8px;
            }
        </style>
    </head>
    <body>
        <div class="message-container">
            <div class="message-card">
                <h2>' . htmlspecialchars($judul) . '</h2>
                <p>' . htmlspecialchars($pesan) . '</p>
                ' . $link . '
            </div>
        </div>
    </body>
    </html>';

    exit;
}


// Pastikan halaman diakses melalui formulir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../register.php");
    exit;
}


// Mengambil data dari formulir
$name = trim($_POST['name'] ?? '');
$nik = trim($_POST['nik'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$birth_date = trim($_POST['birth_date'] ?? '');
$address = trim($_POST['address'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';


// ==============================
// VALIDASI DATA
// ==============================

if (
    $name === '' ||
    $nik === '' ||
    $email === '' ||
    $phone === '' ||
    $gender === '' ||
    $birth_date === '' ||
    $address === '' ||
    $password === '' ||
    $confirm_password === ''
) {
    tampilkanPesan(
        'Registrasi Gagal',
        'Semua kolom wajib diisi.'
    );
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    tampilkanPesan(
        'Email Tidak Valid',
        'Silakan masukkan alamat email yang benar.'
    );
}

if (!preg_match('/^[0-9]{16}$/', $nik)) {
    tampilkanPesan(
        'NIK Tidak Valid',
        'NIK harus terdiri dari 16 digit angka.'
    );
}

if (!in_array($gender, ['Laki-laki', 'Perempuan'], true)) {
    tampilkanPesan(
        'Data Tidak Valid',
        'Silakan pilih jenis kelamin yang tersedia.'
    );
}

if (strlen($password) < 8) {
    tampilkanPesan(
        'Password Terlalu Pendek',
        'Password harus memiliki minimal 8 karakter.'
    );
}

if ($password !== $confirm_password) {
    tampilkanPesan(
        'Password Tidak Cocok',
        'Password dan konfirmasi password harus sama.'
    );
}

// Validasi tanggal lahir
$tanggal = DateTime::createFromFormat('Y-m-d', $birth_date);
$sekarang = new DateTime('today');

if (
    !$tanggal ||
    $tanggal->format('Y-m-d') !== $birth_date ||
    $tanggal > $sekarang
) {
    tampilkanPesan(
        'Tanggal Lahir Tidak Valid',
        'Silakan masukkan tanggal lahir yang benar.'
    );
}


// ==============================
// MENYIMPAN DATA KE DATABASE
// ==============================

try {

    mysqli_begin_transaction($conn);

    // Memeriksa email yang sudah terdaftar
    $cek_email = mysqli_prepare(
        $conn,
        "SELECT id FROM users WHERE email = ?"
    );

    mysqli_stmt_bind_param($cek_email, "s", $email);
    mysqli_stmt_execute($cek_email);
    mysqli_stmt_store_result($cek_email);

    if (mysqli_stmt_num_rows($cek_email) > 0) {
        mysqli_stmt_close($cek_email);
        mysqli_rollback($conn);

        tampilkanPesan(
            'Email Sudah Terdaftar',
            'Gunakan alamat email lain atau masuk menggunakan akun yang sudah ada.'
        );
    }

    mysqli_stmt_close($cek_email);


    // Memeriksa NIK yang sudah terdaftar
    $cek_nik = mysqli_prepare(
        $conn,
        "SELECT id FROM patients WHERE nik = ?"
    );

    mysqli_stmt_bind_param($cek_nik, "s", $nik);
    mysqli_stmt_execute($cek_nik);
    mysqli_stmt_store_result($cek_nik);

    if (mysqli_stmt_num_rows($cek_nik) > 0) {
        mysqli_stmt_close($cek_nik);
        mysqli_rollback($conn);

        tampilkanPesan(
            'NIK Sudah Terdaftar',
            'NIK tersebut sudah digunakan pada akun pasien lain.'
        );
    }

    mysqli_stmt_close($cek_nik);


    // Mengenkripsi password
    $password_hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );


    // Menyimpan akun ke tabel users
    $query_user = mysqli_prepare(
        $conn,
        "INSERT INTO users (name, email, password, role)
         VALUES (?, ?, ?, 'patient')"
    );

    mysqli_stmt_bind_param(
        $query_user,
        "sss",
        $name,
        $email,
        $password_hash
    );

    mysqli_stmt_execute($query_user);

    // Mengambil ID akun yang baru dibuat
    $user_id = mysqli_insert_id($conn);

    mysqli_stmt_close($query_user);


    // Menyimpan profil pasien ke tabel patients
    $query_patient = mysqli_prepare(
        $conn,
        "INSERT INTO patients
        (user_id, nik, phone, gender, birth_date, address)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $query_patient,
        "isssss",
        $user_id,
        $nik,
        $phone,
        $gender,
        $birth_date,
        $address
    );

    mysqli_stmt_execute($query_patient);

    mysqli_stmt_close($query_patient);


    // Menyimpan kedua data secara bersamaan
    mysqli_commit($conn);

    tampilkanPesan(
        'Registrasi Berhasil!',
        'Akun pasien rylonaCare+ berhasil dibuat. Silakan masuk menggunakan email dan password Anda.',
        true
    );


} catch (mysqli_sql_exception $e) {

    mysqli_rollback($conn);

    error_log("Kesalahan registrasi: " . $e->getMessage());

    tampilkanPesan(
        'Registrasi Gagal',
        'Terjadi kesalahan saat menyimpan data. Silakan periksa kembali atau coba beberapa saat lagi.'
    );
}
?>