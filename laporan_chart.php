<?php
$menu = mysqli_query($conn, "SELECT nama_barang, SUM(quantity) AS jumlah FROM laporanku GROUP BY nama_barang ORDER BY jumlah DESC");
$labels = $data = $colors = [];
while ($m = mysqli_fetch_assoc($menu)) {
    $labels[] = $m['nama_barang'];
    $data[] = $m['jumlah'];
    $colors[] = 'rgba(' . rand(50,255) . ',' . rand(50,200) . ',' . rand(50,255) . ',0.7)';
}
?>

<?php if (!empty($labels)): ?>
<div class="col-md-12 mb-3">
    <div class="card">
        <div class="card-header bg-purple text-white"><b>Grafik Menu Terjual</b></div>
        <div class="card-body">
            <canvas id="grafikMenu" width="600" height="300"></canvas>
        </div>
    </div>
</div>
<?php endif; ?>