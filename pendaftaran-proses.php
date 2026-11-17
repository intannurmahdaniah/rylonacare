
<?php
session_start();

require_once '../config/database.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


// ======================================
// MEMASTIKAN PASIEN SUDAH LOGIN
// ======================================

if (!isset($_SESSION['user_id'], $_SESSION['patient_id'])) {
    header("Location: ../login.php");
    exit;
}


// ======================================
// MEMASTIKAN FORM DIKIRIM MELALUI POST
// ======================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php#layanan");
    exit;
}


// ======================================
// MENGAMBIL DATA FORMULIR
// ======================================

$patient_id = (int) $_SESSION['patient_id'];

$schedule_id = filter_input(
    INPUT_POST,
    'schedule_id',
    FILTER_VALIDATE_INT
);

$appointment_date = trim($_POST['appointment_date'] ?? '');
$complaint = trim($_POST['complaint'] ?? '');
$poli = strtolower(trim($_POST['poli'] ?? ''));


// ======================================
// PEMETAAN POLI DAN SPESIALISASI
// ======================================

$daftar_poli = [
    'umum' => [
        'nama' => 'Poli Umum',
        'spesialisasi' => 'Dokter Umum'
    ],

    'anak' => [
        'nama' => 'Poli Anak',
        'spesialisasi' => 'Spesialis Anak'
    ],

    'gigi' => [
        'nama' => 'Poli Gigi',
        'spesialisasi' => 'Spesialis Gigi'
    ]
];


// ======================================
// FUNGSI PESAN PENDAFTARAN
// ======================================

function pesanPendaftaran($judul, $pesan, $berhasil = false, $poli = '')
{
    $warna = $berhasil ? '#168b83' : '#dc3545';

    if ($berhasil) {
        $tautan = '<a href="../beranda.php">Kembali ke Beranda</a>';
    } else {
        $url = '../pendaftaran.php?poli=' . urlencode($poli);

        $tautan = '<a href="' . htmlspecialchars($url) . '">Kembali ke Pendaftaran</a>';
    }

    echo '
    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Pendaftaran Pemeriksaan | rylonaCare+</title>

        <style>
            body {
                margin: 0;
                font-family: Arial, sans-serif;
                background: #f5f8fa;
            }

            .wrapper {
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
            }

            .card {
                max-width: 450px;
                width: 100%;
                padding: 35px;
                background: white;
                border-radius: 15px;
                text-align: center;
                box-shadow: 0 10px 30px rgba(0,0,0,.05);
            }

            h2 {
                color: ' . $warna . ';
            }

            p {
                color: #64748b;
                line-height: 1.7;
            }

            a {
                display: inline-block;
                margin-top: 15px;
                padding: 12px 22px;
                border-radius: 8px;
                background: ' . $warna . ';
                color: white;
                text-decoration: none;
            }
        </style>

    </head>

    <body>

        <div class="wrapper">

            <div class="card">

                <h2>' . htmlspecialchars($judul) . '</h2>

                <p>' . htmlspecialchars($pesan) . '</p>

                ' . $tautan . '

            </div>

        </div>

    </body>
    </html>';

    exit;
}


// ======================================
// VALIDASI PILIHAN POLI
// ======================================

if (!array_key_exists($poli, $daftar_poli)) {

    header("Location: ../index.php#layanan");
    exit;

}

$nama_spesialisasi = $daftar_poli[$poli]['spesialisasi'];


// ======================================
// VALIDASI DATA FORMULIR
// ======================================

if (!$schedule_id || $appointment_date === '') {

    pesanPendaftaran(
        'Data Belum Lengkap',
        'Silakan pilih jadwal dokter dan tanggal pemeriksaan.',
        false,
        $poli
    );

}


// ======================================
// VALIDASI TANGGAL
// ======================================

$tanggal = DateTime::createFromFormat(
    '!Y-m-d',
    $appointment_date
);

$hari_ini = new DateTime('today');

if (
    !$tanggal ||
    $tanggal->format('Y-m-d') !== $appointment_date ||
    $tanggal < $hari_ini
) {

    pesanPendaftaran(
        'Tanggal Tidak Valid',
        'Tanggal pemeriksaan tidak boleh sebelum hari ini.',
        false,
        $poli
    );

}


// ======================================
// MENGUBAH NAMA HARI KE BAHASA INDONESIA
// ======================================

$nama_hari = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu'
];

$hari = $nama_hari[$tanggal->format('l')];


// ======================================
// PROSES PENDAFTARAN
// ======================================

