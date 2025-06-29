<?php include 'template/header.php'; ?>
<?php include 'config.php'; ?>

<?php
function format_ribuan($angka) {
    return number_format($angka, 0, ',', '.');
}

// Default filter tanggal untuk TAMPILAN di form dan laporan (DD Month YYYY)
// Serta konversi untuk KEPERLUAN SQL (YYYY-MM-DD)
if (isset($_GET['dari']) && isset($_GET['sampai'])) {
    // Jika tanggal dipilih dari form, ambil nilainya
    $tanggal_dari_display = $_GET['dari'];
    $tanggal_sampai_display = $_GET['sampai'];

    // Konversi tanggal dari format input date (YYYY-MM-DD) ke format DD Month YYYY untuk tampilan
    $tanggal_dari_formatted_display = date('d F Y', strtotime($tanggal_dari_display));
    $tanggal_sampai_formatted_display = date('d F Y', strtotime($tanggal_sampai_display));

    // Untuk SQL, kita butuh format YYYY-MM-DD agar STR_TO_DATE di MySQL bisa membandingkan
    $tanggal_dari_sql = $tanggal_dari_display;
    $tanggal_sampai_sql = $tanggal_sampai_display;

} else {
    // Jika tidak ada filter, gunakan tanggal default bulan ini
    // Untuk tampilan di form (akan berupa YYYY-MM-DD untuk input type="date")
    $tanggal_dari_display = date('Y-m-01'); // '2025-06-01'
    $tanggal_sampai_display = date('Y-m-d'); // '2025-06-29'

    // Untuk tampilan di laporan (DD Month YYYY)
    $tanggal_dari_formatted_display = date('01 F Y'); // '01 June 2025'
    $tanggal_sampai_formatted_display = date('d F Y'); // '29 June 2025'

    // Untuk SQL, tetap YYYY-MM-DD
    $tanggal_dari_sql = date('Y-m-01');
    $tanggal_sampai_sql = date('Y-m-d');
}


// --- Query Data Pemasukan (dari tabel laporanku) ---
$query_pemasukan_sql = "
    SELECT
        tgl_input,
        no_transaksi,
        nama_barang,
        harga_barang,
        SUM(quantity) AS quantity,
        SUM(subtotal) AS subtotal
    FROM
        laporanku
    WHERE
        STR_TO_DATE(tgl_input, '%d %M %Y') BETWEEN ? AND ?
    GROUP BY
        no_transaksi, nama_barang, harga_barang, tgl_input
    ORDER BY
        STR_TO_DATE(tgl_input, '%d %M %Y') DESC, no_transaksi DESC
";

// Menggunakan Prepared Statements untuk query pemasukan (PENTING!)
$stmt_pemasukan = mysqli_prepare($conn, $query_pemasukan_sql);
$data_pemasukan_grouped = [];
$total_pemasukan = 0;

if ($stmt_pemasukan) {
    mysqli_stmt_bind_param($stmt_pemasukan, "ss", $tanggal_dari_sql, $tanggal_sampai_sql);
    mysqli_stmt_execute($stmt_pemasukan);
    $result_pemasukan = mysqli_stmt_get_result($stmt_pemasukan);

    if ($result_pemasukan) {
        while ($row = mysqli_fetch_assoc($result_pemasukan)) {
            $kode = $row['no_transaksi'];
            if (!isset($data_pemasukan_grouped[$kode])) {
                $data_pemasukan_grouped[$kode] = [
                    'tgl_input' => $row['tgl_input'], // Ambil string aslinya untuk ditampilkan
                    'items' => [],
                    'total_transaksi' => 0
                ];
            }
            $data_pemasukan_grouped[$kode]['items'][] = [
                'nama_barang' => $row['nama_barang'],
                'quantity' => $row['quantity'],
                'harga_barang' => $row['harga_barang'],
                'subtotal' => $row['subtotal']
            ];
            $data_pemasukan_grouped[$kode]['total_transaksi'] += $row['subtotal'];
            $total_pemasukan += $row['subtotal'];
        }
    } else {
        error_log("Error fetching pemasukan result: " . mysqli_error($conn));
        // Opsional: echo "<p>Error mengambil data pemasukan.</p>";
    }
    mysqli_stmt_close($stmt_pemasukan);
} else {
    error_log("Error preparing pemasukan statement: " . mysqli_error($conn));
    // Opsional: echo "<p>Error menyiapkan query pemasukan.</p>";
}
// --- Query Data Pengeluaran (dari tabel pengeluaran) ---
$query_pengeluaran_sql = "
    SELECT
        p.tanggal,
        p.waktu,
        p.deskripsi,
        p.jumlah,
        kp.nama_kategori AS nama_kategori_display
    FROM
        pengeluaran p
    JOIN
        kategori_pengeluaran kp ON p.id_kategori = kp.id_kategori
    WHERE
        p.tanggal BETWEEN ? AND ? -- Langsung bandingkan jika p.tanggal sudah DATE type
    ORDER BY
        p.tanggal DESC, p.waktu DESC
