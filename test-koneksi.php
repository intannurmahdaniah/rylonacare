<?php

require_once 'config/database.php';

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tes Koneksi - rylonaCare+</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        h2 {
            color: #168b85;
        }

        p {
            color: #555;
        }
    </style>
</head>

<body>

    <div class="container">

        <h2>rylonaCare+</h2>

        <h3>Koneksi Database Berhasil!</h3>

        <p>
            Website berhasil terhubung dengan database
            <strong>rylonacare_db</strong>.
        </p>

        <p style="color:green;">
            Status: CONNECTED
        </p>

    </div>

</body>
</html>