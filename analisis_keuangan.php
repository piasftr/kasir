<?php include 'template/header.php'; ?>
<?php include 'config.php'; ?>

<?php
function format_ribuan($angka) {
    return number_format($angka, 0, ',', '.');
}

// Default filter
$tanggal_dari = isset($_GET['dari']) ? $_GET['dari'] : date('Y-m-01'); // Awal bulan
$tanggal_sampai = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-d'); // Hari ini

// PENDAPATAN - Asumsi dari tabel transaksi/penjualan
// Sesuaikan dengan nama tabel yang ada di sistem Anda
$query_pendapatan = "SELECT 
                        COALESCE(SUM(subtotal), 0) as total_pendapatan,
                        COUNT(*) as jumlah_transaksi
                     FROM keranjang 
                     WHERE tgl_input BETWEEN '$tanggal_dari' AND '$tanggal_sampai'";
$result_pendapatan = mysqli_query($conn, $query_pendapatan);
$data_pendapatan = mysqli_fetch_assoc($result_pendapatan);
$total_pendapatan = $data_pendapatan['total_pendapatan'] ?? 0;
$jumlah_transaksi_jual = $data_pendapatan['jumlah_transaksi'] ?? 0;

// PENGELUARAN
$query_pengeluaran = "SELECT 
                         COALESCE(SUM(jumlah), 0) as total_pengeluaran,
                         COUNT(*) as jumlah_transaksi_beli
                      FROM pengeluaran 
                      WHERE tanggal BETWEEN '$tanggal_dari' AND '$tanggal_sampai'";
$result_pengeluaran = mysqli_query($conn, $query_pengeluaran);
$data_pengeluaran = mysqli_fetch_assoc($result_pengeluaran);
$total_pengeluaran = $data_pengeluaran['total_pengeluaran'] ?? 0;
$jumlah_transaksi_beli = $data_pengeluaran['jumlah_transaksi_beli'] ?? 0;

// HITUNG LABA/RUGI
$laba_rugi = $total_pendapatan - $total_pengeluaran;
$persentase_laba = $total_pendapatan > 0 ? ($laba_rugi / $total_pendapatan) * 100 : 0;

// ANALISIS PENGELUARAN TERBESAR
$query_kategori_terbesar = "SELECT 
                               kategori_pengeluaran,
                               SUM(jumlah) as total,
                               COUNT(*) as frekuensi,
                               AVG(jumlah) as rata_rata
                            FROM pengeluaran 
                            WHERE tanggal BETWEEN '$tanggal_dari' AND '$tanggal_sampai'
                            GROUP BY kategori_pengeluaran 
                            ORDER BY total DESC 
                            LIMIT 5";
$result_kategori_terbesar = mysqli_query($conn, $query_kategori_terbesar);

// TREND HARIAN PENDAPATAN VS PENGELUARAN
$query_trend = "SELECT 
                   dates.tanggal,
                   COALESCE(p.pendapatan, 0) as pendapatan,
                   COALESCE(e.pengeluaran, 0) as pengeluaran,
                   (COALESCE(p.pendapatan, 0) - COALESCE(e.pengeluaran, 0)) as profit
                FROM (
                    SELECT DISTINCT DATE(tgl_input) as tanggal FROM keranjang 
                    WHERE tgl_input BETWEEN '$tanggal_dari' AND '$tanggal_sampai'
                    UNION
                    SELECT DISTINCT tanggal FROM pengeluaran 
                    WHERE tanggal BETWEEN '$tanggal_dari' AND '$tanggal_sampai'
                ) dates
                LEFT JOIN (
                    SELECT DATE(tgl_input) as tanggal, SUM(subtotal) as pendapatan 
                    FROM keranjang 
                    WHERE tgl_input BETWEEN '$tanggal_dari' AND '$tanggal_sampai'
                    GROUP BY DATE(tgl_input)
                ) p ON dates.tanggal = p.tanggal
                LEFT JOIN (
                    SELECT tanggal, SUM(jumlah) as pengeluaran 
                    FROM pengeluaran 
                    WHERE tanggal BETWEEN '$tanggal_dari' AND '$tanggal_sampai'
                    GROUP BY tanggal
                ) e ON dates.tanggal = e.tanggal
                ORDER BY dates.tanggal DESC";
