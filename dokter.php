
<?php
require_once 'config/database.php';

// Mengambil seluruh data dokter beserta spesialisasinya
$query_dokter = mysqli_query($conn, "
    SELECT
        doctors.id,
        doctors.name,
        specialties.name AS specialty_name
    FROM doctors
    LEFT JOIN specialties
        ON doctors.specialty_id = specialties.id
    ORDER BY
        CASE
            WHEN LOWER(TRIM(doctors.name)) = 'dr. rio arlan' THEN 1
            ELSE 0
        END ASC,
        doctors.id ASC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profil Dokter | rylonaCare+</title>

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
                <a href="index.php">Beranda</a>
                <a href="index.php#tentang">Tentang</a>
                <a href="index.php#layanan">Layanan</a>
                <a href="dokter.php" class="active">Dokter</a>
                <a href="jadwal.php">Jadwal</a>
                <a href="index.php#konseling">Konseling</a>
            </nav>

            <div class="nav-buttons">
                <a href="login.php" class="btn-login">Masuk</a>

                <a href="register.php" class="btn-primary">
                    Daftar Sekarang
                </a>
            </div>

        </div>
    </header>


    <!-- HEADER HALAMAN DOKTER -->

    <section class="doctor-page section">

        <div class="container">

            <div class="doctor-page-heading">

                <div class="section-label">
                    TENAGA MEDIS
                </div>

                <h1>Kenali Dokter Kami</h1>

                <p>
                    Temukan tenaga medis profesional yang siap
                    memberikan pelayanan kesehatan sesuai kebutuhan Anda.
                </p>

            </div>


            <!-- DAFTAR DOKTER DARI DATABASE -->

            <div class="doctor-grid doctor-page-grid">

                <?php if ($query_dokter && mysqli_num_rows($query_dokter) > 0): ?>

                    <?php while ($dokter = mysqli_fetch_assoc($query_dokter)): ?>

                        <div class="doctor-card doctor-page-card">

                            <div class="doctor-info">

                                <span class="doctor-label">
                                    TENAGA MEDIS RYLONACARE+
                                </span>

                                <h3>
                                    <?php
                                    echo htmlspecialchars($dokter['name']);
                                    ?>
                                </h3>

                                <p class="doctor-specialty">
                                    <?php
                                    echo htmlspecialchars(
                                        $dokter['specialty_name'] ?? 'Belum ditentukan'
                                    );
                                    ?>
                                </p>

                                <a href="jadwal.php" class="doctor-schedule-link">
                                    Lihat Jadwal Praktik →
                                </a>

                            </div>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="doctor-empty">
                        <h3>Belum Ada Data Dokter</h3>
                        <p>
                            Informasi dokter belum tersedia.
                            Silakan kunjungi kembali halaman ini.
                        </p>
                    </div>

                <?php endif; ?>

            </div>


            <div class="doctor-page-action">

                <a href="index.php" class="btn-outline">
                    ← Kembali ke Beranda
                </a>

                <a href="jadwal.php" class="btn-primary">
                    Lihat Jadwal Praktik
                </a>

            </div>

        </div>

    </section>


    <!-- FOOTER -->

    <footer class="footer">

        <div class="container footer-content">

            <div>
                <a href="index.php" class="logo footer-logo">
                    rylona<span>Care+</span>
                </a>

                <p>
                    Pelayanan kesehatan yang mudah,
                    nyaman, dan terpercaya.
                </p>
            </div>

            <div>
                <h4>Menu</h4>
                <a href="index.php#tentang">Tentang Kami</a>
                <a href="index.php#layanan">Layanan</a>
                <a href="dokter.php">Dokter</a>
            </div>

            <div>
                <h4>Informasi</h4>
                <p>Jam operasional: 08.00–16.00</p>
                <p>Email: info@rylonacare.test</p>
            </div>

        </div>

        <div class="copyright">
            © 2026 rylonaCare+. All Rights Reserved.
        </div>

    </footer>

</body>
</html>