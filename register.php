<?php
session_start();

// Jika sudah login, arahkan ke halaman pasien
if (isset($_SESSION['user_id'])) {
    header("Location: beranda.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrasi Pasien | rylonaCare+</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <section class="auth-page">

        <div class="auth-container">

            <div class="auth-header">

                <a href="index.php" class="logo">
                    rylona<span>Care+</span>
                </a>

                <h2>Buat Akun Pasien</h2>

                <p>
                    Daftarkan diri Anda untuk mengakses layanan
                    kesehatan rylonaCare+.
                </p>

            </div>


            <form action="proses/register-proses.php" method="POST">

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input
                        type="text"
                        name="name"
                        placeholder="Masukkan nama lengkap"
                        required
                    >
                </div>


                <div class="form-group">
                    <label>Nomor Induk Kependudukan (NIK)</label>
                    <input
                        type="text"
                        name="nik"
                        placeholder="Masukkan 16 digit NIK"
                        maxlength="16"
                        pattern="[0-9]{16}"
                        required
                    >
                </div>


                <div class="form-group">
                    <label>Email</label>
                    <input
                        type="email"
                        name="email"
                        placeholder="contoh@email.com"
                        required
                    >
                </div>


                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input
                        type="tel"
                        name="phone"
                        placeholder="Contoh: 081234567890"
                        required
                    >
                </div>


                <div class="form-row">

                    <div class="form-group">
                        <label>Jenis Kelamin</label>

                        <select name="gender" required>
                            <option value="">Pilih</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>


                    <div class="form-group">
                        <label>Tanggal Lahir</label>

                        <input
                            type="date"
                            name="birth_date"
                            max="<?php echo date('Y-m-d'); ?>"
                            required
                        >
                    </div>

                </div>


                <div class="form-group">
                    <label>Alamat Lengkap</label>

                    <textarea
                        name="address"
                        rows="3"
                        placeholder="Masukkan alamat lengkap"
                        required
                    ></textarea>
                </div>


                <div class="form-group">
                    <label>Password</label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Minimal 8 karakter"
                        minlength="8"
                        required
                    >
                </div>


                <div class="form-group">
                    <label>Konfirmasi Password</label>

                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Ulangi password"
                        minlength="8"
                        required
                    >
                </div>


                <button type="submit" name="register" class="btn-primary auth-submit">
                    Daftar Sekarang
                </button>


                <p class="auth-footer">
                    Sudah memiliki akun?
                    <a href="login.php">Masuk di sini</a>
                </p>

            </form>

        </div>

    </section>

</body>
</html>