$result_trend = mysqli_query($conn, $query_trend);

// RASIO KEUANGAN
$rasio_pengeluaran = $total_pendapatan > 0 ? ($total_pengeluaran / $total_pendapatan) * 100 : 0;
$rata_pendapatan_harian = $total_pendapatan / max(1, (strtotime($tanggal_sampai) - strtotime($tanggal_dari)) / 86400 + 1);
$rata_pengeluaran_harian = $total_pengeluaran / max(1, (strtotime($tanggal_sampai) - strtotime($tanggal_dari)) / 86400 + 1);
?>

<div class="col-md-12 mb-2">
    <!-- FILTER -->
    <div class="card mb-3">
        <div class="card-header">
            <h5><b>Analisis Keuangan & Laba Rugi</b></h5>
        </div>
        <div class="card-body">
            <form method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="dari" class="form-control" value="<?= $tanggal_dari ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="sampai" class="form-control" value="<?= $tanggal_sampai ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i> Analisis
                            </button>
                            <a href="analisis_keuangan.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- RINGKASAN LABA RUGI -->
    <div class="row">
        <div class="col-md-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5><b>Laporan Laba Rugi Periode <?= date('d/m/Y', strtotime($tanggal_dari)) ?> - <?= date('d/m/Y', strtotime($tanggal_sampai)) ?></b></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h4>Rp <?= format_ribuan($total_pendapatan) ?></h4>
                                    <p class="mb-0">Total Pendapatan</p>
                                    <small><?= $jumlah_transaksi_jual ?> transaksi</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h4>Rp <?= format_ribuan($total_pengeluaran) ?></h4>
                                    <p class="mb-0">Total Pengeluaran</p>
                                    <small><?= $jumlah_transaksi_beli ?> transaksi</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="card <?= $laba_rugi >= 0 ? 'bg-primary' : 'bg-warning' ?> text-white">
                                <div class="card-body">
                                    <h4>Rp <?= format_ribuan($laba_rugi) ?></h4>
                                    <p class="mb-0"><?= $laba_rugi >= 0 ? 'LABA' : 'RUGI' ?></p>
                                    <small><?= number_format($persentase_laba, 1) ?>% dari pendapatan</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h4><?= number_format($rasio_pengeluaran, 1) ?>%</h4>
                                    <p class="mb-0">Rasio Pengeluaran</p>
                                    <small>dari total pendapatan</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- ANALISIS PENGELUARAN TERBESAR -->
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5><b>Top 5 Pengeluaran Terbesar</b></h5>
                </div>
                <div class="card-body">
                    <?php 
                    $rank = 1;
                    $max_pengeluaran = 0;
                    
                    // Cari nilai maksimum untuk persentase bar
                    mysqli_data_seek($result_kategori_terbesar, 0);
                    while($row = mysqli_fetch_assoc($result_kategori_terbesar)) {
                        if ($row['total'] > $max_pengeluaran) {
                            $max_pengeluaran = $row['total'];
                        }
                    }
                    
                    // Reset pointer dan tampilkan data
                    mysqli_data_seek($result_kategori_terbesar, 0);
                    while($row = mysqli_fetch_assoc($result_kategori_terbesar)): 
                        $persentase_bar = $max_pengeluaran > 0 ? ($row['total'] / $max_pengeluaran) * 100 : 0;
                        $persentase_total = $total_pengeluaran > 0 ? ($row['total'] / $total_pengeluaran) * 100 : 0;
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="badge bg-secondary"><?= $rank++ ?></span>
                                <strong><?= $row['kategori_pengeluaran'] ?></strong>
                            </div>
                            <div class="text-end">
                                <strong class="text-danger">Rp <?= format_ribuan($row['total']) ?></strong>
                                <br>
                                <small class="text-muted"><?= $row['frekuensi'] ?>x transaksi</small>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 25px;">
                            <div class="progress-bar bg-danger" style="width: <?= $persentase_bar ?>%">
                                <?= number_format($persentase_total, 1) ?>% dari total pengeluaran
                            </div>
                        </div>
                        <small class="text-muted">Rata-rata per transaksi: Rp <?= format_ribuan($row['rata_rata']) ?></small>
                    </div>
                    <?php endwhile; ?>
                    
                    <?php if (mysqli_num_rows($result_kategori_terbesar) == 0): ?>
                    <p class="text-center text-muted">Belum ada data pengeluaran pada periode ini</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- RASIO KEUANGAN -->
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5><b>Indikator Keuangan</b></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="text-center p-3 border rounded">
                                <h5 class="text-success">Rp <?= format_ribuan($rata_pendapatan_harian) ?></h5>
                                <small>Rata-rata Pendapatan Harian</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-center p-3 border rounded">
                                <h5 class="text-danger">Rp <?= format_ribuan($rata_pengeluaran_harian) ?></h5>
                                <small>Rata-rata Pengeluaran Harian</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Kesehatan Keuangan -->
                    <div class="mt-3">
                        <h6><b>Status Kesehatan Keuangan:</b></h6>
                        <?php if ($persentase_laba > 20): ?>
                            <div class="alert alert-success">
                                <i class="fa fa-check-circle"></i> <strong>SANGAT SEHAT</strong><br>
                                Laba di atas 20%, bisnis berjalan sangat baik!
                            </div>
                        <?php elseif ($persentase_laba > 10): ?>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> <strong>SEHAT</strong><br>
                                Laba 10-20%, kondisi bisnis stabil.
                            </div>
                        <?php elseif ($persentase_laba > 0): ?>
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle"></i> <strong>PERLU PERHATIAN</strong><br>
                                Laba di bawah 10%, perlu optimasi pengeluaran.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="fa fa-times-circle"></i> <strong>RUGI</strong><br>
                                Segera evaluasi dan kurangi pengeluaran!
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Rekomendasi -->
                    <div class="mt-3">
                        <h6><b>Rekomendasi:</b></h6>
                        <ul class="small">
                            <?php if ($rasio_pengeluaran > 80): ?>
                                <li>Pengeluaran terlalu tinggi (<?= number_format($rasio_pengeluaran, 1) ?>%), kurangi pengeluaran non-esensial</li>
                            <?php endif; ?>
                            
                            <?php if (mysqli_num_rows($result_kategori_terbesar) > 0): 
                                mysqli_data_seek($result_kategori_terbesar, 0);
                                $top_expense = mysqli_fetch_assoc($result_kategori_terbesar);
                            ?>
                                <li>Fokus optimasi pada kategori "<?= $top_expense['kategori_pengeluaran'] ?>" yang merupakan pengeluaran terbesar</li>
                            <?php endif; ?>
                            
                            <?php if ($persentase_laba < 10): ?>
                                <li>Pertimbangkan untuk menaikkan harga jual atau mencari supplier yang lebih murah</li>
                            <?php endif; ?>
                            
                            <li>Lakukan review rutin setiap minggu untuk memantau tren keuangan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- TREND HARIAN -->
    <div class="card">
        <div class="card-header">
            <h5><b>Trend Harian Pendapatan vs Pengeluaran</b></h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pendapatan</th>
                            <th>Pengeluaran</th>
                            <th>Laba/Rugi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result_trend)): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                            <td class="text-success">Rp <?= format_ribuan($row['pendapatan']) ?></td>
                            <td class="text-danger">Rp <?= format_ribuan($row['pengeluaran']) ?></td>
                            <td class="<?= $row['profit'] >= 0 ? 'text-primary' : 'text-warning' ?>">
                                <strong>Rp <?= format_ribuan($row['profit']) ?></strong>
                            </td>
                            <td>
                                <?php if ($row['profit'] > 0): ?>
                                    <span class="badge bg-success">Untung</span>
                                <?php elseif ($row['profit'] == 0): ?>
                                    <span class="badge bg-secondary">Impas</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Rugi</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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
                            <a href="index.php" class="btn btn-success w-100">
                                <i class="fa fa-shopping-cart"></i> Kembali ke Kasir
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button onclick="window.print()" class="btn btn-secondary w-100">
                                <i class="fa fa-print"></i> Print Laporan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-header .btn {
        display: none !important;
    }
    .card {
        border: 1px solid #000 !important;
        page-break-inside: avoid;
    }
}
</style>

<?php include 'template/footer.php'; ?>