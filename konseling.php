
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

// Mengambil dokter dengan spesialisasi Psikologi Klinis
$query_dokter = mysqli_query($conn, "
    SELECT
        doctors.id,
        doctors.name,
        specialties.name AS specialty_name
    FROM doctors
    INNER JOIN specialties
        ON doctors.specialty_id = specialties.id
    WHERE specialties.name = 'Psikologi Klinis'
    ORDER BY doctors.id ASC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Booking Konseling | rylonaCare+</title>

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
                <a href="dokter.php">Dokter</a>
                <a href="konseling.php" class="active">Konseling</a>
            </nav>

            <div class="nav-buttons">
                <a href="proses/logout-proses.php" class="btn-login">
                    Keluar
                </a>
            </div>

        </div>
    </header>


    <!-- KONTEN KONSELING -->

    <section class="counseling-page section">

        <div class="container">

            <div class="counseling-page-heading">

                <div class="section-label">
                    MENTAL HEALTH CARE
                </div>

                <h1>Booking Konseling Psikologi</h1>

                <p>
                    Sediakan waktu untuk diri sendiri.
                    Pilih jadwal konseling sesuai kebutuhan Anda
                    melalui layanan rylonaCare+.
                </p>

            </div>


            <div class="counseling-form-container">

                <div class="counseling-form-intro">
                    <h2>Formulir Booking Konseling</h2>

                    <p>
                        Silakan lengkapi informasi berikut untuk
                        mengajukan sesi konseling.
                    </p>
                </div>


                <form action="proses/konseling-proses.php" method="POST">

                    <div class="form-group">
                        <label>Dokter Psikologi</label>

                        <select name="doctor_id" required>
                            <option value="">Pilih dokter psikologi</option>

                            <?php if ($query_dokter && mysqli_num_rows($query_dokter) > 0): ?>

                                <?php while ($dokter = mysqli_fetch_assoc($query_dokter)): ?>

                                    <option value="<?php echo (int) $dokter['id']; ?>">
                                        <?php echo htmlspecialchars($dokter['name']); ?>
                                        - <?php echo htmlspecialchars($dokter['specialty_name']); ?>
                                    </option>

                                <?php endwhile; ?>

                            <?php endif; ?>

                        </select>

                        <?php if (!$query_dokter || mysqli_num_rows($query_dokter) === 0): ?>
                            <small>Dokter psikologi belum tersedia. Silakan hubungi pihak rumah sakit.</small>
                        <?php endif; ?>
                    </div>


                    <div class="form-group">
                        <label>Tanggal Konseling</label>

                        <input
                            type="date"
                            name="booking_date"
                            min="<?php echo date('Y-m-d'); ?>"
                            required
                        >

                        <small>
                            Pilih tanggal sesuai jadwal praktik dokter.
                        </small>
                    </div>


                    <div class="form-group">
                        <label>Waktu Konseling</label>

                        <input
                            type="time"
                            name="booking_time"
                            required
                        >
                    </div>


                    <div class="form-group">
                        <label>Jenis Konseling</label>

                        <select name="counseling_type" required>
                            <option value="">Pilih jenis konseling</option>
                            <option value="Individu">Individu</option>
                            <option value="Online">Online</option>
                            <option value="Tatap Muka">Tatap Muka</option>
                        </select>
                    </div>


                    <button type="submit" class="btn-primary auth-submit">
                        Ajukan Booking Konseling
                    </button>

                    <p class="auth-footer">
                        <a href="beranda.php">← Kembali ke Beranda Pasien</a>
                    </p>

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