try {

    mysqli_begin_transaction($conn);


    // ======================================
    // MENGAMBIL DATA JADWAL DAN DOKTER
    // SEKALIGUS MEMVALIDASI KESESUAIAN POLI
    // ======================================

    $cek_jadwal = mysqli_prepare(
        $conn,
        "SELECT
            schedules.id,
            schedules.doctor_id,
            schedules.day,
            schedules.quota,
            specialties.name AS specialty_name
         FROM schedules
         INNER JOIN doctors
            ON schedules.doctor_id = doctors.id
         INNER JOIN specialties
            ON doctors.specialty_id = specialties.id
         WHERE schedules.id = ?
           AND specialties.name = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $cek_jadwal,
        "is",
        $schedule_id,
        $nama_spesialisasi
    );

    mysqli_stmt_execute($cek_jadwal);

    $hasil_jadwal = mysqli_stmt_get_result($cek_jadwal);

    $jadwal = mysqli_fetch_assoc($hasil_jadwal);

    mysqli_stmt_close($cek_jadwal);


    // ======================================
    // MEMASTIKAN JADWAL DAN POLI SESUAI
    // ======================================

    if (!$jadwal) {

        mysqli_rollback($conn);

        pesanPendaftaran(
            'Dokter Tidak Sesuai',
            'Dokter atau jadwal yang dipilih tidak sesuai dengan poli pemeriksaan.',
            false,
            $poli
        );

    }


    // ======================================
    // MEMERIKSA KESESUAIAN HARI PRAKTIK
    // ======================================

    if ($jadwal['day'] !== $hari) {

        mysqli_rollback($conn);

        pesanPendaftaran(
            'Hari Tidak Sesuai',
            'Tanggal yang dipilih tidak sesuai dengan hari praktik dokter.',
            false,
            $poli
        );

    }


    // ======================================
    // MEMERIKSA KUOTA PENDAFTARAN
    // ======================================

    $cek_kuota = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM appointments
         WHERE schedule_id = ?
           AND appointment_date = ?
           AND status <> 'Ditolak'"
    );

    mysqli_stmt_bind_param(
        $cek_kuota,
        "is",
        $schedule_id,
        $appointment_date
    );

    mysqli_stmt_execute($cek_kuota);

    $hasil_kuota = mysqli_stmt_get_result($cek_kuota);

    $data_kuota = mysqli_fetch_assoc($hasil_kuota);

    mysqli_stmt_close($cek_kuota);


    if ((int) $data_kuota['total'] >= (int) $jadwal['quota']) {

        mysqli_rollback($conn);

        pesanPendaftaran(
            'Kuota Penuh',
            'Kuota pemeriksaan pada jadwal tersebut sudah terpenuhi. Silakan pilih tanggal lain.',
            false,
            $poli
        );

    }


    // ======================================
    // MENYIMPAN PENDAFTARAN
    // ======================================

    $kode_sementara = 'TEMP';

    $simpan = mysqli_prepare(
        $conn,
        "INSERT INTO appointments
        (
            patient_id,
            doctor_id,
            schedule_id,
            appointment_date,
            booking_code,
            complaint
        )
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $simpan,
        "iiisss",
        $patient_id,
        $jadwal['doctor_id'],
        $schedule_id,
        $appointment_date,
        $kode_sementara,
        $complaint
    );

    mysqli_stmt_execute($simpan);


    // Mengambil ID pendaftaran otomatis

    $appointment_id = mysqli_insert_id($conn);

    mysqli_stmt_close($simpan);


    // ======================================
    // MEMBUAT KODE PENDAFTARAN
    // ======================================

    $booking_code = 'PR-' . str_pad(
        (string) $appointment_id,
        3,
        '0',
        STR_PAD_LEFT
    );


    // Memperbarui kode pendaftaran

    $update_kode = mysqli_prepare(
        $conn,
        "UPDATE appointments
         SET booking_code = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $update_kode,
        "si",
        $booking_code,
        $appointment_id
    );

    mysqli_stmt_execute($update_kode);

    mysqli_stmt_close($update_kode);


    // ======================================
    // MENYIMPAN SELURUH PERUBAHAN
    // ======================================

    mysqli_commit($conn);


    // ======================================
    // PESAN BERHASIL
    // ======================================

    pesanPendaftaran(
        'Pendaftaran Berhasil!',
        'Kode pendaftaran Anda adalah ' . $booking_code . '. Status pendaftaran saat ini Menunggu.',
        true
    );


} catch (mysqli_sql_exception $e) {

    mysqli_rollback($conn);

    error_log(
        "Kesalahan pendaftaran: " . $e->getMessage()
    );

    pesanPendaftaran(
        'Pendaftaran Gagal',
        'Terjadi kesalahan saat menyimpan pendaftaran. Silakan coba kembali.',
        false,
        $poli
    );

}

?>