";
// ... (bagian bind_param tetap sama, karena $tanggal_dari_sql dan $tanggal_sampai_sql sudah YYYY-MM-DD)
// Menggunakan Prepared Statements untuk query pengeluaran (PENTING!)
$stmt_pengeluaran = mysqli_prepare($conn, $query_pengeluaran_sql);
$total_pengeluaran = 0;
$pengeluaran_data = [];

if ($stmt_pengeluaran) {
    mysqli_stmt_bind_param($stmt_pengeluaran, "ss", $tanggal_dari_sql, $tanggal_sampai_sql);
    mysqli_stmt_execute($stmt_pengeluaran);
    $result_pengeluaran = mysqli_stmt_get_result($stmt_pengeluaran);

    if ($result_pengeluaran) {
        while ($row = mysqli_fetch_assoc($result_pengeluaran)) {
            $pengeluaran_data[] = $row;
            $total_pengeluaran += $row['jumlah'];
        }
    } else {
        error_log("Error fetching pengeluaran result: " . mysqli_error($conn));
        // Opsional: echo "<p>Error mengambil data pengeluaran.</p>";
    }
    mysqli_stmt_close($stmt_pengeluaran);
} else {
    error_log("Error preparing pengeluaran statement: " . mysqli_error($conn));
    // Opsional: echo "<p>Error menyiapkan query pengeluaran.</p>";
}

// Hitung Saldo Bersih
$saldo_bersih = $total_pemasukan - $total_pengeluaran;

?>

