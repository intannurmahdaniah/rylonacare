
<?php
session_start();

// ======================================
// MEMASTIKAN PASIEN SUDAH LOGIN
// ======================================

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

// ======================================
// PEMETAAN POLI DAN SPECIALTY ID
// ======================================

$daftar_poli = [
    'umum' => [
        'nama' => 'Poli Umum',
        'specialty_id' => 1,
        'spesialisasi' => 'Dokter Umum'
    ],

    'anak' => [
        'nama' => 'Poli Anak',
        'specialty_id' => 2,
        'spesialisasi' => 'Spesialis Anak'
    ],

    'gigi' => [
        'nama' => 'Poli Gigi',
        'specialty_id' => 3,
        'spesialisasi' => 'Spesialis Gigi'
    ]
];

// ======================================
// MENGAMBIL PILIHAN POLI DARI URL
// ======================================

$poli = strtolower(trim($_GET['poli'] ?? ''));

// Jika poli tidak valid, kembali ke halaman layanan
if (!array_key_exists($poli, $daftar_poli)) {
    header("Location: index.php#layanan");
    exit;
}

$nama_poli = $daftar_poli[$poli]['nama'];
$specialty_id = $daftar_poli[$poli]['specialty_id'];

// ======================================
// MENGAMBIL JADWAL DOKTER
// SESUAI POLI YANG DIPILIH
// ======================================

$query_jadwal = mysqli_prepare(
    $conn,
    "SELECT
        schedules.id AS schedule_id,
        schedules.day,
        schedules.start_time,
        schedules.end_time,
        schedules.quota,
        doctors.id AS doctor_id,
        doctors.name AS doctor_name,
        specialties.name AS specialty_name
     FROM schedules
     INNER JOIN doctors
        ON schedules.doctor_id = doctors.id
     INNER JOIN specialties
        ON doctors.specialty_id = specialties.id
     WHERE doctors.specialty_id = ?
     ORDER BY
        FIELD(
            schedules.day,
            'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu',
            'Minggu'
        ),
        schedules.start_time ASC"
);

mysqli_stmt_bind_param(
    $query_jadwal,
    "i",
    $specialty_id
);

mysqli_stmt_execute($query_jadwal);

$result_jadwal = mysqli_stmt_get_result($query_jadwal);

$jumlah_jadwal = mysqli_num_rows($result_jadwal);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pendaftaran Pemeriksaan | rylonaCare+</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>

        .selected-poli {
            background: #e8f7f5;
            border: 1px solid #c8ebe6;
            color: #087f78;
            padding: 13px 16px;
            border-radius: 9px;
            margin-bottom: 22px;
            font-size: 13px;
        }

        .selected-poli strong {
            display: block;
            margin-top: 4px;
            font-size: 15px;
        }

        .change-poli {
            display: inline-block;
            margin-top: 7px;
            color: #087f78;
            font-size: 12px;
            text-decoration: underline;
        }

    </style>

</head>

<body>

<!-- =========================
     NAVBAR
========================= -->

<header class="navbar">

    <div class="container nav-content">

        <a href="index.php" class="logo">
            rylona<span>Care+</span>
        </a>

        <nav class="nav-menu">

            <a href="beranda.php">
                Beranda Pasien
            </a>

            <a href="jadwal.php">
                Jadwal Dokter
            </a>

            <a href="pendaftaran.php?poli=<?php echo htmlspecialchars($poli); ?>" class="active">
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
     PENDAFTARAN PEMERIKSAAN
========================= -->

<section class="registration-page section">

    <div class="container">

        <!-- HEADING -->

        <div class="registration-heading">

            <div class="section-label">
                LAYANAN KESEHATAN
            </div>

            <h1>Pendaftaran Pemeriksaan</h1>

            <p>
                Silakan pilih dokter dan jadwal praktik
                untuk mengajukan pemeriksaan kesehatan.
            </p>

        </div>

        <!-- FORMULIR -->

        <div class="registration-form-container">

            <div class="registration-form-intro">

                <h2>Formulir Pendaftaran</h2>

                <p>
                    Lengkapi informasi berikut dengan benar.
                </p>

            </div>

            <!-- INFORMASI POLI -->

            <div class="selected-poli">

                Poli yang dipilih:

                <strong>
                    <?php echo htmlspecialchars($nama_poli); ?>
                </strong>

                <a href="index.php#layanan" class="change-poli">
                    Ganti pilihan poli
                </a>

            </div>

            <form action="proses/pendaftaran-proses.php" method="POST">

                <!-- MENYIMPAN PILIHAN POLI -->

                <input
                    type="hidden"
                    name="poli"
                    value="<?php echo htmlspecialchars($poli); ?>"
                >

                <!-- PILIH DOKTER DAN JADWAL -->

                <div class="form-group">

                    <label>Jadwal dan Dokter Pemeriksa</label>

                    <select name="schedule_id" required>

                        <option value="">
                            Pilih dokter dan jadwal
                        </option>

                        <?php if ($jumlah_jadwal > 0): ?>

                            <?php while ($jadwal = mysqli_fetch_assoc($result_jadwal)): ?>

                                <option value="<?php echo (int) $jadwal['schedule_id']; ?>">

                                    <?php echo htmlspecialchars($jadwal['doctor_name']); ?>

                                    -

                                    <?php echo htmlspecialchars($jadwal['specialty_name']); ?>

                                    |

                                    <?php echo htmlspecialchars($jadwal['day']); ?>

                                    |

                                    <?php
                                    echo date(
                                        'H:i',
                                        strtotime($jadwal['start_time'])
                                    );
                                    ?>

                                    -

                                    <?php
                                    echo date(
                                        'H:i',
                                        strtotime($jadwal['end_time'])
                                    );
                                    ?>

                                    |

                                    Kuota: <?php echo (int) $jadwal['quota']; ?> pasien

                                </option>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <option value="" disabled>
                                Belum ada jadwal untuk poli ini
                            </option>

                        <?php endif; ?>

                    </select>

                    <?php if ($jumlah_jadwal === 0): ?>

                        <small>
                            Belum ada jadwal dokter untuk poli ini.
                            Silakan hubungi pihak rumah sakit.
                        </small>

                    <?php endif; ?>

                </div>

                <!-- TANGGAL PEMERIKSAAN -->

                <div class="form-group">

                    <label>Tanggal Pemeriksaan</label>

                    <input
                        type="date"
                        name="appointment_date"
                        min="<?php echo date('Y-m-d'); ?>"
                        required
                    >

                    <small>
                        Pilih tanggal yang sesuai dengan hari praktik dokter.
                    </small>

                </div>

                <!-- KELUHAN PASIEN -->

                <div class="form-group">

                    <label>Keluhan atau Alasan Pemeriksaan</label>

                    <textarea
                        name="complaint"
                        rows="4"
                        placeholder="Ceritakan keluhan atau alasan Anda ingin melakukan pemeriksaan..."
                    ></textarea>

                </div>

                <!-- TOMBOL SUBMIT -->

                <button
                    type="submit"
                    class="btn-primary auth-submit"
                    <?php echo $jumlah_jadwal === 0 ? 'disabled' : ''; ?>
                >
                    Ajukan Pendaftaran
                </button>

                <p class="auth-footer">

                    <a href="beranda.php">
                        ← Kembali ke Beranda Pasien
                    </a>

                </p>

            </form>

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

<?php
mysqli_stmt_close($query_jadwal);
?>