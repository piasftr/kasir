<?php include 'template/header.php'; ?>
<?php include 'config.php'; ?>

<?php
function format_ribuan($angka) {
    return number_format($angka, 0, ',', '.');
}

// Default filter
$tanggal_dari = isset($_GET['dari']) ? $_GET['dari'] : date('Y-m-01'); // Awal bulan
$tanggal_sampai = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-d'); // Hari ini
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Query data pengeluaran dengan filter
$where_clause = "WHERE tanggal BETWEEN '$tanggal_dari' AND '$tanggal_sampai'";
if (!empty($kategori_filter)) {
    $where_clause .= " AND kategori_pengeluaran = '$kategori_filter'";
}

$query_pengeluaran = "SELECT * FROM pengeluaran $where_clause ORDER BY tanggal DESC, waktu DESC";
$result_pengeluaran = mysqli_query($conn, $query_pengeluaran);

// Query untuk analisis per kategori
$query_kategori = "SELECT kategori_pengeluaran, 
                          COUNT(*) as jumlah_transaksi, 
                          SUM(jumlah) as total_pengeluaran,
                          AVG(jumlah) as rata_rata
                   FROM pengeluaran $where_clause 
                   GROUP BY kategori_pengeluaran 
                   ORDER BY total_pengeluaran DESC";
$result_kategori = mysqli_query($conn, $query_kategori);

// Query untuk analisis per hari
$query_harian = "SELECT tanggal, 
                        COUNT(*) as jumlah_transaksi, 
                        SUM(jumlah) as total_pengeluaran
                 FROM pengeluaran $where_clause 
                 GROUP BY tanggal 
                 ORDER BY tanggal DESC";
$result_harian = mysqli_query($conn, $query_harian);

// Hitung total keseluruhan
$query_total = "SELECT SUM(jumlah) as grand_total FROM pengeluaran $where_clause";
$result_total = mysqli_query($conn, $query_total);
$grand_total = mysqli_fetch_assoc($result_total)['grand_total'] ?? 0;

// Ambil kategori untuk filter
$kategori_query = mysqli_query($conn, "SELECT DISTINCT kategori_pengeluaran FROM pengeluaran ORDER BY kategori_pengeluaran");
?>

<div class="col-md-12 mb-2">
    <!-- FILTER -->
    <div class="card mb-3">
        <div class="card-header">
            <h5><b>Filter Laporan Pengeluaran</b></h5>
        </div>
        <div class="card-body">
            <form method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="dari" class="form-control" value="<?= $tanggal_dari ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="sampai" class="form-control" value="<?= $tanggal_sampai ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-control">
                            <option value="">Semua Kategori</option>
                            <?php while($kat = mysqli_fetch_assoc($kategori_query)): ?>
                            <option value="<?= $kat['kategori_pengeluaran'] ?>" 
                                    <?= $kategori_filter == $kat['kategori_pengeluaran'] ? 'selected' : '' ?>>
                                <?= $kat['kategori_pengeluaran'] ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i> Filter
                            </button>
                            <a href="laporan_pengeluaran.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="row">
        
        <!-- RINGKASAN -->
        <div class="col-md-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5><b>Ringkasan Periode <?= date('d/m/Y', strtotime($tanggal_dari)) ?> - <?= date('d/m/Y', strtotime($tanggal_sampai)) ?></b></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <h3 class="text-danger">Rp <?= format_ribuan($grand_total) ?></h3>
                            <p class="text-muted">Total Pengeluaran</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h3 class="text-info"><?= mysqli_num_rows($result_pengeluaran) ?></h3>
                            <p class="text-muted">Total Transaksi</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h3 class="text-warning">Rp <?= $grand_total > 0 ? format_ribuan($grand_total / mysqli_num_rows($result_pengeluaran)) : 0 ?></h3>
                            <p class="text-muted">Rata-rata per Transaksi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ANALISIS PER KATEGORI -->
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5><b>Analisis per Kategori</b></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th>Jumlah</th>
                                    <th>Total</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($result_kategori)): 
                                    $persentase = $grand_total > 0 ? ($row['total_pengeluaran'] / $grand_total) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?= $row['kategori_pengeluaran'] ?></td>
                                    <td><?= $row['jumlah_transaksi'] ?>x</td>
                                    <td>Rp <?= format_ribuan($row['total_pengeluaran']) ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" style="width: <?= $persentase ?>%">
                                                <?= number_format($persentase, 1) ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- TREND HARIAN -->
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5><b>Trend Pengeluaran Harian</b></h5>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    <?php while($row = mysqli_fetch_assoc($result_harian)): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                        <div>
                            <strong><?= date('d/m/Y', strtotime($row['tanggal'])) ?></strong>
                            <br>
                            <small class="text-muted"><?= $row['jumlah_transaksi'] ?> transaksi</small>
                        </div>
                        <div class="text-end">
                            <strong class="text-danger">Rp <?= format_ribuan($row['total_pengeluaran']) ?></strong>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- DETAIL TRANSAKSI -->
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5><b>Detail Transaksi Pengeluaran</b></h5>
            <div>
                <a href="input_pengeluaran.php" class="btn btn-sm btn-primary">
                    <i class="fa fa-plus"></i> Tambah Pengeluaran
                </a>
                <button onclick="window.print()" class="btn btn-sm btn-success">
                    <i class="fa fa-print"></i> Print
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Kategori</th>
                            <th>Deskripsi</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        mysqli_data_seek($result_pengeluaran, 0); // Reset pointer
                        while($row = mysqli_fetch_assoc($result_pengeluaran)): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                            <td><?= date('H:i', strtotime($row['waktu'])) ?></td>
                            <td>
                                <span class="badge bg-secondary"><?= $row['kategori_pengeluaran'] ?></span>
                            </td>
                            <td><?= $row['deskripsi'] ?></td>
                            <td class="text-danger">
                                <strong>Rp <?= format_ribuan($row['jumlah']) ?></strong>
                            </td>
                            <td>
                                <a href="input_pengeluaran.php?hapus=<?= $row['id_pengeluaran'] ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Yakin hapus pengeluaran ini?')">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-dark">
                            <th colspan="5">TOTAL PENGELUARAN</th>
                            <th class="text-danger">Rp <?= format_ribuan($grand_total) ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-header .btn {
        display: none !important;
    }
}
</style>

<?php include 'template/footer.php'; ?>