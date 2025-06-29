<?php
// Pastikan tidak ada spasi atau karakter di sini sebelum tag <?php
// Ini adalah baris paling atas dari file header.php

include "config.php"; // Pastikan config.php hanya berisi koneksi DB dan tidak ada output
if (session_status() == PHP_SESSION_NONE) { // Check if no session is active
    session_start();
}


// Logika redirect harus berada di bagian paling atas, sebelum HTML apapun
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:login.php");
    exit(); // Selalu tambahkan exit() setelah header redirects
}

// Gunakan nama toko dari sesi jika sudah tersedia.
// Ini lebih efisien daripada query database berulang kali.
$displayed_nama_toko = $_SESSION['nama_toko'] ?? 'Nama Toko Default'; // Berikan nilai default jika tidak ada

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Kasir</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="logo3.ico">
    <link href="assets/fontawesome/css/all.min.css" rel="stylesheet" type="text/css">

    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="path/to/your/custom.css">

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
    <script src="path/to/your/custom.js"></script>
    <style>
    .btn-group-xs>.btn,
    .btn-xs {
        padding: .25rem .4rem;
        font-size: .875rem;
        line-height: .5;
        border-radius: .2rem;
    }

    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 6px 20px rgb(17 26 104 / 10%);
    }

    .card-header {
        border-radius: 15px 15px 0 0 !important;
    }

    .form-control,
    .btn {
        border-radius: 15px;
    }

    button.buttons-html5 {
        padding: .25rem .4rem !important;
        font-size: .875rem !important;
        line-height: .5 !important;
    }
    .head{
        text-decoration: none;
        color: white;
    }
    .navbar-brand,
    .navbar-brand:hover,
    .navbar-brand:focus,
    .navbar-brand:active {
        color: white !important; /* Forces the color to white in all states */
        text-decoration: none;   /* Removes the underline on hover if it appears */
    }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-purple text-white shadow-sm sticky-top d-md-none">
        <a class="navbar-brand" href="#"><i class="fa fa-shopping-cart mr-1"></i><b>
                <?php echo htmlspecialchars($displayed_nama_toko); ?></b></a>
        <button class="navbar-toggler border-0" type="button" data-toggle="collapse"
            data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
            aria-label="Toggle navigation">
            <i class="fa fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent" > 
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link putih" href="index.php"><i
                                class="fa fa-desktop mr-2"></i>Kasir</a></li>
                <?php if ($_SESSION['role'] === 'owner'): ?>          
                <li class="nav-item"><a class="nav-link putih" href="menu_input.php"><i
                                class="fa fa-shopping-bag mr-2"></i>Menu</a></li>
                <li class="nav-item"><a class="nav-link putih" href="input_pengeluaran.php"><i
                                class="fa fa-cog mr-2"></i>Pengeluaran</a></li>
                <li class="nav-item"><a class="nav-link putih" href="kelola_katagori.php"><i
                                class="fa fa-table mr-2"></i>Katagori</a></li>
                <li class="nav-item"><a class="nav-link putih" href="laporan_keuangan.php"><i
                                class="fa fa-table mr-2"></i>Laporan</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link putih" href="pengaturan.php"><i
                                class="fa fa-cog mr-2"></i>Pengaturan</a></li>
                <li class="nav-item"><a class="nav-link putih" href="logout.php"
                                onclick="return confirm('Anda yakin ingin keluar ?');"><i
                                class="fa fa-power-off mr-2"></i>Keluar</a></li>
            </ul>
        </div>
    </nav>

    <div class="bg-danger text-center py-2 shadow-sm sticky-top d-none d-md-block">
        <a class="navbar-brand head" href="#">
    <i class="fa fa-shopping-cart mr-1"></i><b>
        <?php echo htmlspecialchars($displayed_nama_toko); ?>
    </b>
</a>
    </div>
    <br>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 mb-2 d-none d-md-block">
                <div class="card mt-5" style="height: 100vh; position: fixed; left: 0; top: 0; width: 250px; overflow-y: auto;">
                    <div class="card-header bg-danger">
                        <div class="card-title text-white">Hallo, <b><?php echo htmlspecialchars($_SESSION['user']); ?>!</b></div>
                    </div>
                    <div class="card-body">
                        <ul class="navbar-nav">
                            <li class="nav-item"><a class="nav-link" href="index.php"><i
                                                class="fa fa-desktop text-success mr-2"></i>Kasir</a></li>

                            <?php if ($_SESSION['role'] === 'owner'): ?>  
                            <li class="nav-item"><a class="nav-link" href="menu_input.php"><i
                                                class="fa fa-shopping-bag text-success mr-2"></i>Menu</a></li>
                            <li class="nav-item"><a class="nav-link" href="input_pengeluaran.php"><i
                                                class="fas fa-search-dollar text-success mr-2"></i>Pengeluaran</a></li>
                             <li class="nav-item"><a class="nav-link" href="kelola_katagori.php"><i
                                                class="fas fa-bullseye text-success mr-2"></i>Katagori</a></li>
                            <li class="nav-item"><a class="nav-link" href="laporan_keuangan.php"><i
                                                class="fa fa-table text-success mr-2"></i>Laporan</a></li>
                             <?php endif; ?>
                            <li class="nav-item"><a class="nav-link" href="pengaturan.php"><i
                                                class="fa fa-cog text-success mr-2"></i>Pengaturan</a></li>
                            <li class="nav-item"><a class="nav-link" href="logout.php"
                                                onclick="return confirm('Anda yakin ingin keluar ?');"><i
                                                class="fa fa-power-off text-success mr-2"></i>Keluar</a></li>
                        </ul>
                    </div>
                </div>
            </div>