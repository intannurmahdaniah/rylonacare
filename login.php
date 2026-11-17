<?php
session_start();

// Jika sudah login, arahkan ke beranda pasien
if (isset($_SESSION['user_id'])) {
    header("Location: beranda.php");
    exit;
}

$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Pasien | rylonaCare+</title>

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

                <h2>Selamat Datang Kembali</h2>

                <p>
                    Masuk ke akun Anda untuk mengakses layanan
                    kesehatan rylonaCare+.
                </p>

            </div>

            <?php if ($error === 'kosong'): ?>
                <div class="auth-alert">
                    Email dan password wajib diisi.
                </div>
            <?php elseif ($error === 'tidak_valid'): ?>
                <div class="auth-alert">
                    Email atau password yang Anda masukkan salah.
                </div>
            <?php elseif ($error === 'gagal'): ?>
                <div class="auth-alert">
                    Terjadi kesalahan. Silakan coba kembali.
                </div>
            <?php endif; ?>

            <form action="proses/login-proses.php" method="POST">

                <div class="form-group">
                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        placeholder="Masukkan email terdaftar"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Password</label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn-primary auth-submit">
                    Masuk
                </button>

                <p class="auth-footer">
                    Belum memiliki akun?
                    <a href="register.php">Daftar sekarang</a>
                </p>

                <p class="auth-footer">
                    <a href="index.php">← Kembali ke Beranda</a>
                </p>

            </form>

        </div>

    </section>

</body>
</html>