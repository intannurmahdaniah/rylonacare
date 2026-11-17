
<?php
session_start();

require_once '../config/database.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Memastikan pasien sudah login
if (!isset($_SESSION['user_id'], $_SESSION['patient_id'])) {
    header("Location: ../login.php");
    exit;
}

// Memastikan form dikirim melalui POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../konseling.php");
    exit;
}

// Mengambil data formulir
$patient_id = (int) $_SESSION['patient_id'];
$doctor_id = filter_input(INPUT_POST, 'doctor_id', FILTER_VALIDATE_INT);
$booking_date = trim($_POST['booking_date'] ?? '');
$booking_time = trim($_POST['booking_time'] ?? '');
$counseling_type = trim($_POST['counseling_type'] ?? '');


// Fungsi menampilkan pesan
function pesanBooking($judul, $pesan, $berhasil = false)
{
    $warna = $berhasil ? '#168b83' : '#dc3545';

    $tautan = $berhasil
        ? '<a href="../beranda.php">Kembali ke Beranda</a>'
        : '<a href="../konseling.php">Kembali ke Booking</a>';

    echo '
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking Konseling | rylonaCare+</title>

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


// ==============================
// VALIDASI DATA
// ==============================

if (!$doctor_id || $booking_date === '' || $booking_time === '' || $counseling_type === '') {
    pesanBooking(
        'Data Belum Lengkap',
        'Silakan lengkapi seluruh formulir booking.'
    );
}

if (!in_array($counseling_type, ['Individu', 'Online', 'Tatap Muka'], true)) {
    pesanBooking(
        'Jenis Konseling Tidak Valid',
        'Silakan pilih jenis konseling yang tersedia.'
    );
}


// Validasi tanggal
$tanggal = DateTime::createFromFormat('!Y-m-d', $booking_date);
$hari_ini = new DateTime('today');

if (
    !$tanggal ||
    $tanggal->format('Y-m-d') !== $booking_date ||
    $tanggal < $hari_ini
) {
    pesanBooking(
        'Tanggal Tidak Valid',
        'Tanggal konseling tidak boleh sebelum hari ini.'
    );
}


// Validasi waktu
$waktu = DateTime::createFromFormat('!H:i', $booking_time);

if (!$waktu || $waktu->format('H:i') !== $booking_time) {
    pesanBooking(
        'Waktu Tidak Valid',
        'Silakan masukkan waktu konseling yang benar.'
    );
}

$booking_time_db = $booking_time . ':00';


// Mengubah nama hari ke bahasa Indonesia
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


try {

    mysqli_begin_transaction($conn);


    // Memastikan dokter merupakan dokter psikologi
    $cek_dokter = mysqli_prepare(
        $conn,
        "SELECT doctors.id
         FROM doctors
         INNER JOIN specialties
            ON doctors.specialty_id = specialties.id
         WHERE doctors.id = ?
           AND specialties.name = 'Psikologi Klinis'
         LIMIT 1"
    );

    mysqli_stmt_bind_param($cek_dokter, "i", $doctor_id);
    mysqli_stmt_execute($cek_dokter);

    $hasil_dokter = mysqli_stmt_get_result($cek_dokter);
    $dokter = mysqli_fetch_assoc($hasil_dokter);

    mysqli_stmt_close($cek_dokter);

    if (!$dokter) {
        mysqli_rollback($conn);

        pesanBooking(
            'Dokter Tidak Valid',
            'Dokter psikologi yang dipilih tidak tersedia.'
        );
    }


    // Memeriksa jadwal praktik dokter
    $cek_jadwal = mysqli_prepare(
        $conn,
        "SELECT id, quota
         FROM schedules
         WHERE doctor_id = ?
           AND day = ?
           AND start_time <= ?
           AND end_time >= ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $cek_jadwal,
        "isss",
        $doctor_id,
        $hari,
        $booking_time_db,
        $booking_time_db
    );

    mysqli_stmt_execute($cek_jadwal);

    $hasil_jadwal = mysqli_stmt_get_result($cek_jadwal);
    $jadwal = mysqli_fetch_assoc($hasil_jadwal);

    mysqli_stmt_close($cek_jadwal);

    if (!$jadwal) {
        mysqli_rollback($conn);

        pesanBooking(
            'Jadwal Tidak Tersedia',
            'Dokter tidak memiliki jadwal praktik pada hari atau waktu yang dipilih.'
        );
    }


    // Memeriksa kuota booking pada tanggal tersebut
    $cek_kuota = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM counseling_bookings
         WHERE doctor_id = ?
           AND booking_date = ?
           AND status <> 'Ditolak'"
    );

    mysqli_stmt_bind_param(
        $cek_kuota,
        "is",
        $doctor_id,
        $booking_date
    );

    mysqli_stmt_execute($cek_kuota);

    $hasil_kuota = mysqli_stmt_get_result($cek_kuota);
    $data_kuota = mysqli_fetch_assoc($hasil_kuota);

    mysqli_stmt_close($cek_kuota);

    if ((int) $data_kuota['total'] >= (int) $jadwal['quota']) {
        mysqli_rollback($conn);

        pesanBooking(
            'Kuota Penuh',
            'Kuota konseling dokter pada tanggal tersebut sudah terpenuhi. Silakan pilih tanggal lain.'
        );
    }


    // ==============================
    // MEMBUAT KODE BOOKING SEDERHANA
    // ==============================

    // Mengunci tabel agar nomor booking tidak dibuat bersamaan
    mysqli_query($conn, "LOCK TABLES counseling_bookings WRITE");

    $query_nomor = mysqli_query(
        $conn,
        "SELECT MAX(id) AS nomor_terakhir
         FROM counseling_bookings"
    );

    $data_nomor = mysqli_fetch_assoc($query_nomor);

    $nomor_booking = (int) $data_nomor['nomor_terakhir'] + 1;

    $booking_code = 'KS-' . str_pad(
        (string) $nomor_booking,
        3,
        '0',
        STR_PAD_LEFT
    );

    mysqli_query($conn, "UNLOCK TABLES");


    // ==============================
    // MENYIMPAN BOOKING
    // ==============================

    $simpan = mysqli_prepare(
        $conn,
        "INSERT INTO counseling_bookings
        (patient_id, doctor_id, booking_date, booking_time, booking_code, counseling_type)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $simpan,
        "iissss",
        $patient_id,
        $doctor_id,
        $booking_date,
        $booking_time_db,
        $booking_code,
        $counseling_type
    );

    mysqli_stmt_execute($simpan);
    mysqli_stmt_close($simpan);

    mysqli_commit($conn);


    // Menampilkan pesan berhasil
    pesanBooking(
        'Booking Berhasil Diajukan!',
        'Kode booking Anda adalah ' . $booking_code . '. Status booking saat ini Menunggu.',
        true
    );


} catch (mysqli_sql_exception $e) {

    // Melepaskan kunci tabel jika masih terkunci
    try {
        mysqli_query($conn, "UNLOCK TABLES");
    } catch (Throwable $unlock_error) {
        // Tidak perlu tindakan tambahan
    }

    mysqli_rollback($conn);

    error_log("Kesalahan booking konseling: " . $e->getMessage());

    pesanBooking(
        'Booking Gagal',
        'Terjadi kesalahan saat menyimpan booking. Silakan coba kembali.'
    );
}
?>