
<?php
session_start();

// Memastikan pasien sudah login
if (!isset($_SESSION['user_id'], $_SESSION['patient_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';

// Mengambil ID pasien dari session
$user_id = (int) $_SESSION['user_id'];
$patient_id = (int) $_SESSION['patient_id'];

// Memastikan proses hanya menerima data dari formulir
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../profil.php");
    exit;
}

// Mengambil data dari formulir
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$nik = trim($_POST['nik'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$birth_date = trim($_POST['birth_date'] ?? '');
$address = trim($_POST['address'] ?? '');

// Validasi data tidak boleh kosong
if (
    $name === '' ||
    $email === '' ||
    $nik === '' ||
    $phone === '' ||
    $gender === '' ||
    $birth_date === '' ||
    $address === ''
) {
    header("Location: ../profil.php?pesan=tidak_valid");
    exit;
}

// Validasi format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../profil.php?pesan=tidak_valid");
    exit;
}

// Validasi NIK harus 16 digit
if (!preg_match('/^[0-9]{16}$/', $nik)) {
    header("Location: ../profil.php?pesan=tidak_valid");
    exit;
}

// Validasi jenis kelamin
if (!in_array($gender, ['Laki-laki', 'Perempuan'], true)) {
    header("Location: ../profil.php?pesan=tidak_valid");
    exit;
}

// Validasi tanggal lahir
$date = DateTime::createFromFormat('Y-m-d', $birth_date);

if (
    !$date ||
    $date->format('Y-m-d') !== $birth_date ||
    $birth_date > date('Y-m-d')
) {
    header("Location: ../profil.php?pesan=tidak_valid");
    exit;
}

// Memeriksa email agar tidak sama dengan akun lain
$cek_email = mysqli_prepare(
    $conn,
    "SELECT id
     FROM users
     WHERE email = ?
       AND id <> ?
     LIMIT 1"
);

mysqli_stmt_bind_param($cek_email, "si", $email, $user_id);
mysqli_stmt_execute($cek_email);

$result_email = mysqli_stmt_get_result($cek_email);
$email_ada = mysqli_num_rows($result_email) > 0;

mysqli_stmt_close($cek_email);

if ($email_ada) {
    header("Location: ../profil.php?pesan=email");
    exit;
}

// Memeriksa NIK agar tidak sama dengan pasien lain
$cek_nik = mysqli_prepare(
    $conn,
    "SELECT id
     FROM patients
     WHERE nik = ?
       AND id <> ?
     LIMIT 1"
);

mysqli_stmt_bind_param($cek_nik, "si", $nik, $patient_id);
mysqli_stmt_execute($cek_nik);

$result_nik = mysqli_stmt_get_result($cek_nik);
$nik_ada = mysqli_num_rows($result_nik) > 0;

mysqli_stmt_close($cek_nik);

if ($nik_ada) {
    header("Location: ../profil.php?pesan=nik");
    exit;
}

// Memulai transaksi database
mysqli_begin_transaction($conn);

try {

    // Memperbarui nama dan email pada tabel users
    $update_user = mysqli_prepare(
        $conn,
        "UPDATE users
         SET name = ?, email = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $update_user,
        "ssi",
        $name,
        $email,
        $user_id
    );

    if (!mysqli_stmt_execute($update_user)) {
        throw new Exception("Gagal memperbarui data akun.");
    }

    mysqli_stmt_close($update_user);


    // Memperbarui informasi pasien pada tabel patients
    $update_pasien = mysqli_prepare(
        $conn,
        "UPDATE patients
         SET nik = ?,
             phone = ?,
             gender = ?,
             birth_date = ?,
             address = ?
         WHERE id = ?
           AND user_id = ?"
    );

    mysqli_stmt_bind_param(
        $update_pasien,
        "sssssii",
        $nik,
        $phone,
        $gender,
        $birth_date,
        $address,
        $patient_id,
        $user_id
    );

    if (!mysqli_stmt_execute($update_pasien)) {
        throw new Exception("Gagal memperbarui data pasien.");
    }

    if (mysqli_stmt_affected_rows($update_pasien) === 0) {
        // Tidak berubah tetap boleh berhasil jika data pasien memang ada.
        $cek_pasien = mysqli_prepare(
            $conn,
            "SELECT id FROM patients
             WHERE id = ? AND user_id = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $cek_pasien,
            "ii",
            $patient_id,
            $user_id
        );

        mysqli_stmt_execute($cek_pasien);
        $cek_result = mysqli_stmt_get_result($cek_pasien);
        $pasien_ada = mysqli_num_rows($cek_result) > 0;
        mysqli_stmt_close($cek_pasien);

        if (!$pasien_ada) {
            throw new Exception("Data pasien tidak ditemukan.");
        }
    }

    mysqli_stmt_close($update_pasien);

    // Menyimpan seluruh perubahan
mysqli_commit($conn);

header("Location: ../beranda.php?status=berhasil");
exit;

} catch (Throwable $e) {

    mysqli_rollback($conn);

    header("Location: ../profil.php?pesan=gagal");
    exit;
}
?>
