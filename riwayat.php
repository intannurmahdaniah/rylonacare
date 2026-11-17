
<?php
session_start();

// ======================================
// MEMASTIKAN PASIEN SUDAH LOGIN
// ======================================

if (!isset($_SESSION['user_id'], $_SESSION['patient_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

$patient_id = (int) $_SESSION['patient_id'];

// ======================================
// RIWAYAT PENDAFTARAN PEMERIKSAAN
// TERMASUK DOKTER PSIKOLOGI KLINIS
// ======================================

$query_pemeriksaan = mysqli_prepare(
    $conn,
    "SELECT
        appointments.booking_code,
        appointments.appointment_date,
        appointments.complaint,
        appointments.status,
        doctors.name AS doctor_name,
        specialties.name AS specialty_name,
        schedules.day,
        schedules.start_time,
        schedules.end_time
    FROM appointments
    INNER JOIN doctors
        ON appointments.doctor_id = doctors.id
    LEFT JOIN specialties
        ON doctors.specialty_id = specialties.id
    INNER JOIN schedules
        ON appointments.schedule_id = schedules.id
    WHERE appointments.patient_id = ?
    ORDER BY appointments.created_at DESC"
);

mysqli_stmt_bind_param(
    $query_pemeriksaan,
    "i",
    $patient_id
);

mysqli_stmt_execute($query_pemeriksaan);

$result_pemeriksaan = mysqli_stmt_get_result($query_pemeriksaan);

// ======================================
// RIWAYAT BOOKING KONSELING LAMA
// ======================================

$query_konseling = mysqli_prepare(
    $conn,
    "SELECT
        counseling_bookings.booking_code,
        counseling_bookings.booking_date,
        counseling_bookings.booking_time,
        counseling_bookings.counseling_type,
        counseling_bookings.status,
        doctors.name AS doctor_name,
        specialties.name AS specialty_name
    FROM counseling_bookings
    INNER JOIN doctors
        ON counseling_bookings.doctor_id = doctors.id
    LEFT JOIN specialties
        ON doctors.specialty_id = specialties.id
    WHERE counseling_bookings.patient_id = ?
    ORDER BY counseling_bookings.created_at DESC"
);

mysqli_stmt_bind_param(
    $query_konseling,
    "i",
    $patient_id
);

mysqli_stmt_execute($query_konseling);

$result_konseling = mysqli_stmt_get_result($query_konseling);

// ======================================
// FUNGSI FORMAT TANGGAL INDONESIA
// ======================================

function formatTanggal($tanggal)
{
    $bulan = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    $pecah = explode('-', $tanggal);

    if (count($pecah) !== 3) {
        return $tanggal;
    }

    return (int) $pecah[2] . ' ' .
        $bulan[(int) $pecah[1]] . ' ' .
        $pecah[0];
}

// ======================================
// FUNGSI WARNA STATUS
// ======================================

function warnaStatus($status)
{
    if ($status === 'Dikonfirmasi') {
        return 'status-confirmed';
    }

    if ($status === 'Ditolak') {
        return 'status-rejected';
    }

    if ($status === 'Selesai') {
        return 'status-finished';
    }

    return 'status-pending';
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Booking | rylonaCare+</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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

                <a href="pendaftaran.php">
                    Pemeriksaan
                </a>

                <a href="riwayat.php" class="active">
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
         RIWAYAT BOOKING
    ========================= -->

    <section class="history-page section">

        <div class="container">

            <div class="history-heading">

                <div class="section-label">
                    INFORMASI PASIEN
                </div>

                <h1>Riwayat Booking Saya</h1>

                <p>
                    Pantau seluruh pendaftaran pemeriksaan dan
                    booking konseling yang telah Anda ajukan.
                </p>

            </div>

            <!-- =========================
                 RIWAYAT PEMERIKSAAN
            ========================= -->

            <div class="history-section">

                <div class="history-section-heading">

                    <h2>Riwayat Pemeriksaan</h2>

                    <p>
                        Daftar pendaftaran pemeriksaan kesehatan Anda.
                    </p>

                </div>

                <div class="history-list">

                    <?php if (mysqli_num_rows($result_pemeriksaan) > 0): ?>

                        <?php while ($data = mysqli_fetch_assoc($result_pemeriksaan)): ?>

                            <div class="history-card">

                                <div class="history-card-top">

                                    <div>

                                        <span class="history-category">
                                            PENDAFTARAN PEMERIKSAAN
                                        </span>

                                        <h3>
                                            <?php
                                            echo htmlspecialchars(
                                                $data['specialty_name'] ?? 'Pemeriksaan'
                                            );
                                            ?>
                                        </h3>

                                    </div>

                                    <span class="status-badge <?php echo warnaStatus($data['status']); ?>">
                                        <?php echo htmlspecialchars($data['status']); ?>
                                    </span>

                                </div>

                                <div class="history-details">

                                    <div>

                                        <span>Kode Pendaftaran</span>

                                        <strong>
                                            <?php echo htmlspecialchars($data['booking_code']); ?>
                                        </strong>

                                    </div>

                                    <div>

                                        <span>Dokter Pemeriksa</span>

                                        <strong>
                                            <?php echo htmlspecialchars($data['doctor_name']); ?>
                                        </strong>

                                    </div>

                                    <div>

                                        <span>Tanggal Pemeriksaan</span>

                                        <strong>
                                            <?php echo formatTanggal($data['appointment_date']); ?>
                                        </strong>

                                    </div>

                                    <div>

                                        <span>Hari Praktik</span>

                                        <strong>
                                            <?php echo htmlspecialchars($data['day']); ?>
                                        </strong>

                                    </div>

                                    <div>

                                        <span>Jam Praktik</span>

                                        <strong>

                                            <?php
                                            echo date(
                                                'H:i',
                                                strtotime($data['start_time'])
                                            );
                                            ?>

                                            -

                                            <?php
                                            echo date(
                                                'H:i',
                                                strtotime($data['end_time'])
                                            );
                                            ?>

                                        </strong>

                                    </div>

                                </div>

                                <?php if (!empty($data['complaint'])): ?>

                                    <div class="history-complaint">

                                        <span>Keluhan atau Alasan Pemeriksaan</span>

                                        <p>
                                            <?php
                                            echo nl2br(
                                                htmlspecialchars($data['complaint'])
                                            );
                                            ?>
                                        </p>

                                    </div>

                                <?php endif; ?>

                            </div>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <div class="history-empty">

                            <h3>Belum Ada Pendaftaran Pemeriksaan</h3>

                            <p>
                                Anda belum memiliki riwayat pendaftaran pemeriksaan.
                            </p>

                            <a href="pendaftaran.php" class="btn-primary">
                                Daftar Pemeriksaan
                            </a>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

            <!-- =========================
                 RIWAYAT KONSELING
            ========================= -->

            <div class="history-section">

                <div class="history-section-heading">

                    <h2>Riwayat Konseling Psikologi</h2>

                    <p>
                        Daftar booking sesi konseling psikologi Anda sebelumnya.
                    </p>

                </div>

                <div class="history-list">

                    <?php if (mysqli_num_rows($result_konseling) > 0): ?>

                        <?php while ($data = mysqli_fetch_assoc($result_konseling)): ?>

                            <div class="history-card">

                                <div class="history-card-top">

                                    <div>

                                        <span class="history-category">
                                            KONSELING PSIKOLOGI
                                        </span>

                                        <h3>
                                            <?php echo htmlspecialchars($data['counseling_type']); ?>
                                        </h3>

                                    </div>

                                    <span class="status-badge <?php echo warnaStatus($data['status']); ?>">
                                        <?php echo htmlspecialchars($data['status']); ?>
                                    </span>

                                </div>

                                <div class="history-details">

                                    <div>

                                        <span>Kode Booking</span>

                                        <strong>
                                            <?php echo htmlspecialchars($data['booking_code']); ?>
                                        </strong>

                                    </div>

                                    <div>

                                        <span>Psikolog</span>

                                        <strong>
                                            <?php echo htmlspecialchars($data['doctor_name']); ?>
                                        </strong>

                                    </div>

                                    <div>

                                        <span>Tanggal Konseling</span>

                                        <strong>
                                            <?php echo formatTanggal($data['booking_date']); ?>
                                        </strong>

                                    </div>

                                    <div>

                                        <span>Waktu Konseling</span>

                                        <strong>
                                            <?php
                                            echo date(
                                                'H:i',
                                                strtotime($data['booking_time'])
                                            );
                                            ?>
                                        </strong>

                                    </div>

                                </div>

                            </div>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <div class="history-empty">

                            <h3>Belum Ada Booking Konseling</h3>

                            <p>
                                Belum ada riwayat booking konseling sebelumnya.
                            </p>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

            <!-- KEMBALI KE BERANDA -->

            <div class="history-back">

                <a href="beranda.php" class="btn-outline">
                    ← Kembali ke Beranda Pasien
                </a>

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

<?php

mysqli_stmt_close($query_pemeriksaan);
mysqli_stmt_close($query_konseling);

?>