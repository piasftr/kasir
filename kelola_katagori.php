<?php include 'template/header.php'; ?>
<?php include 'config.php'; ?>

<?php
// TAMBAH KATEGORI
if (isset($_POST['tambah_kategori'])) {
    $nama_kategori = $_POST['nama_kategori'];
    $deskripsi = $_POST['deskripsi'];
    
    $cek = mysqli_query($conn, "SELECT * FROM kategori_pengeluaran WHERE nama_kategori='$nama_kategori'");
    if (mysqli_num_rows($cek) > 0) {
        echo '<script>alert("Kategori sudah ada!");</script>';
    } else {
        $query = "INSERT INTO kategori_pengeluaran (nama_kategori, deskripsi) VALUES ('$nama_kategori', '$deskripsi')";
        if (mysqli_query($conn, $query)) {
            echo '<script>alert("Kategori berhasil ditambahkan!"); window.location="kelola_kategori.php";</script>';
        }
    }
}

// HAPUS KATEGORI
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Cek apakah kategori masih digunakan
    $cek_penggunaan = mysqli_query($conn, "SELECT * FROM pengeluaran WHERE id_kategori=(SELECT nama_kategori FROM kategori_pengeluaran WHERE id_kategori='$id')");
    
    if (mysqli_num_rows($cek_penggunaan) > 0) {
        echo '<script>alert("Kategori tidak dapat dihapus karena masih digunakan dalam transaksi!");</script>';
    } else {
        mysqli_query($conn, "DELETE FROM kategori_pengeluaran WHERE id_kategori='$id'");
        echo '<script>alert("Kategori berhasil dihapus!"); window.location="kelola_katagori.php";</script>';
    }
}

// EDIT KATEGORI
if (isset($_POST['edit_kategori'])) {
    $id_kategori = $_POST['id_kategori'];
    $nama_kategori = $_POST['nama_kategori'];
    $deskripsi = $_POST['deskripsi'];
    
    $query = "UPDATE kategori_pengeluaran SET nama_kategori='$nama_kategori', deskripsi='$deskripsi' WHERE id_kategori='$id_kategori'";
    if (mysqli_query($conn, $query)) {
        echo '<script>alert("Kategori berhasil diupdate!"); window.location="kelola_kategori.php";</script>';
    }
}

// Ambil data kategori
$kategori_query = mysqli_query($conn, "SELECT * FROM kategori_pengeluaran ORDER BY nama_kategori ASC");
?>

<div class="col-md-9 mb-2">
    <div class="row">
        
        <!-- FORM TAMBAH KATEGORI -->
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5><b>Tambah Kategori Baru</b></h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori</label>
                            <input type="text" name="nama_kategori" class="form-control" placeholder="Nama kategori..." required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi kategori..."></textarea>
                        </div>
                        
                        <button type="submit" name="tambah_kategori" class="btn btn-primary w-100">
                            <i class="fa fa-plus"></i> Tambah Kategori
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- DAFTAR KATEGORI -->
        <div class="col-md-8 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5><b>Daftar Kategori Pengeluaran</b></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kategori</th>
                                    <th>Deskripsi</th>
                                    <th>Penggunaan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while($row = mysqli_fetch_assoc($kategori_query)): 
                                    // Hitung penggunaan kategori
                                    $usage_query = mysqli_query($conn, "SELECT COUNT(*) as total, SUM(jumlah) as total_amount FROM pengeluaran WHERE id_kategori='".$row['id_kategori']."'");
                                    $usage = mysqli_fetch_assoc($usage_query);
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= $row['nama_kategori'] ?></strong></td>
                                    <td><?= $row['deskripsi'] ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?= $usage['total'] ?> transaksi<br>
                                            <?php if($usage['total_amount']): ?>
                                                Total: Rp <?= number_format($usage['total_amount'], 0, ',', '.') ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editKategori(<?= $row['id_kategori'] ?>, '<?= $row['nama_kategori'] ?>', '<?= $row['deskripsi'] ?>')">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        
                                        <?php if($usage['total'] == 0): ?>
                                        <a href="?hapus=<?= $row['id_kategori'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Yakin hapus kategori ini?')">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                        <?php else: ?>
                                        <span class="btn btn-sm btn-secondary disabled">
                                            <i class="fa fa-lock"></i> Terpakai
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- STATISTIK PENGGUNAAN KATEGORI -->
    <div class="card">
        <div class="card-header">
            <h5><b>Statistik Penggunaan Kategori</b></h5>
        </div>
        <div class="card-body">
            <?php
            $stats_query = mysqli_query($conn, "SELECT 
                                                   id_kategori,
                                                   COUNT(*) as jumlah_transaksi,
                                                   SUM(jumlah) as total_pengeluaran,
                                                   AVG(jumlah) as rata_rata
                                                FROM pengeluaran 
                                                GROUP BY id_kategori
                                                ORDER BY total_pengeluaran DESC");
            
            $total_all = mysqli_query($conn, "SELECT SUM(jumlah) as grand_total FROM pengeluaran");
            $grand_total = mysqli_fetch_assoc($total_all)['grand_total'] ?? 1;
            ?>
            
            <div class="row">
                <?php while($stat = mysqli_fetch_assoc($stats_query)): 
                    $persentase = ($stat['total_pengeluaran'] / $grand_total) * 100;
                ?>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6><?= $stat['id_kategori'] ?></h6>
                                    <small class="text-muted"><?= $stat['jumlah_transaksi'] ?> transaksi</small>
                                </div>
                                <div class="text-end">
                                    <strong class="text-danger">Rp <?= number_format($stat['total_pengeluaran'], 0, ',', '.') ?></strong>
                                    <br>
                                    <small class="text-muted">Avg: Rp <?= number_format($stat['rata_rata'], 0, ',', '.') ?></small>
                                </div>
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-danger" style="width: <?= $persentase ?>%">
                                    <?= number_format($persentase, 1) ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <!-- QUICK ACCESS -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="input_pengeluaran.php" class="btn btn-primary w-100">
                                <i class="fa fa-plus"></i> Input Pengeluaran
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="laporan_pengeluaran.php" class="btn btn-info w-100">
                                <i class="fa fa-chart-line"></i> Laporan Pengeluaran
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="analisis_keuangan.php" class="btn btn-success w-100">
                                <i class="fa fa-analytics"></i> Analisis Keuangan
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="index.php" class="btn btn-warning w-100">
                                <i class="fa fa-shopping-cart"></i> Kembali ke Kasir
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT KATEGORI -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_kategori" id="edit_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="nama_kategori" id="edit_nama" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_kategori" class="btn btn-primary">Update Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editKategori(id, nama, deskripsi) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_deskripsi').value = deskripsi;
    
    var modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}
</script>

<?php include 'template/footer.php'; ?>