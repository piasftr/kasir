<?php include 'template/header.php'; ?>
<?php include 'config.php'; ?>

<?php
function format_ribuan($angka) {
    return number_format($angka, 0, ',', '.');
}

// SIMPAN KE KERANJANG
if (isset($_POST['save'])) {
    $idb = $_POST['kode_barang'];
    $nama = $_POST['nama_barang'];
    $harga = $_POST['harga_barang'];
    $qty = $_POST['quantity'];
    $total = $_POST['subtotal'];
    $jenis = $_POST['jenis'];
    $tgl = date("j F Y");

    $cek = mysqli_query($conn, "SELECT * FROM keranjang WHERE kode_barang='$idb' AND jenis='$jenis'");
    if (mysqli_num_rows($cek) > 0) {
        $data = mysqli_fetch_assoc($cek);
        $qty_baru = $data['quantity'] + $qty;
        $subtotal_baru = $qty_baru * $harga;
        mysqli_query($conn, "UPDATE keranjang SET quantity='$qty_baru', subtotal='$subtotal_baru' WHERE kode_barang='$idb' AND jenis='$jenis'");
    } else {
        mysqli_query($conn, "INSERT INTO keranjang (kode_barang, nama_barang, harga_barang, quantity, subtotal, jenis, tgl_input)
            VALUES('$idb','$nama','$harga','$qty','$total','$jenis','$tgl')");
    }

    echo '<script>window.location="index.php";</script>';
}

// HAPUS DARI KERANJANG
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM keranjang WHERE id_cart='$id'");
    echo '<script>window.location="index.php";</script>';
}
?>

<div class="col-md-9 mb-2">
    <div class="row">

        <!-- MENU -->
        <div class="col-md-7 mb-3">
            <?php
            $menu_khusus = ['Nasi Goreng', 'Nasi Mawut', 'Nasi Pecel', 'Nasi Campur'];
            $grouped = [];
            $makanan = mysqli_query($conn, "SELECT * FROM makanan ORDER BY nama ASC");
            $menu_makanan = [];

            while ($row = mysqli_fetch_assoc($makanan)) {
                $nama = $row['nama'];
                $is_grouped = false;
                foreach ($menu_khusus as $menu) {
                    if (stripos($nama, $menu) !== false) {
                        $grouped[$menu][] = $row;
                        $is_grouped = true;
                        break;
                    }
                }
                if (!$is_grouped) $menu_makanan[] = $row;
            }

            $minuman = mysqli_query($conn, "SELECT * FROM minuman ORDER BY nama ASC");
            $menu_minuman = mysqli_fetch_all($minuman, MYSQLI_ASSOC);
            ?>

            <div class="card">
                <div class="card-body py-4">
                    <h5><b>Menu Makanan</b></h5>
                    <div class="row">
                        <?php foreach ($grouped as $menu => $lauks): ?>
                        <div class="col-12 mb-3">
                            <button class="btn btn-light w-100 text-start" onclick="toggleLauk('<?= md5($menu) ?>')">
                                <b><?= $menu ?></b>
                            </button>
                            <div id="<?= md5($menu) ?>" class="mt-2" style="display:none;">
                                <div class="row">
                                    <?php foreach ($lauks as $item): ?>
                                    <div class="col-6 mb-2">
                                        <form method="POST">
                                            <input type="hidden" name="kode_barang" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="nama_barang" value="<?= $item['nama'] ?>">
                                            <input type="hidden" name="harga_barang" value="<?= $item['harga'] ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="subtotal" value="<?= $item['harga'] ?>">
                                            <input type="hidden" name="jenis" value="makanan">
                                            <button type="submit" name="save" class="btn btn-light w-100">
                                                <?= str_replace($menu . ' + ', '', $item['nama']) ?>
                                            </button>
                                        </form>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php foreach ($menu_makanan as $row): ?>
                        <div class="col-6 mb-3">
                            <form method="POST">
                                <input type="hidden" name="kode_barang" value="<?= $row['id'] ?>">
                                <input type="hidden" name="nama_barang" value="<?= $row['nama'] ?>">
                                <input type="hidden" name="harga_barang" value="<?= $row['harga'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="subtotal" value="<?= $row['harga'] ?>">
                                <input type="hidden" name="jenis" value="makanan">
                                <button type="submit" name="save" class="btn btn-light w-100">
                                    <b><?= $row['nama'] ?></b>
                                </button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <h5 class="mt-4"><b>Menu Minuman</b></h5>
                    <div class="row">
                        <?php foreach ($menu_minuman as $row): ?>
                        <div class="col-6 mb-3">
                            <form method="POST">
                                <input type="hidden" name="kode_barang" value="<?= $row['id'] ?>">
                                <input type="hidden" name="nama_barang" value="<?= $row['nama'] ?>">
                                <input type="hidden" name="harga_barang" value="<?= $row['harga'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="subtotal" value="<?= $row['harga'] ?>">
                                <input type="hidden" name="jenis" value="minuman">
                                <button type="submit" name="save" class="btn btn-light w-100">
                                    <b><?= $row['nama'] ?></b>
                                </button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- PEMBAYARAN -->
        <div class="col-md-5 mb-3">
            <?php include 'pembayaran.php'; ?>
        </div>

    </div>
</div>

<script>
function toggleLauk(id) {
    var el = document.getElementById(id);
    el.style.display = el.style.display === "none" ? "block" : "none";
}
</script>

<?php include 'template/footer.php'; ?>