<?php include 'template/header.php'; ?>
<?php include 'config.php'; ?>

<div class="col-md-9 mb-2">

    <!-- FORM TAMBAH MAKANAN -->
    <div class="card mb-3">
        <div class="card-body">
            <h5><b>Tambah Menu Makanan</b></h5>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" name="nama_makanan" class="form-control" placeholder="Nama Makanan" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <input type="number" name="harga_makanan" class="form-control" placeholder="Harga" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <select name="kategori_lauk" class="form-control">
                            <option value="">Tanpa Lauk</option>
                            <option value="Telur">Telur</option>
                            <option value="Ayam">Ayam</option>
                            <option value="Ayam Geprek">Ayam Geprek</option>
                        </select>
                    </div>
                    <div class="col-md-12 text-right">
                        <button type="submit" name="simpan_makanan" class="btn btn-primary">Simpan Makanan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- FORM TAMBAH MINUMAN -->
    <div class="card mb-3">
        <div class="card-body">
            <h5><b>Tambah Menu Minuman</b></h5>
            <form method="POST">
                <div class="row">
                    <div class="col-md-8 mb-2">
                        <input type="text" name="nama_minuman" class="form-control" placeholder="Nama Minuman" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <input type="number" name="harga_minuman" class="form-control" placeholder="Harga" required>
                    </div>
                    <div class="col-md-12 text-right">
                        <button type="submit" name="simpan_minuman" class="btn btn-success">Simpan Minuman</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DAFTAR MAKANAN -->
    <div class="card mb-3">
        <div class="card-body">
            <h5><b>Daftar Menu Makanan</b></h5>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light text-center">
                        <tr>
                            <th>Nama</th>
                            <th>Harga</th>
                            <th>Lauk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $makanan = mysqli_query($conn, "SELECT * FROM makanan ORDER BY id ASC");
                        while ($m = mysqli_fetch_assoc($makanan)): ?>
                        <tr>
                            <form method="POST">
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <input type="hidden" name="jenis" value="makanan">
                                <td><input type="text" name="nama" value="<?= $m['nama'] ?>" class="form-control"></td>
                                <td><input type="number" name="harga" value="<?= $m['harga'] ?>" class="form-control">
                                </td>
                                <td>
                                    <select name="kategori_lauk" class="form-control">
                                        <option value="">Tanpa Lauk</option>
                                        <option value="Telur" <?= $m['kategori_lauk']=='Telur' ? 'selected' : '' ?>>
                                            Telur</option>
                                        <option value="Ayam" <?= $m['kategori_lauk']=='Ayam' ? 'selected' : '' ?>>Ayam
                                        </option>
                                        <option value="Ayam Geprek"
                                            <?= $m['kategori_lauk']=='Ayam Geprek' ? 'selected' : '' ?>>Ayam Geprek
                                        </option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <button type="submit" name="update" class="btn btn-success btn-sm">Simpan</button>
                                    <button type="submit" name="hapus" onclick="return confirm('Yakin hapus?')"
                                        class="btn btn-danger btn-sm">Hapus</button>
                                </td>
                            </form>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- DAFTAR MINUMAN -->
    <div class="card mb-3">
        <div class="card-body">
            <h5><b>Daftar Menu Minuman</b></h5>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light text-center">
                        <tr>
                            <th>Nama</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $minuman = mysqli_query($conn, "SELECT * FROM minuman ORDER BY id ASC");
                        while ($m = mysqli_fetch_assoc($minuman)): ?>
                        <tr>
                            <form method="POST">
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <input type="hidden" name="jenis" value="minuman">
                                <td><input type="text" name="nama" value="<?= $m['nama'] ?>" class="form-control"></td>
                                <td><input type="number" name="harga" value="<?= $m['harga'] ?>" class="form-control">
                                </td>
                                <td class="text-center">
                                    <button type="submit" name="update" class="btn btn-success btn-sm">Simpan</button>
                                    <button type="submit" name="hapus" onclick="return confirm('Yakin hapus?')"
                                        class="btn btn-danger btn-sm">Hapus</button>
                                </td>
                            </form>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php
// Simpan makanan
if (isset($_POST['simpan_makanan'])) {
    $nama = $_POST['nama_makanan'];
    $harga = $_POST['harga_makanan'];
    $lauk = $_POST['kategori_lauk'] ?: NULL;

    mysqli_query($conn, "INSERT INTO makanan (nama, harga, kategori_lauk) VALUES ('$nama', '$harga', " . ($lauk ? "'$lauk'" : "NULL") . ")");
    echo "<script>alert('Makanan berhasil ditambahkan'); location='menu_input.php';</script>";
}

// Simpan minuman
if (isset($_POST['simpan_minuman'])) {
    $nama = $_POST['nama_minuman'];
    $harga = $_POST['harga_minuman'];

    mysqli_query($conn, "INSERT INTO minuman (nama, harga) VALUES ('$nama', '$harga')");
    echo "<script>alert('Minuman berhasil ditambahkan'); location='menu_input.php';</script>";
}

// Update atau hapus
if (isset($_POST['update']) || isset($_POST['hapus'])) {
    $id = $_POST['id'];
    $jenis = $_POST['jenis'];

    if (isset($_POST['hapus'])) {
        mysqli_query($conn, "DELETE FROM $jenis WHERE id=$id");
    } else {
        $nama = $_POST['nama'];
        $harga = $_POST['harga'];
        $lauk = $_POST['kategori_lauk'] ?? null;

        if ($jenis == 'makanan') {
            $sql = "UPDATE makanan SET nama='$nama', harga='$harga', kategori_lauk=" . ($lauk ? "'$lauk'" : "NULL") . " WHERE id=$id";
        } else {
            $sql = "UPDATE minuman SET nama='$nama', harga='$harga' WHERE id=$id";
        }

        mysqli_query($conn, $sql);
    }

    echo "<script>location='menu_input.php';</script>";
}
?>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<?php include 'template/footer.php'; ?>