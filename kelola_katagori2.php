<?php
include 'template/header.php'; 
include 'config.php';

// CREATE
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_kategori'];
    $deskripsi = $_POST['deskripsi'];
    mysqli_query($koneksi, "INSERT INTO kategori_pengeluaran (nama_kategori, deskripsi) VALUES ('$nama', '$deskripsi')");
    header("Location: kelola_kategori2.php");
}

// DELETE
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM kategori_pengeluaran WHERE id_kategori = $id");
    header("Location: kelola_kategori2.php");
}

// UPDATE
if (isset($_POST['edit'])) {
    $id = $_POST['id_kategori'];
    $nama = $_POST['nama_kategori'];
    $deskripsi = $_POST['deskripsi'];
    mysqli_query($koneksi, "UPDATE kategori_pengeluaran SET nama_kategori='$nama', deskripsi='$deskripsi' WHERE id_kategori=$id");
    header("Location: kelola_kategori2.php");
}

// GET data untuk form edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit_result = mysqli_query($koneksi, "SELECT * FROM kategori_pengeluaran WHERE id_kategori = $id");
    $edit_data = mysqli_fetch_assoc($edit_result);
}

// TAMPILKAN SEMUA KATEGORI
$data = mysqli_query($koneksi, "SELECT * FROM kategori_pengeluaran ORDER BY id_kategori ASC");
?>

<h2>Kategori Pengeluaran</h2>

<form method="POST">
    <input type="hidden" name="id_kategori" value="<?= $edit_data['id_kategori'] ?? '' ?>">
    <label>Nama Kategori:</label><br>
    <input type="text" name="nama_kategori" required value="<?= $edit_data['nama_kategori'] ?? '' ?>"><br>
    <label>Deskripsi:</label><br>
    <textarea name="deskripsi"><?= $edit_data['deskripsi'] ?? '' ?></textarea><br><br>

    <?php if ($edit_data): ?>
        <button type="submit" name="edit">Update</button>
        <a href="kategori.php">Batal</a>
    <?php else: ?>
        <button type="submit" name="tambah">Tambah</button>
    <?php endif; ?>
</form>

<hr>

<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>No.</th>
        <th>Nama</th>
        <th>Deskripsi</th>
        <th>Opsi</th>
    </tr>
    <?php
    $no = 1;
    while ($row = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $row['nama_kategori'] ?></td>
            <td><?= $row['deskripsi'] ?></td>
            <td>
                <a href="kelola_kategori2.php?edit=<?= $row['id_kategori'] ?>">Edit</a> | 
                <a href="kelola_kategori2.php?hapus=<?= $row['id_kategori'] ?>" onclick="return confirm('Yakin hapus?')">Hapus</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<?php include 'template/footer.php'; ?>
