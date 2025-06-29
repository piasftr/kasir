<?php include 'template/header.php'; ?>
<?php include 'config.php'; ?>

<?php
function format_ribuan($angka) {
    return number_format($angka, 0, ',', '.');
}

if (isset($_POST['simpan_pengeluaran'])) {
    $id_kategori = $_POST['id_kategori'] ?? null;
    $deskripsi = $_POST['deskripsi'] ?? '';
    $jumlah = $_POST['jumlah'] ?? 0; // Ini masih string "40.000"
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $waktu = $_POST['waktu'] ?? date('H:i:s');

    // --- MULAI PERBAIKAN JUMLAH ---
    // 1. Hapus titik sebagai pemisah ribuan
    $jumlah_cleaned = str_replace('.', '', $jumlah);

    // 2. Konversi string yang sudah bersih menjadi integer
    //    Gunakan (float) jika jumlah bisa memiliki desimal (misal: 40.500)
    //    Gunakan (int) jika jumlah selalu bilangan bulat (misal: 40000)
    $jumlah_for_db = (int)$jumlah_cleaned;
    // --- AKHIR PERBAIKAN JUMLAH ---

    // validasi FK
    if (empty($id_kategori)) {
        echo "<script>alert('Pilih kategori dulu!'); window.history.back();</script>";
        exit;
    }

    // --- PERHATIAN PENTING: SANGAT DISARANKAN MENGGUNAKAN PREPARED STATEMENTS UNTUK KEAMANAN ---
    // Kode di bawah ini masih rentan terhadap SQL Injection.
    // Jika Anda ingin metode yang lebih aman (sangat direkomendasikan):
    /*
    $stmt = mysqli_prepare($conn, "
        INSERT INTO pengeluaran (id_kategori, deskripsi, jumlah, tanggal, waktu)
        VALUES (?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, "ssiss", $id_kategori, $deskripsi, $jumlah_for_db, $tanggal, $waktu);
    
    if (mysqli_stmt_execute($stmt)) {
        echo '<script>alert("Pengeluaran berhasil disimpan!"); window.location="input_pengeluaran.php";</script>';
    } else {
        echo "Error: " . mysqli_error($conn); // Hanya untuk debugging, hapus di production
        echo "<script>alert('Gagal menyimpan pengeluaran!'); window.history.back();</script>";
    }
    mysqli_stmt_close($stmt);
    */

    // --- KODE SAAT INI (kurang aman, tapi disesuaikan dengan permintaan Anda) ---
    $query = mysqli_query($conn, "
        INSERT INTO pengeluaran (id_kategori, deskripsi, jumlah, tanggal, waktu)
        VALUES ('$id_kategori', '$deskripsi', '$jumlah_for_db', '$tanggal', '$waktu')
    ");

    if (!$query) {
        // Tampilkan error database untuk debugging. Hapus atau log ini di lingkungan produksi.
        echo "Error saat menyimpan: " . mysqli_error($conn);
        echo "<script>alert('Gagal menyimpan pengeluaran!'); window.history.back();</script>";
        exit;
    } else {
        echo '<script>alert("Pengeluaran berhasil disimpan!"); window.location="input_pengeluaran.php";</script>';
    }
}


// HAPUS PENGELUARAN
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM pengeluaran WHERE id_pengeluaran='$id'");
    echo '<script>
            alert("Pengeluaran berhasil dihapus!");
            window.location="input_pengeluaran.php";
          </script>';
}

// Ambil data kategori
// PENTING: Pastikan kolom yang dipilih di sini cocok dengan value option di form input pengeluaran.
// Jika id_kategori di tabel pengeluaran menyimpan ID (int), maka select option harus value=id.
// Jika id_kategori di tabel pengeluaran menyimpan NAMA (varchar), maka select option harus value=nama.
// Berdasarkan diskusi sebelumnya, sebaiknya Anda menggunakan ID kategori.
$kategori_query = mysqli_query($conn, "SELECT * FROM kategori_pengeluaran ORDER BY nama_kategori ASC");

// Ambil data pengeluaran hari ini (sudah diperbaiki di diskusi sebelumnya)
$today = date('Y-m-d');
$pengeluaran_today = mysqli_query($conn, "
    SELECT 
        p.*, 
        kp.nama_kategori AS nama_kategori_display 
    FROM 
        pengeluaran p
    JOIN 
        kategori_pengeluaran kp ON p.id_kategori = kp.id_kategori
    WHERE 
        p.tanggal='$today' 
    ORDER BY 
        p.waktu DESC
");
?>

<div class="col-md-9 mb-2">
    <div class="row">
        
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header bg-success text-light">
                    <h5><b>Input Pengeluaran</b></h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Kategori Pengeluaran</label>
                            <select name="id_kategori" class="form-control" required>
                                <option value="">Pilih Kategori</option>
                                <?php 
                                // Reset pointer kategori_query jika sudah pernah dipakai sebelum loop ini
                                mysqli_data_seek($kategori_query, 0); 
                                while($kat = mysqli_fetch_assoc($kategori_query)): 
                                ?>
                                <option value="<?= $kat['id_kategori'] ?>"><?= $kat['nama_kategori'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan detail pengeluaran..." required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Jumlah (Rp)</label>
                            <input type="text" name="jumlah" class="form-control" id="jumlah" placeholder="0" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        
                        <button type="submit" name="simpan_pengeluaran" class="btn btn-success w-100">
                            <i class="fa fa-save"></i> Simpan Pengeluaran
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header bg-success text-light">
                    <h5><b>Pengeluaran Hari Ini</b></h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php
                    $total_today = 0;
                    if (mysqli_num_rows($pengeluaran_today) > 0):
                        while($row = mysqli_fetch_assoc($pengeluaran_today)):
                            $total_today += $row['jumlah'];
                    ?>
                    <div class="card mb-2">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <small class="text-muted"><?= $row['nama_kategori_display'] ?></small>
                                    <p class="mb-1"><?= $row['deskripsi'] ?></p>
                                    <small class="text-muted"><?= date('H:i', strtotime($row['waktu'])) ?></small>
                                </div>
                                <div class="text-end">
                                    <strong class="text-danger">Rp <?= format_ribuan($row['jumlah']) ?></strong>
                                    <br>
                                    <a href="?hapus=<?= $row['id_pengeluaran'] ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Yakin hapus pengeluaran ini?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <p class="text-center text-muted">Belum ada pengeluaran hari ini</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-success text-light">
                    <strong>Total Hari Ini: Rp <?= format_ribuan($total_today) ?></strong>
                </div>
            </div>
        </div>
        
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row justify-items-between justify-content-between" >
                        <div class="col-md-3 mb-2">
                            <a href="laporan_keuangan.php" class="btn btn-info w-100">
                                <i class="fa fa-chart-line"></i> Laporan
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="index.php" class="btn btn-warning w-100">
                                <i class="fa fa-shopping-cart"></i> Kembali ke Kasir
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="kelola_katagori.php" class="btn btn-secondary w-100">
                                <i class="fa fa-cog"></i> Kelola Kategori
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Format input jumlah dengan thousand separator
document.getElementById('jumlah').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '');
    let formattedValue = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    e.target.value = formattedValue;
});
</script>

<?php include 'template/footer.php'; ?>