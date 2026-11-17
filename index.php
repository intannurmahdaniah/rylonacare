
<?php
require_once 'config/database.php';

// Mengambil data dokter dari database
$query_dokter = mysqli_query($conn, "
    SELECT
        doctors.*,
        specialties.name AS specialty_name
    FROM doctors
    LEFT JOIN specialties
        ON doctors.specialty_id = specialties.id
    ORDER BY
        CASE
            WHEN LOWER(TRIM(doctors.name)) LIKE 'dr. rio arlan%' THEN 1
            ELSE 0
        END ASC,
        doctors.id ASC
    LIMIT 4
");

// Menghitung jumlah dokter
$query_total_dokter = mysqli_query($conn, "SELECT COUNT(*) AS total FROM doctors");
$total_dokter = 0;

if ($query_total_dokter) {
    $data_dokter = mysqli_fetch_assoc($query_total_dokter);
    $total_dokter = (int) $data_dokter['total'];
}

// Menghitung jumlah layanan
$query_total_layanan = mysqli_query($conn, "SELECT COUNT(*) AS total FROM services");
$total_layanan = 0;

if ($query_total_layanan) {
    $data_layanan = mysqli_fetch_assoc($query_total_layanan);
    $total_layanan = (int) $data_layanan['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>rylonaCare+ | Hospital & Healthcare</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        .hero-image.hero-logo {
            width: 100%;
            height: 430px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(20, 65, 85, 0.08);
        }

        .hero-image.hero-logo img {
            width: 85%;
            height: 85%;
            object-fit: contain;
            display: block;
            padding: 15px;
        }

        @media (max-width: 768px) {
            .hero-image.hero-logo {
                height: 300px;
                margin-top: 30px;
            }

            .hero-image.hero-logo img {
                width: 90%;
                height: 90%;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<header class="navbar">
    <div class="container nav-content">

        <a href="index.php" class="logo">
            rylona<span>Care+</span>
        </a>

        <nav class="nav-menu">
            <a href="index.php" class="active">Beranda</a>
            <a href="#tentang">Tentang</a>
            <a href="#layanan">Layanan</a>
            <a href="#dokter">Dokter</a>
            <a href="jadwal.php">Jadwal</a>
            <a href="#konseling">Konseling</a>
        </nav>

        <div class="nav-buttons">
            <a href="login.php" class="btn-login">Masuk</a>
            <a href="register.php" class="btn-primary">Daftar Sekarang</a>
        </div>

    </div>
</header>


<!-- HERO SECTION -->
<section class="hero">
    <div class="container hero-content">

        <div class="hero-text">

            <span class="hero-label">
                ✦ Layanan Kesehatan Terpercaya
            </span>

            <h1>
                Kesehatan Anda,<br>
                <span>Prioritas Kami.</span>
            </h1>

            <p>
                Dapatkan pelayanan kesehatan yang nyaman,
                mudah, dan terpercaya. Temukan dokter sesuai
                kebutuhan serta lakukan pendaftaran secara online.
            </p>

            <div class="hero-buttons">
                <a href="pendaftaran.php" class="btn-primary">
                    Buat Janji Temu →
                </a>

                <a href="#layanan" class="btn-outline">
                    Jelajahi Layanan
                </a>
            </div>

            <div class="hero-info">
                <div>
                    <strong><?php echo $total_dokter; ?>+</strong>
                    <small>Dokter Terdaftar</small>
                </div>

                <div>
                    <strong><?php echo $total_layanan; ?>+</strong>
                    <small>Layanan Kesehatan</small>
                </div>

                <div>
                    <strong>Online</strong>
                    <small>Pendaftaran Mudah</small>
                </div>
            </div>

        </div>

        <div class="hero-image hero-logo">
            <img
                src="assets/css/js/images/logo-rylonacare.png.png"
                alt="Logo rylonaCare+"
            >
        </div>

    </div>
</section>


<!-- ABOUT SECTION -->
<section class="about section" id="tentang">
    <div class="container about-content">

        <div class="section-label">TENTANG KAMI</div>

        <h2>
            Pelayanan Kesehatan yang<br>
            Lebih Mudah dan Nyaman
        </h2>

        <p>
            rylonaCare+ merupakan platform informasi dan
            pendaftaran layanan rumah sakit yang dirancang
            untuk membantu masyarakat memperoleh akses
            pelayanan kesehatan secara lebih mudah.
        </p>

    </div>
</section>


<!-- SERVICES SECTION -->
<section class="services section" id="layanan">
    <div class="container">

        <div class="section-heading">
            <div>
                <div class="section-label">LAYANAN KAMI</div>
                <h2>Layanan Kesehatan</h2>
                <p>Temukan layanan kesehatan sesuai kebutuhan Anda.</p>
            </div>
        </div>

        <div class="service-grid">

            <!-- POLI UMUM -->
            <div class="service-card">
                <div class="service-icon">✚</div>
                <h3>Poli Umum</h3>
                <p>Pemeriksaan kesehatan dan konsultasi umum.</p>

                <a href="pendaftaran.php?poli=umum">
                    Daftar Pemeriksaan →
                </a>
            </div>

            <!-- POLI ANAK -->
            <div class="service-card">
                <div class="service-icon">♡</div>
                <h3>Poli Anak</h3>
                <p>Pelayanan pemeriksaan kesehatan anak.</p>

                <a href="pendaftaran.php?poli=anak">
                    Daftar Pemeriksaan →
                </a>
            </div>

            <!-- POLI GIGI -->
            <div class="service-card">
                <div class="service-icon">✧</div>
                <h3>Poli Gigi</h3>
                <p>Pelayanan kesehatan dan pemeriksaan gigi.</p>

                <a href="pendaftaran.php?poli=gigi">
                    Daftar Pemeriksaan →
                </a>
            </div>

            <!-- KONSELING PSIKOLOGI -->
            <div class="service-card">
                <div class="service-icon">☘</div>
                <h3>Konseling Psikologi</h3>
                <p>Layanan konsultasi untuk kesehatan mental.</p>

                <a href="konseling.php">
                    Booking Konseling →
                </a>
            </div>

        </div>
    </div>
</section>


<!-- DOCTOR SECTION -->
<section class="doctors section" id="dokter">
    <div class="container">

        <div class="section-heading">
            <div>
                <div class="section-label">TENAGA MEDIS</div>
                <h2>Kenali Dokter Kami</h2>
                <p>Didukung tenaga profesional di bidang kesehatan.</p>
            </div>

            <a href="dokter.php" class="text-link">
                Lihat Semua Dokter →
            </a>
        </div>

        <div class="doctor-grid" id="doctor-list">

            <?php if ($query_dokter && mysqli_num_rows($query_dokter) > 0): ?>

                <?php while ($dokter = mysqli_fetch_assoc($query_dokter)): ?>

                    <div class="doctor-card">

                        <div class="doctor-placeholder">DR</div>

                        <h3>
                            <?php echo htmlspecialchars($dokter['name']); ?>
                        </h3>

                        <p>
                            <?php echo htmlspecialchars(
                                $dokter['specialty_name'] ?? 'Belum ditentukan'
                            ); ?>
                        </p>

                        <a
                            href="jadwal.php?doctor_id=<?php echo (int) $dokter['id']; ?>"
                            class="doctor-schedule-link"
                        >
                            Lihat Jadwal Praktik →
                        </a>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p>Belum ada data dokter tersedia.</p>

            <?php endif; ?>

        </div>
    </div>
</section>


<!-- COUNSELING SECTION -->
<section class="counseling section" id="konseling">
    <div class="container counseling-content">

        <div>
            <div class="section-label">MENTAL HEALTH CARE</div>

            <h2>
                Tidak Apa-Apa untuk<br>
                Tidak Selalu Baik-Baik Saja.
            </h2>

            <p>
                Setiap perasaan layak didengar dan setiap cerita
                pantas mendapatkan ruang. Tak perlu menghadapi
                semuanya sendirian. Temukan ruang aman untuk
                bercerita, memahami diri, dan memulihkan hati
                melalui layanan konseling psikologi rylonaCare+.
            </p>

            <a href="konseling.php" class="btn-white">
                Booking Konseling →
            </a>
        </div>

        <div class="counseling-icon">♡</div>

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
            <a href="#tentang">Tentang Kami</a>
            <a href="#layanan">Layanan</a>
            <a href="#dokter">Dokter</a>
            <a href="jadwal.php">Jadwal Dokter</a>
        </div>

        <div>
            <h4>Informasi</h4>
            <p>Jam operasional: 08.00–16.00</p>
            <p>Email: medical@rylonacare.test</p>
        </div>

    </div>

    <div class="copyright">
        © 2026 rylonaCare+. All Rights Reserved.
    </div>
</footer>

</body>
</html>