<div class="col-md-9 mb-2">
    <div class="card mb-3">
        <div class="card-header bg-warning text-white">
            <h5><b><i class="fa fa-file-invoice"></i> Laporan Keuangan Gabungan</b></h5>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="dari" class="form-control" value="<?= htmlspecialchars($tanggal_dari_display) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="sampai" class="form-control" value="<?= htmlspecialchars($tanggal_sampai_display) ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fa fa-search"></i> Filter
                        </button>
                        <a href="laporan_keuangan.php" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5><b>Ringkasan Keuangan Periode: <?= htmlspecialchars($tanggal_dari_formatted_display) ?> - <?= htmlspecialchars($tanggal_sampai_formatted_display) ?></b></h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h3 class="text-success">Rp <?= format_ribuan($total_pemasukan) ?></h3>
                            <p class="text-muted">Total Pemasukan</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-danger">Rp <?= format_ribuan($total_pengeluaran) ?></h3>
                            <p class="text-muted">Total Pengeluaran</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="<?= $saldo_bersih >= 0 ? 'text-primary' : 'text-danger' ?>">
                                Rp <?= format_ribuan($saldo_bersih) ?>
                            </h3>
                            <p class="text-muted">Saldo Bersih</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mb-3">
                <button onclick="exportExcel()" class="btn btn-success me-2">
                    <i class="fa fa-file-excel"></i> Export Excel
                </button>
                <button onclick="exportPDF()" class="btn btn-danger">
                    <i class="fa fa-file-pdf"></i> Export PDF
                </button>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5><b>Detail Pemasukan (Penjualan)</b></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm" id="table-pemasukan">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Kode Transaksi</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Harga Satuan</th>
                                    <th>Sub-Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no_pemasukan = 1;
                                if (!empty($data_pemasukan_grouped)):
                                    foreach ($data_pemasukan_grouped as $kode_transaksi => $transaksi):
                                        foreach ($transaksi['items'] as $item):
                                ?>
                                <tr>
                                    <td><?= $no_pemasukan++ ?></td>
                                    <td><?= htmlspecialchars($transaksi['tgl_input']) ?></td>
                                    <td><?= htmlspecialchars($kode_transaksi) ?></td>
                                    <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                                    <td>Rp <?= format_ribuan($item['harga_barang']) ?></td>
                                    <td>Rp <?= format_ribuan($item['subtotal']) ?></td>
                                </tr>
                                <?php
                                        endforeach;
                                    endforeach;
                                else:
                                ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada data pemasukan pada periode ini.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-success">
                                    <th colspan="6" class="text-end">TOTAL PEMASUKAN</th>
                                    <th>Rp <?= format_ribuan($total_pemasukan) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5><b>Detail Pengeluaran</b></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm" id="table-pengeluaran">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Kategori</th>
                                    <th>Deskripsi</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no_pengeluaran = 1;
                                if (!empty($pengeluaran_data)):
                                    foreach ($pengeluaran_data as $row):
                                ?>
                                <tr>
                                    <td><?= $no_pengeluaran++ ?></td>
                                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                    <td><?= htmlspecialchars($row['waktu']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_kategori_display']) ?></td>
                                    <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                                    <td>Rp <?= format_ribuan($row['jumlah']) ?></td>
                                </tr>
                                <?php
                                    endforeach;
                                else:
                                ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada data pengeluaran pada periode ini.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-danger">
                                    <th colspan="5" class="text-end">TOTAL PENGELUARAN</th>
                                    <th>Rp <?= format_ribuan($total_pengeluaran) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>

<?php if (!empty($labels)): // This condition depends on your PHP providing $labels for a chart ?>
<script>
const ctx = document.getElementById('grafikMenu').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Jumlah Terjual',
            data: <?= json_encode($data) ?>,
            backgroundColor: <?= json_encode($colors) ?>
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            title: {
                display: true,
                text: 'Grafik Menu Terjual'
            }
        }
    }
});
</script>
<?php endif; ?>

<script>
function formatRibuanJS(angka) {
    return new Intl.NumberFormat('id-ID').format(angka);
}

function exportExcel() {
    // Gunakan tanggal_dari_formatted_display untuk tampilan di Excel
    const tanggalDari = "<?= htmlspecialchars($tanggal_dari_formatted_display) ?>";
    const tanggalSampai = "<?= htmlspecialchars($tanggal_sampai_formatted_display) ?>";
    const totalPemasukan = "Rp <?= format_ribuan($total_pemasukan) ?>";
    const totalPengeluaran = "Rp <?= format_ribuan($total_pengeluaran) ?>";
    const saldoBersih = "Rp <?= format_ribuan($saldo_bersih) ?>";

    let excelHTML = `
    <html>
    <head>
        <meta charset="utf-8" />
        <style>
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid black; padding: 8px; text-align: left; }
            .text-end { text-align: right; }
            .text-center { text-align: center; }
            h2, h3 { text-align: center; }
        </style>
    </head>
    <body>
        <h2>Laporan Keuangan Gabungan</h2>
        <h3>Periode: ${tanggalDari} - ${tanggalSampai}</h3>
        <table>
            <thead>
                <tr>
                    <th colspan="3">Ringkasan Keuangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Total Pemasukan:</strong></td>
                    <td colspan="2" class="text-end">${totalPemasukan}</td>
                </tr>
                <tr>
                    <td><strong>Total Pengeluaran:</strong></td>
                    <td colspan="2" class="text-end">${totalPengeluaran}</td>
                </tr>
                <tr>
                    <td><strong>Saldo Bersih:</strong></td>
                    <td colspan="2" class="text-end">${saldoBersih}</td>
                </tr>
            </tbody>
        </table>
        <br>
        <h3>Detail Pemasukan (Penjualan)</h3>
    `;

    // Append Pemasukan table
    const tablePemasukan = document.getElementById('table-pemasukan').outerHTML;
    excelHTML += tablePemasukan;

    excelHTML += `
        <br>
        <h3>Detail Pengeluaran</h3>
    `;

    // Append Pengeluaran table
    const tablePengeluaran = document.getElementById('table-pengeluaran').outerHTML;
    excelHTML += tablePengeluaran;

    excelHTML += `</body></html>`;

    const filename = 'laporan_keuangan_gabungan.xls';
    const link = document.createElement('a');
    link.href = 'data:application/vnd.ms-excel,' + encodeURIComponent(excelHTML);
    link.download = filename;
    link.click();
}

function exportPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'pt', 'a4'); // 'p' for portrait, 'pt' for points, 'a4' for A4 size

    // Add Title and Period
    // Gunakan tanggal_dari_formatted_display untuk tampilan di PDF
    const tanggalDari = "<?= htmlspecialchars($tanggal_dari_formatted_display) ?>";
    const tanggalSampai = "<?= htmlspecialchars($tanggal_sampai_formatted_display) ?>";
    doc.setFontSize(16);
    doc.text('Laporan Keuangan Gabungan', doc.internal.pageSize.getWidth() / 2, 30, { align: 'center' });
    doc.setFontSize(12);
    doc.text(`Periode: ${tanggalDari} - ${tanggalSampai}`, doc.internal.pageSize.getWidth() / 2, 50, { align: 'center' });

    // Add Summary
    const summaryData = [
        ['Total Pemasukan:', 'Rp <?= format_ribuan($total_pemasukan) ?>'],
        ['Total Pengeluaran:', 'Rp <?= format_ribuan($total_pengeluaran) ?>'],
        ['Saldo Bersih:', 'Rp <?= format_ribuan($saldo_bersih) ?>']
    ];
    doc.autoTable({
        startY: 70,
        head: [['Ringkasan Keuangan', '']],
        body: summaryData,
        theme: 'grid',
        styles: { fontSize: 10, cellPadding: 5 },
        headStyles: { fillColor: [66, 133, 244] },
        columnStyles: { 1: { halign: 'right' } }
    });

    let finalY = doc.autoTable.previous.finalY;

    // Add Pemasukan Table
    doc.setFontSize(14);
    doc.text('Detail Pemasukan (Penjualan)', doc.internal.pageSize.getWidth() / 2, finalY + 30, { align: 'center' });
    doc.autoTable({
        html: '#table-pemasukan',
        startY: finalY + 45,
        theme: 'grid',
        styles: { fontSize: 8, cellPadding: 4 },
        headStyles: { fillColor: [52, 168, 83] },
        didParseCell: function(data) {
            if (data.column.index === 4 || data.column.index === 5 || data.column.index === 6) {
                data.cell.styles.halign = 'right';
            }
        }
    });
    finalY = doc.autoTable.previous.finalY;

    // Add Pengeluaran Table
    doc.setFontSize(14);
    doc.text('Detail Pengeluaran', doc.internal.pageSize.getWidth() / 2, finalY + 30, { align: 'center' });
    doc.autoTable({
        html: '#table-pengeluaran',
        startY: finalY + 45,
        theme: 'grid',
        styles: { fontSize: 8, cellPadding: 4 },
        headStyles: { fillColor: [234, 67, 53] },
        didParseCell: function(data) {
            if (data.column.index === 5) {
                data.cell.styles.halign = 'right';
            }
        }
    });

    doc.save("laporan_keuangan_gabungan.pdf");
}
</script>

<style>
@media print {
    /* Sembunyikan elemen yang tidak perlu saat dicetak */
    .btn, .card-header button, .form-control, label, .card-header .btn-primary, .card-header .btn-secondary,
    .d-flex.align-items-end button, .d-flex.align-items-end a, .text-end .btn-success, .text-end .btn-danger {
        display: none !important;
    }
    .card-header, .card-footer {
        background-color: #f8f9fa !important;
        color: #212529 !important;
        border-bottom: 1px solid #dee2e6;
    }
    body {
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    .table thead th {
        background-color: #e9ecef !important;
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.05) !important;
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    .table-success {
        background-color: #d1e7dd !important;
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    .table-danger {
        background-color: #f8d7da !important;
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
}
</style>

<?php include 'template/footer.php'; ?>