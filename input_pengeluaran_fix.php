
<?php
include 'koneksi.php';
#7952b3

<head>
  ...
  <style>
    .sidebar {
      background: red;
    }
    .sidebar i {
      color: green;
    }
     </style>
</head>
 
input insert atau simpan pengeluaran
 $query = mysqli_query($koneksi, "
        INSERT INTO pengeluaran (id_kategori, deskripsi, jumlah, tanggal, waktu)
        VALUES ('$id_kategori', '$deskripsi', '$jumlah', '$tanggal', '$waktu')
    ");


if (isset($_POST['simpan_pengeluaran'])) {
    $id_kategori = $_POST['id_kategori'];
    $deskripsi = $_POST['deskripsi'];
    $jumlah = str_replace('.', '', $_POST['jumlah']); // Remove thousand separator
    $tanggal = $_POST['tanggal'];
    $waktu = date('H:i:s');

    if (empty($id_kategori)) {
        echo "<script>alert('Kategori harus dipilih!'); window.history.back();</script>";
        exit;
    }
    
    $query = "INSERT INTO pengeluaran (id_kategori, deskripsi, jumlah, tanggal, waktu) 
              VALUES ('$id_kategori', '$deskripsi', '$jumlah', '$tanggal', '$waktu')";
    
    if (mysqli_query($conn, $query)) {
        echo '<script>
                alert("Pengeluaran berhasil disimpan!");
                window.location="input_pengeluaran.php";
              </script>';
    } else {
        echo '<script>alert("Error: ' . mysqli_error($conn) . '");</script>';
    }
}

// Proses simpan pengeluaran
if (isset($_POST['simpan_pengeluaran'])) {
    $id_kategori = $_POST['id_kategori'];
    $deskripsi = $_POST['deskripsi'];
    $jumlah = $_POST['jumlah'];
    $tanggal = $_POST['tanggal'];
    $waktu = $_POST['waktu'];

    // Insert dengan id_kategori yang valid
    $query = mysqli_query($koneksi, "
        INSERT INTO pengeluaran (id_kategori, deskripsi, jumlah, tanggal, waktu)
        VALUES ('$id_kategori', '$deskripsi', '$jumlah', '$tanggal', '$waktu')
    ");

    if ($query) {
        echo "<script>alert('Pengeluaran berhasil disimpan!'); window.location='input_pengeluaran.php';</script>";
    } else {
        echo "<script>alert('Gagal disimpan: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Input Pengeluaran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">

<div class="row">
    <!-- Sidebar Contoh (bisa disesuaikan) -->
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-body">
                <h5>Hallo, Admin</h5>
                <!-- Tambahkan menu navigasi jika ada -->
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-9">
        <div class="card mb-4">
            <div class="card-header">Input Pengeluaran</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="id_kategori" class="form-label">Kategori</label>
                        <select name="id_kategori" id="id_kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php
                            $kategori = mysqli_query($koneksi, "SELECT * FROM kategori_pengeluaran");
                            while ($row = mysqli_fetch_array($kategori)) {
                                echo "<option value='$row[id_kategori]'>$row[nama_kategori]</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <input type="text" name="deskripsi" id="deskripsi" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="jumlah" class="form-label">Jumlah (Rp)</label>
                        <input type="number" name="jumlah" id="jumlah" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="waktu" class="form-label">Waktu</label>
                        <input type="time" name="waktu" id="waktu" class="form-control" required value="<?php echo date('H:i'); ?>">
                    </div>

                    <button type="submit" name="simpan_pengeluaran" class="btn btn-primary">Simpan Pengeluaran</button>
                </form>
            </div>
        </div>

        <!-- Pengeluaran Hari Ini -->
        <div class="card">
            <div class="card-header">Pengeluaran Hari Ini</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kategori</th>
                            <th>Deskripsi</th>
                            <th>Jumlah</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $today = date('Y-m-d');
                        $query = mysqli_query($koneksi, "
                            SELECT p.*, k.nama_kategori 
                            FROM pengeluaran p 
                            JOIN kategori_pengeluaran k ON p.id_kategori = k.id_kategori 
                            WHERE p.tanggal = '$today'
                            ORDER BY p.waktu DESC
                        ");
                        $no = 1;
                        $total = 0;
                        while ($row = mysqli_fetch_array($query)) {
                            echo "<tr>
                                <td>$no</td>
                                <td>$row[nama_kategori]</td>
                                <td>$row[deskripsi]</td>
                                <td>Rp " . number_format($row['jumlah'],0,",",".") . "</td>
                                <td>$row[tanggal]</td>
                                <td>$row[waktu]</td>
                            </tr>";
                            $total += $row['jumlah'];
                            $no++;
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Total</th>
                            <th colspan="3">Rp <?php echo number_format($total,0,",","."); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</div>

</body>
</html>
