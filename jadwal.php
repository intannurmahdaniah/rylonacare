
<?php
require_once 'config/database.php';

// ======================================
// MEMERIKSA APAKAH ADA DOKTER YANG DIPILIH
// ======================================

$doctor_id = isset($_GET['doctor_id'])
    ? (int) $_GET['doctor_id']
    : 0;

$doctor = null;
$query_jadwal = false;
$doctor_valid = true;

// ======================================
// JIKA PENGUNJUNG MEMILIH DOKTER TERTENTU
// ======================================

if ($doctor_id > 0) {

    // Mengambil informasi dokter
    $query_dokter = mysqli_prepare($conn, "
        SELECT
            doctors.id,
            doctors.name,
            specialties.name AS specialty_name
        FROM doctors
        LEFT JOIN specialties
            ON doctors.specialty_id = specialties.id
        WHERE doctors.id = ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($query_dokter, "i", $doctor_id);
    mysqli_stmt_execute($query_dokter);

    $result_dokter = mysqli_stmt_get_result($query_dokter);
    $doctor = mysqli_fetch_assoc($result_dokter);

    mysqli_stmt_close($query_dokter);

    if (!$doctor) {

        $doctor_valid = false;

    } else {

        // Mengambil jadwal khusus dokter yang dipilih
        $query_jadwal = mysqli_prepare($conn, "
            SELECT
                schedules.*,
                doctors.name AS doctor_name,
                specialties.name AS specialty_name
            FROM schedules
            INNER JOIN doctors
                ON schedules.doctor_id = doctors.id
            LEFT JOIN specialties
                ON doctors.specialty_id = specialties.id
            WHERE schedules.doctor_id = ?
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
                schedules.start_time ASC
        ");

        mysqli_stmt_bind_param($query_jadwal, "i", $doctor_id);
        mysqli_stmt_execute($query_jadwal);

        $result_jadwal = mysqli_stmt_get_result($query_jadwal);

    }

} else {

    // ======================================
    // JIKA TIDAK MEMILIH DOKTER
    // TAMPILKAN SELURUH JADWAL DOKTER
    // ======================================

    $result_jadwal = mysqli_query($conn, "
        SELECT
            schedules.*,
            doctors.name AS doctor_name,
            specialties.name AS specialty_name
        FROM schedules
        INNER JOIN doctors
            ON schedules.doctor_id = doctors.id
        LEFT JOIN specialties
            ON doctors.specialty_id = specialties.id
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
            schedules.start_time ASC
    ");
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo $doctor
            ? 'Jadwal ' . htmlspecialchars($doctor['name'])
            : 'Jadwal Dokter'; ?>
        | rylonaCare+
    </title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        .schedule-filter-info {
            margin-bottom: 25px;
        }

        .schedule-doctor-name {
            color: #173f5f;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .schedule-specialty {
            color: #718096;
            margin-bottom: 15px;
        }

        .schedule-back-link {
            display: inline-block;
            color: #12a89d;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 20px;
        }

        .schedule-back-link:hover {
            text-decoration: underline;
        }

        .schedule-empty {
            text-align: center;
            padding: 35px 15px;
            color: #718096;
        }

        .schedule-table-wrapper {
            overflow-x: auto;
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

            <a href="index.php">Beranda</a>

            <a href="index.php#tentang">Tentang</a>

            <a href="index.php#layanan">Layanan</a>

            <a href="index.php#dokter">Dokter</a>

            <a href="jadwal.php" class="active">
                Jadwal Praktik
            </a>

            <a href="index.php#konseling">Konseling</a>

        </nav>

        <div class="nav-buttons">

            <a href="login.php" class="btn-login">
                Masuk
            </a>

            <a href="register.php" class="btn-primary">
                Daftar
            </a>

        </div>

    </div>

</header>


<!-- =========================
     SCHEDULE SECTION
========================= -->

<section class="schedule-page section">

    <div class="container">

        <div class="section-heading">

            <div>

                <div class="section-label">
                    INFORMASI MEDIS
                </div>

                <?php if ($doctor_id > 0 && $doctor): ?>

                    <h2>Jadwal Praktik Dokter</h2>

                    <p>
                        Berikut jadwal praktik dokter yang Anda pilih.
                    </p>

                <?php else: ?>

                    <h2>Jadwal Praktik Dokter</h2>

                    <p>
                        Temukan jadwal praktik dokter sesuai kebutuhan kesehatan Anda.
                    </p>

                <?php endif; ?>

            </div>

        </div>


        <!-- INFORMASI DOKTER YANG DIPILIH -->

        <?php if ($doctor_id > 0 && $doctor): ?>

            <div class="schedule-filter-info">

                <div class="schedule-doctor-name">
                    <?php echo htmlspecialchars($doctor['name']); ?>
                </div>

                <div class="schedule-specialty">
                    <?php echo htmlspecialchars($doctor['specialty_name'] ?? '-'); ?>
                </div>

                <a href="jadwal.php" class="schedule-back-link">
                    ← Lihat Semua Jadwal Dokter
                </a>

            </div>

        <?php endif; ?>


        <!-- TABEL JADWAL -->

        <div class="schedule-table-wrapper">

            <table class="schedule-table">

                <thead>

                    <tr>
                        <th>No.</th>
                        <th>Nama Dokter</th>
                        <th>Spesialisasi</th>
                        <th>Hari</th>
                        <th>Jam Praktik</th>
                        <th>Kuota</th>
                    </tr>

                </thead>

                <tbody>

                    <?php if ($doctor_id > 0 && !$doctor_valid): ?>

                        <tr>
                            <td colspan="6" class="schedule-empty">
                                Dokter yang Anda pilih tidak ditemukan.
                                <br><br>
                                <a href="jadwal.php">
                                    Kembali ke semua jadwal
                                </a>
                            </td>
                        </tr>

                    <?php elseif (
                        $result_jadwal &&
                        mysqli_num_rows($result_jadwal) > 0
                    ): ?>

                        <?php $no = 1; ?>

                        <?php while ($jadwal = mysqli_fetch_assoc($result_jadwal)): ?>

                            <tr>

                                <td>
                                    <?php echo $no++; ?>
                                </td>

                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars($jadwal['doctor_name']); ?>
                                    </strong>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($jadwal['specialty_name'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($jadwal['day']); ?>
                                </td>

                                <td>
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
                                </td>

                                <td>
                                    <?php echo (int) $jadwal['quota']; ?> pasien
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="schedule-empty">

                                <?php if ($doctor): ?>

                                    Belum ada jadwal praktik untuk dokter ini.

                                <?php else: ?>

                                    Belum ada jadwal praktik dokter.

                                <?php endif; ?>

                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <?php
        // Menutup statement jika jadwal difilter berdasarkan dokter
        if ($doctor_id > 0 && $doctor) {
            mysqli_stmt_close($query_jadwal);
        }
        ?>

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