<?php
include 'koneksi.php';

if (isset($_POST['simpan_pengeluaran'])) {
    $id_kategori = $_POST['id_kategori'];
    $deskripsi = $_POST['deskripsi'];
    $jumlah = $_POST['jumlah'];
    $tanggal = $_POST['tanggal'];
    $waktu = date('H:i:s');

    // Insert dengan id_kategori yang valid
    mysqli_query($conn, "
        INSERT INTO pengeluaran (id_kategori, deskripsi, jumlah, tanggal, waktu)
        VALUES ('$id_kategori', '$deskripsi', '$jumlah', '$tanggal', '$waktu')
    ");

    if ($query) {
        echo "<script>alert('Pengeluaran berhasil disimpan!'); window.location='input_pengeluaran.php';</script>";
    } else {
        echo "<script>alert('Gagal disimpan: " . mysqli_error($koneksi) . "');</script>";
    }
} yg atas yg awal atau aslinya input pengeluaran

if (isset($_POST['simpan_pengeluaran'])) {
  $id_kategori = $_POST['id_kategori'] ?? null;
  $deskripsi = $_POST['deskripsi'] ?? '';
  $jumlah = $_POST['jumlah'] ?? 0;
  $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
  $waktu = $_POST['waktu'] ?? date('H:i:s');

  if (empty($id_kategori)) {
    echo "<script>alert('Pilih kategori dulu!'); window.history.back();</script>";
    exit;
  }

  $query = mysqli_query($koneksi, "
    INSERT INTO pengeluaran (id_kategori, deskripsi, jumlah, tanggal, waktu)
    VALUES ('$id_kategori', '$deskripsi', '$jumlah', '$tanggal', '$waktu')
  ");

  if ($query) {
    echo "<script>alert('Pengeluaran berhasil disimpan!'); window.location='input_pengeluaran.php';</script>";
  } else {
    echo "<script>alert('Gagal: " . mysqli_error($koneksi) . "');</script>";
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

<div class="card mb-4">
  <div class="card-header bg-success text-white">
    Input Pengeluaran
  </div>
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
        <label for="jumlah" class="form-label">Jumlah</label>
        <input type="number" name="jumlah" id="jumlah" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="tanggal" class="form-label">Tanggal</label>
        <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
      </div>

      <div class="mb-3">
        <label for="waktu" class="form-label">Waktu</label>
        <input type="time" name="waktu" id="waktu" class="form-control" value="<?php echo date('H:i'); ?>" required>
      </div>

      <button type="submit" name="simpan_pengeluaran" class="btn btn-success">Simpan Pengeluaran</button>
    </form>
  </div>
</div>

</body>
</html>
