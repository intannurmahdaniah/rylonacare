
<?php
session_start();

require_once '../config/database.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Pastikan proses hanya diakses melalui formulir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}

// Mengambil data formulir
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi input kosong
if ($email === '' || $password === '') {
    header("Location: ../login.php?error=kosong");
    exit;
}

// Validasi format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../login.php?error=tidak_valid");
    exit;
}

try {

    // Mencari akun berdasarkan email
    $query = mysqli_prepare(
        $conn,
        "SELECT id, name, email, password, role
         FROM users
         WHERE email = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($query, "s", $email);
    mysqli_stmt_execute($query);

    $result = mysqli_stmt_get_result($query);
    $user = mysqli_fetch_assoc($result);

    mysqli_stmt_close($query);

    // Memeriksa password
    if (!$user || !password_verify($password, $user['password'])) {
        header("Location: ../login.php?error=tidak_valid");
        exit;
    }

    // Pastikan akun merupakan akun pasien
    if ($user['role'] !== 'patient') {
        header("Location: ../login.php?error=tidak_valid");
        exit;
    }

    // Memastikan profil pasien tersedia
    $query_pasien = mysqli_prepare(
        $conn,
        "SELECT id FROM patients WHERE user_id = ? LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $query_pasien,
        "i",
        $user['id']
    );

    mysqli_stmt_execute($query_pasien);

    $result_pasien = mysqli_stmt_get_result($query_pasien);
    $pasien = mysqli_fetch_assoc($result_pasien);

    mysqli_stmt_close($query_pasien);

    if (!$pasien) {
        header("Location: ../login.php?error=tidak_valid");
        exit;
    }

    // Membuat session login
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['patient_id'] = $pasien['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    // Arahkan ke beranda pasien
    header("Location: ../beranda.php");
    exit;

} catch (mysqli_sql_exception $e) {

    error_log("Kesalahan login: " . $e->getMessage());

    header("Location: ../login.php?error=gagal");
    exit;
}
?>