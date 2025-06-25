<?php
session_start();
require 'config.php';

if (isset($_SESSION['status']) && $_SESSION['status'] == "login") {
    header('Location: index.php');
    exit;
}

if (isset($_POST['proses'])) {
    $user = mysqli_real_escape_string($conn, strip_tags($_POST['user']));
    $pass = mysqli_real_escape_string($conn, strip_tags($_POST['pass']));

    $query = mysqli_query($conn, "SELECT * FROM login WHERE user='$user' LIMIT 1");
    $data = mysqli_fetch_assoc($query);

    if ($data) {
        // Jika password disimpan dalam bentuk plain text
        if ($pass === $data['pass']) {
            $_SESSION['user'] = $user;
            $_SESSION['status'] = "login";
            echo '<script>alert("Login Sukses"); window.location="index.php";</script>';
        } else {
            echo '<script>alert("Password salah!"); history.back();</script>';
        }

        // Jika password disimpan sebagai hash (rekomendasi keamanan)
        // if (password_verify($pass, $data['pass'])) {
        //     $_SESSION['user'] = $user;
        //     $_SESSION['status'] = "login";
        //     echo '<script>alert("Login Sukses"); window.location="index.php";</script>';
        // } else {
        //     echo '<script>alert("Password salah!"); history.back();</script>';
        // }
    } else {
        echo '<script>alert("Username tidak ditemukan!"); history.back();</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" href="logo.ico">
    <title>Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-purple">
    <div class="container">
        <br><br><br><br><br><br>
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body">
                        <h1 class="h4 text-center mb-4"><b>Login Admin</b></h1>
                        <form method="POST">
                            <div class="form-group">
                                <input type="text" class="form-control form-control-user" name="user"
                                    placeholder="Username" required>
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control form-control-user" name="pass"
                                    placeholder="Password" required>
                            </div>
                            <button class="btn btn-purple form-control-user btn-block" name="proses"
                                type="submit">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery.slim.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>