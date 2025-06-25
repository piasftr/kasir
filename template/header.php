<?php
include "config.php";
session_start();
if ($_SESSION['status'] != "login") {
    header("location:login.php");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Kasir</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="logo3.ico">
    <link href="assets/fontawesome/css/all.min.css" rel="stylesheet" type="text/css">

    <!-- Fonts & Bootstrap -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />

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
    </style>
</head>

<body>

    <!-- NAVBAR MOBILE -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-purple text-white shadow-sm sticky-top d-md-none">
        <a class="navbar-brand" href="#"><i class="fa fa-shopping-cart mr-1"></i><b>
                <?php 
            $toko = mysqli_query($conn,"SELECT * FROM login ORDER BY nama_toko ASC");
            while($dat = mysqli_fetch_array($toko)){
                $nama_toko = $dat['nama_toko'];
                echo $nama_toko;
            }
        ?></b></a>
        <button class="navbar-toggler border-0" type="button" data-toggle="collapse"
            data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
            aria-label="Toggle navigation">
            <i class="fa fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link putih" href="index.php"><i
                            class="fa fa-desktop mr-2"></i>Kasir</a></li>
                <li class="nav-item"><a class="nav-link putih" href="menu_input.php"><i
                            class="fa fa-shopping-bag mr-2"></i>Menu</a></li>
                <li class="nav-item"><a class="nav-link putih" href="input_pengeluaran.php"><i
                            class="fa fa-cog mr-2"></i>Pengeluaran</a></li>
                i class="nav-item"><a class="nav-link putih" href="kelola_katagori.php"><i
                            class="fa fa-table mr-2"></i>Katagori</a></li>
                <li class="nav-item"><a class="nav-link putih" href="laporan.php"><i
                            class="fa fa-table mr-2"></i>Laporan</a></li>
                <li class="nav-item"><a class="nav-link putih" href="pengaturan.php"><i
                            class="fa fa-cog mr-2"></i>Pengaturan</a></li>
                <li class="nav-item"><a class="nav-link putih" href="logout.php"
                        onclick="return confirm('Anda yakin ingin keluar ?');"><i
                            class="fa fa-power-off mr-2"></i>Keluar</a></li>
            </ul>
        </div>
    </nav>

    <!-- HEADER DESKTOP -->
    <div class="bg-danger text-center py-2 shadow-sm sticky-top d-none d-md-block">
        <a class="navbar-brand text-white" href="#"><i class="fa fa-shopping-cart mr-1"></i><b>
                <?php 
            $toko = mysqli_query($conn,"SELECT * FROM login ORDER BY nama_toko ASC");
            while($dat = mysqli_fetch_array($toko)){
                $nama_toko = $dat['nama_toko'];
                $userr = $dat['user'];
                echo $nama_toko;
            }
        ?></b></a>
    </div>
    <br>

    <!-- SIDEBAR DESKTOP -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 mb-2 d-none d-md-block">
                <div class="card">
                    <div class="card-header bg-danger">
                        <div class="card-title text-white">Hallo, <b><?php echo $userr ?></b></div>
                    </div>
                    <div class="card-body">
                        <ul class="navbar-nav">
                            <li class="nav-item"><a class="nav-link" href="index.php"><i
                                        class="fa fa-desktop text-success mr-2"></i>Kasir</a></li>
                            <li class="nav-item"><a class="nav-link" href="menu_input.php"><i
                                        class="fa fa-shopping-bag text-purple mr-2"></i>Menu</a></li>
                            <li class="nav-item"><a class="nav-link" href="input_pengeluaran.php"><i
                                        class="fas fa-search-dollar text-purple mr-2"></i>Pengeluaran</a></li>
                             <li class="nav-item"><a class="nav-link" href="kelola_katagori.php"><i
                                        class="fas fa-bullseye text-purple mr-2"></i>Katagori</a></li>
                            <li class="nav-item"><a class="nav-link" href="laporan.php"><i
                                        class="fa fa-table text-purple mr-2"></i>Laporan</a></li>
                            <li class="nav-item"><a class="nav-link" href="pengaturan.php"><i
                                        class="fa fa-cog text-purple mr-2"></i>Pengaturan</a></li>
                            <li class="nav-item"><a class="nav-link" href="logout.php"
                                    onclick="return confirm('Anda yakin ingin keluar ?');"><i
                                        class="fa fa-power-off text-purple mr-2"></i>Keluar</a></li>
                        </ul>
                    </div>
                </div>
            </div>