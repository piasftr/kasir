<?php
$nota = "AD" . date("dmYHis");
$data_toko = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM login LIMIT 1"));
$user = $data_toko['user'];
$nama_toko = $data_toko['nama_toko'];
$alamat = $data_toko['alamat'];
$telp = $data_toko['telp'];
?>

<div class="card" id="print">
    <div class="card-header bg-white border-0 pb-0 pt-4">
        <h5 class="card-title mb-0 text-center"><b><?= $nama_toko ?></b></h5>
        <p class="m-0 small text-center"><?= $alamat ?></p>
        <p class="small text-center">Telp. <?= $telp ?></p>
        <div class="row small">
            <div class="col-7">
                <ul class="pl-0" style="list-style: none;">
                    <li>NOTA : <?= $nota ?></li>
                    <li>KASIR : <?= $user ?></li>
                </ul>
            </div>
            <div class="col-5">
                <ul class="pl-0" style="list-style: none;">
                    <li>TGL : <?= date("d-m-Y") ?></li>
                    <li>JAM : <?= date("H:i:s") ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card-body small pt-0">
        <hr class="mt-0">
        <div class="row font-weight-bold text-center">
            <div class="col-4">Nama</div>
            <div class="col-2">Qty</div>
            <div class="col-3">Harga</div>
            <div class="col-3">Subtotal</div>
        </div>
        <div class="row">
            <?php 
            $data_barang = mysqli_query($conn,"SELECT * FROM keranjang");
            $total = 0;
            while($d = mysqli_fetch_array($data_barang)){ 
                $total += $d['subtotal']; ?>
            <div class="col-4"><?= $d['nama_barang'] ?></div>
            <div class="col-2 text-center"><?= $d['quantity'] ?></div>
            <div class="col-3 text-right"><?= format_ribuan($d['harga_barang']) ?></div>
            <div class="col-3 text-right"><?= format_ribuan($d['subtotal']) ?>
                <a href="?hapus=<?= $d['id_cart'] ?>" class="text-danger ml-2"
                    onclick="return confirm('Hapus item ini?')"><i class="fa fa-times"></i></a>
            </div>
            <?php } ?>
        </div>
        <hr class="mt-2">

        <!-- FORM BAYAR -->
        <form method="POST">
            <input type="hidden" name="total" value="<?= $total ?>">
            <ul class="list-group list-group-flush border-0">
                <li class="list-group-item d-flex justify-content-between px-0">
                    <b>Total</b><span><b>Rp <?= format_ribuan($total) ?></b></span>
                </li>
                <li class="list-group-item px-0">
                    <label><b>Bayar</b></label>
                    <input type="number" class="form-control mb-2" name="bayar" id="bayarnya" onchange="totalnya()"
                        required>
                    <div class="btn-group w-100 mt-2 d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-light flex-fill" onclick="setBayar(<?= $total ?>)">Uang
                            Pas</button>
                        <button type="button" class="btn btn-light flex-fill"
                            onclick="setBayar(20000)">Rp20.000</button>
                        <button type="button" class="btn btn-light flex-fill"
                            onclick="setBayar(50000)">Rp50.000</button>
                        <button type="button" class="btn btn-light flex-fill"
                            onclick="setBayar(100000)">Rp100.000</button>
                    </div>
                </li>
                <li class="list-group-item px-0">
                    <label><b>Kembalian</b></label>
                    <input type="number" class="form-control" name="kembalian" id="total1" readonly>
                </li>
            </ul>
            <div class="text-center mt-3">
                <button class="btn btn-success btn-sm w-100" name="bayar_selesai" type="submit">
                    <i class="fa fa-check mr-1"></i> Selesaikan Pembayaran
                </button>
            </div>
        </form>

        <?php
        if(isset($_POST['bayar_selesai'])){
            $bayar = $_POST['bayar'];
            $kembali = $_POST['kembalian'];
            mysqli_query($conn,"UPDATE keranjang SET no_transaksi='$nota', bayar='$bayar', kembalian='$kembali'");
            mysqli_query($conn,"INSERT INTO laporanku (no_transaksi,bayar,kembalian,id_cart,kode_barang,nama_barang,harga_barang,quantity,subtotal,jenis,tgl_input)
                SELECT no_transaksi,bayar,kembalian,id_cart,kode_barang,nama_barang,harga_barang,quantity,subtotal,jenis,tgl_input FROM keranjang");
            mysqli_query($conn,"DELETE FROM keranjang");
            echo '<script>printContent("print");</script>';
            echo '<script>window.location="index.php";</script>';
        }
        ?>
    </div>
</div>

<script>
function totalnya() {
    let total = parseInt(document.querySelector('input[name="total"]').value) || 0;
    let bayar = parseInt(document.getElementById('bayarnya').value) || 0;
    document.getElementById('total1').value = bayar - total;
}

function setBayar(nominal) {
    document.getElementById('bayarnya').value = nominal;
    totalnya();
}

function printContent(el) {
    var restorepage = document.body.innerHTML;
    var printcontent = document.getElementById(el).innerHTML;
    document.body.innerHTML = printcontent;
    window.print();
    document.body.innerHTML = restorepage;
}
</script>