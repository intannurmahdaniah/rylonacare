
<?php
session_start();

if (!isset($_SESSION['user_id'], $_SESSION['patient_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

$user_id = (int) $_SESSION['user_id'];
$patient_id = (int) $_SESSION['patient_id'];

// Mengambil data profil pasien
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
       AND patients.id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($query, "ii", $user_id, $patient_id);
mysqli_stmt_execute($query);

$result = mysqli_stmt_get_result($query);
$pasien = mysqli_fetch_assoc($result);

mysqli_stmt_close($query);

if (!$pasien) {
    echo "Data profil pasien tidak ditemukan.";
    exit;
}

$pesan = $_GET['pesan'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profil Pasien | rylonaCare+</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <!-- NAVBAR -->

    <header class="navbar">
        <div class="container nav-content">

            <a href="index.php" class="logo">
                rylona<span>Care+</span>
            </a>

            <nav class="nav-menu">
                <a href="beranda.php">Beranda Pasien</a>
                <a href="jadwal.php">Jadwal Dokter</a>
                <a href="riwayat.php">Riwayat</a>
                <a href="profil.php" class="active">Profil</a>
            </nav>

            <div class="nav-buttons">
                <a href="proses/logout-proses.php" class="btn-login">
                    Keluar
                </a>
            </div>

        </div>
    </header>


    <!-- PROFIL PASIEN -->

    <section class="profile-page section">

        <div class="container">

            <div class="profile-heading">

                <div class="section-label">
                    INFORMASI AKUN
                </div>

                <h1>Profil Pasien</h1>

                <p>
                    Periksa dan perbarui informasi pribadi
                    yang terdaftar pada akun rylonaCare+.
                </p>

            </div>


            <?php if ($pesan === 'berhasil'): ?>
                <div class="profile-alert profile-success">
                    Profil berhasil diperbarui.
                </div>
            <?php elseif ($pesan === 'email'): ?>
                <div class="profile-alert profile-error">
                    Email sudah digunakan oleh akun lain.
                </div>
            <?php elseif ($pesan === 'nik'): ?>
                <div class="profile-alert profile-error">
                    NIK sudah digunakan oleh pasien lain.
                </div>
            <?php elseif ($pesan === 'tidak_valid'): ?>
                <div class="profile-alert profile-error">
                    Data yang dimasukkan tidak valid. Periksa kembali formulir.
                </div>
            <?php elseif ($pesan === 'gagal'): ?>
                <div class="profile-alert profile-error">
                    Profil gagal diperbarui. Silakan coba kembali.
                </div>
            <?php endif; ?>


            <div class="profile-form-container">

                <div class="profile-form-intro">
                    <h2>Informasi Pribadi</h2>
                    <p>Pastikan data yang kamu masukkan sudah benar.</p>
                </div>


                <form action="proses/profil-proses.php" method="POST">

                    <div class="form-group">
                        <label>Nama Lengkap</label>

                        <input
                            type="text"
                            name="name"
                            value="<?php echo htmlspecialchars($pasien['name']); ?>"
                            required
                        >
                    </div>


                    <div class="form-group">
                        <label>Email</label>

                        <input
                            type="email"
                            name="email"
                            value="<?php echo htmlspecialchars($pasien['email']); ?>"
                            required
                        >
                    </div>


                    <div class="form-group">
                        <label>Nomor Induk Kependudukan (NIK)</label>

                        <input
                            type="text"
                            name="nik"
                            value="<?php echo htmlspecialchars($pasien['nik']); ?>"
                            maxlength="16"
                            pattern="[0-9]{16}"
                            required
                        >
                    </div>


                    <div class="form-group">
                        <label>Nomor Telepon</label>

                        <input
                            type="tel"
                            name="phone"
                            value="<?php echo htmlspecialchars($pasien['phone']); ?>"
                            required
                        >
                    </div>


                    <div class="form-row">

                        <div class="form-group">
                            <label>Jenis Kelamin</label>

                            <select name="gender" required>
                                <option value="">Pilih jenis kelamin</option>

                                <option value="Laki-laki"
                                    <?php echo $pasien['gender'] === 'Laki-laki' ? 'selected' : ''; ?>>
                                    Laki-laki
                                </option>

                                <option value="Perempuan"
                                    <?php echo $pasien['gender'] === 'Perempuan' ? 'selected' : ''; ?>>
                                    Perempuan
                                </option>
                            </select>
                        </div>


                        <div class="form-group">
                            <label>Tanggal Lahir</label>

                            <input
                                type="date"
                                name="birth_date"
                                value="<?php echo htmlspecialchars($pasien['birth_date']); ?>"
                                max="<?php echo date('Y-m-d'); ?>"
                                required
                            >
                        </div>

                    </div>


                    <div class="form-group">
                        <label>Alamat Lengkap</label>

                        <textarea
                            name="address"
                            rows="4"
                            required
                        ><?php echo htmlspecialchars($pasien['address']); ?></textarea>
                    </div>


                    <div class="profile-form-actions">

                        <a href="beranda.php" class="btn-outline">
                            Batal
                        </a>

                        <button type="submit" class="btn-primary">
                            Simpan Perubahan
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </section>


    <!-- FOOTER -->

    <footer class="footer">
        <div class="copyright">
            © 2026 rylonaCare+. All Rights Reserved.
        </div>
    </footer>

</body>
</html>