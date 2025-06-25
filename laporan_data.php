<?php
function format_ribuan($nilai) {
    return number_format($nilai, 0, ',', '.');
}

// Ambil dan gabungkan data
$query = mysqli_query($conn, "
    SELECT no_transaksi, tgl_input, nama_barang, harga_barang, jenis, SUM(quantity) AS quantity, SUM(subtotal) AS subtotal
    FROM laporanku 
    GROUP BY no_transaksi, nama_barang 
    ORDER BY MIN(id_cart)
");

$data_grouped = [];
$total_semua = 0;

while ($row = mysqli_fetch_assoc($query)) {
    $kode = $row['no_transaksi'];
    if (!isset($data_grouped[$kode])) {
        $data_grouped[$kode] = [
            'tgl_input' => $row['tgl_input'],
            'items' => [],
            'total_transaksi' => 0
        ];
    }

    $data_grouped[$kode]['items'][] = [
        'nama_barang' => $row['nama_barang'],
        'quantity' => $row['quantity'],
        'harga_barang' => $row['harga_barang'],
        'subtotal' => $row['subtotal']
    ];
    $data_grouped[$kode]['total_transaksi'] += $row['subtotal'];
    $total_semua += $row['subtotal'];
}
?>

<div class="col-md-12 mb-3">
    <div class="card">
        <div class="card-header bg-purple d-flex justify-content-between align-items-center">
            <div class="card-title text-white"><i class="fa fa-table"></i> <b>Data Laporan</b></div>
            <div>
                <?php if ($total_semua > 0): ?>
                <button class="btn btn-sm btn-light" onclick="exportExcel()">Excel</button>
                <button class="btn btn-sm btn-light" onclick="exportPDF()">PDF</button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-sm" id="table" width="100%">
                <thead class="thead-purple">
                    <tr>
                        <th>No</th>
                        <th>Kode Transaksi</th>
                        <th>Tanggal</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Sub-Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($data_grouped as $kode => $transaksi) {
                        $tgl = $transaksi['tgl_input'];
                        $items = $transaksi['items'];

                        foreach ($items as $item) {
                            echo "<tr>";
                            echo "<td>{$no}</td>";
                            echo "<td>{$kode}</td>";
                            echo "<td>{$tgl}</td>";
                            echo "<td>{$item['nama_barang']}</td>";
                            echo "<td>{$item['quantity']}</td>";
                            echo "<td>Rp. " . format_ribuan($item['harga_barang']) . ",-</td>";
                            echo "<td>Rp. " . format_ribuan($item['subtotal']) . ",-</td>";
                            echo "</tr>";
                            $no++;
                        }

                        echo "<tr class='bg-light'>";
                        echo "<td colspan='6' class='text-end'><b>Subtotal Transaksi:</b></td>";
                        echo "<td><b>Rp. " . format_ribuan($transaksi['total_transaksi']) . ",-</b></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-end"><b>TOTAL :</b></th>
                        <th><b>Rp. <?= format_ribuan($total_semua) ?>,-</b></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>