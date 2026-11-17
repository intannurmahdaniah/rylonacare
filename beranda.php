
<?php
session_start();

// Melindungi halaman agar hanya bisa diakses pasien yang login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

// Mengambil informasi pasien
$user_id = $_SESSION['user_id'];

$query = mysqli_prepare(
    $conn,
    "SELECT
        users.name,
        users.email,
        patients.nik,
        patients.phone,
        patients.gender,
        patients.birth_date,
        patients.address
     FROM users
     INNER JOIN patients
        ON users.id = patients.user_id
     WHERE users.id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($query, "i", $user_id);
mysqli_stmt_execute($query);

$result = mysqli_stmt_get_result($query);
$pasien = mysqli_fetch_assoc($result);

mysqli_stmt_close($query);

if (!$pasien) {
    session_unset();
    session_destroy();

    header("Location: login.php?error=gagal");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Beranda Pasien | rylonaCare+</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Tombol Edit Data Profil */
        .profile-action {
            display: flex;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .btn-edit-profile {
            display: inline-block;
            padding: 11px 24px;
            background: #087f78;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 500;
            transition: 0.3s ease;
        }

        .btn-edit-profile:hover {
            background: #06645f;
            transform: translateY(-2px);
        }
    </style>

</head>

<body>


<!-- =========================
     NAVBAR
========================= -->

<header class="navbar">

    <div class="container nav-content">

        <a href="beranda.php" class="logo">
            rylona<span>Care+</span>
        </a>

        <nav class="nav-menu">

            <a href="beranda.php" class="active">
                Beranda Pasien
            </a>

            <a href="jadwal.php">
                Jadwal Dokter
            </a>

            <a href="pendaftaran.php">
                Pemeriksaan
            </a>

            <a href="riwayat.php">
                Riwayat
            </a>

        </nav>

        <div class="nav-buttons">

            <a href="proses/logout-proses.php" class="btn-login">
                Keluar
            </a>

        </div>

    </div>

</header>


<!-- =========================
     BERANDA PASIEN
========================= -->

<section class="patient-home section">

    <div class="container">


        <!-- SAMBUTAN PASIEN -->

        <div class="patient-welcome">

            <div class="section-label">
                AREA PASIEN
            </div>

            <h1>
                Selamat Datang,
                <?php echo htmlspecialchars($pasien['name']); ?>!
            </h1>

            <p>
                Selamat datang di layanan pasien rylonaCare+.
                Silakan pilih layanan kesehatan yang Anda butuhkan.
            </p>

        </div>


        <!-- MENU UTAMA PASIEN -->

        <div class="patient-menu">


            <!-- JADWAL DOKTER -->

            <a href="jadwal.php" class="patient-menu-card">

                <div class="patient-menu-icon">
                    ✚
                </div>

                <h3>Jadwal Dokter</h3>

                <p>
                    Lihat jadwal praktik dokter rylonaCare+.
                </p>

            </a>


            <!-- PENDAFTARAN PEMERIKSAAN -->

            <a href="pendaftaran.php" class="patient-menu-card">

                <div class="patient-menu-icon">
                    ♡
                </div>

                <h3>Pendaftaran Pemeriksaan</h3>

                <p>
                    Daftar pemeriksaan kesehatan secara online.
                </p>

            </a>


            <!-- RIWAYAT BOOKING -->

            <a href="riwayat.php" class="patient-menu-card">

                <div class="patient-menu-icon">
                    ▤
                </div>

                <h3>Riwayat Booking</h3>

                <p>
                    Pantau status pendaftaran dan riwayat booking Anda.
                </p>

            </a>


        </div>


        <!-- INFORMASI AKUN PASIEN -->

        <div class="patient-profile">

            <h2>Informasi Akun Pasien</h2>

            <div class="profile-list">


                <div class="profile-item">

                    <span>Nama Lengkap</span>

                    <strong>
                        <?php echo htmlspecialchars($pasien['name']); ?>
                    </strong>

                </div>


                <div class="profile-item">

                    <span>Email</span>

                    <strong>
                        <?php echo htmlspecialchars($pasien['email']); ?>
                    </strong>

                </div>


                <div class="profile-item">

                    <span>NIK</span>

                    <strong>
                        <?php echo htmlspecialchars($pasien['nik']); ?>
                    </strong>

                </div>


                <div class="profile-item">

                    <span>Nomor Telepon</span>

                    <strong>
                        <?php echo htmlspecialchars($pasien['phone']); ?>
                    </strong>

                </div>


                <div class="profile-item">

                    <span>Jenis Kelamin</span>

                    <strong>
                        <?php echo htmlspecialchars($pasien['gender']); ?>
                    </strong>

                </div>


                <div class="profile-item">

                    <span>Tanggal Lahir</span>

                    <strong>
                        <?php echo htmlspecialchars($pasien['birth_date']); ?>
                    </strong>

                </div>


                <div class="profile-item">

                    <span>Alamat</span>

                    <strong>
                        <?php echo htmlspecialchars($pasien['address']); ?>
                    </strong>

                </div>


            </div>


            <!-- TOMBOL EDIT DATA -->

            <div class="profile-action">

                <a href="profil.php" class="btn-edit-profile">
                    ✎ Edit Data
                </a>

            </div>

        </div>


    </div>

</section>


<!-- =========================
     FOOTER
========================= -->

<footer class="footer">

    <div class="copyright">
        © 2026 rylonaCare+. All Rights Reserved.
    </div>

</footer>


</body>
</html>