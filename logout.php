<?php
// Pastikan sesi sudah dimulai agar bisa dihancurkan
// Ini harus selalu di baris paling atas
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi koneksi database
$conn = mysqli_connect("localhost", "root", "", "db_kasir2");
if (!$conn) {
    // Tangani error koneksi database
    // Ini penting agar tidak ada masalah lebih lanjut jika database tidak bisa diakses
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// --- Logika PHP untuk Aksi GET ---

// Logika untuk menghapus data laporan (dipanggil oleh AJAX dari JavaScript)
if (isset($_GET['do']) && $_GET['do'] == 'hapus') {
    mysqli_query($conn, "DELETE FROM laporanku");
    mysqli_close($conn); // Tutup koneksi setelah selesai
    echo "OK";
    exit; // PENTING: Hentikan eksekusi script setelah mengirim respons AJAX
}

// Logika untuk logout (dipanggil oleh JavaScript setelah hapus data)
if (isset($_GET['do']) && $_GET['do'] == 'logout') {
    // Kosongkan semua variabel sesi
    $_SESSION = array();

    // Hapus cookie sesi jika ada
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Hancurkan sesi di server
    session_destroy();

    // Redirect ke halaman index.php atau login.php setelah logout
    // Sesuaikan 'index.php' jika Anda ingin ke 'login.php'
    header("Location: index.php");
    exit(); // PENTING: Hentikan eksekusi script setelah redirect
}

// --- Pengambilan Data untuk Laporan ---
// Ini akan dijalankan saat halaman diakses pertama kali (tanpa parameter 'do')
// Tangkap output laporan_keuangan.php ke dalam buffer
ob_start();
include 'laporan_keuangan.php';
$laporan_html = ob_get_clean();

// Pastikan variabel $labels, $data, $colors (untuk grafik) tersedia
// Jika laporan_keuangan.php tidak mendefinisikannya, grafik tidak akan muncul
// Atau Anda bisa mengambil data ini langsung di sini jika laporan_keuangan.php hanya HTML
// Contoh inisialisasi jika laporan_keuangan.php hanya berisi HTML dan tidak ada logika PHP
if (!isset($labels)) $labels = [];
if (!isset($data)) $data = [];
if (!isset($colors)) $colors = [];

// Jika laporan_keuangan.php berisi logika pengambilan data,
// pastikan variabel-variabel tersebut tersedia setelah 'include'
// Misalnya, jika $labels, $data, $colors didefinisikan di laporan_keuangan.php
// maka mereka akan tersedia di sini setelah include.

mysqli_close($conn); // Tutup koneksi setelah semua data diambil

?>
<!DOCTYPE html>
<html>
<head>
    <title>Mengunduh Laporan & Logout...</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <style>
        /* Sembunyikan konten laporan agar tidak terlihat oleh pengguna */
        #hidden-report-container {
            display: none;
            /* Atau gunakan visibility: hidden; position: absolute; left: -9999px; */
        }
    </style>
</head>
<body>
    <div id="hidden-report-container">
        <?= $laporan_html ?>
        <?php if (isset($labels) && !empty($labels)): ?>
        <canvas id="grafikMenu"></canvas>
        <script>
            // Pastikan Chart.js hanya diinisialisasi jika ada elemen grafik
            const ctx = document.getElementById('grafikMenu');
            if (ctx) {
                new Chart(ctx.getContext('2d'), {
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
                            legend: { display: false },
                            title: { display: true, text: 'Grafik Menu Terjual' }
                        },
                        // Nonaktifkan animasi untuk mempercepat rendering jika perlu
                        animation: false
                    }
                });
            }
        </script>
        <?php endif; ?>
    </div>

    <div style="text-align: center; margin-top: 50px; font-family: sans-serif;">
        <h2>Sedang Mengunduh Laporan dan Logout...</h2>
        <p>Mohon tunggu sebentar.</p>
        <img src="https://i.stack.imgur.com/kdkU4.gif" alt="Loading GIF" style="width: 50px;">
    </div>

    <script>
    async function exportPDFAndLogout() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'pt', 'a4');

        // Pastikan ada waktu untuk Chart.js merender grafik
        await new Promise(resolve => setTimeout(resolve, 500)); // Delay 0.5 detik

        const chartCanvas = document.getElementById('grafikMenu');
        if (chartCanvas) {
            try {
                const chartImage = chartCanvas.toDataURL('image/png');
                doc.addImage(chartImage, 'PNG', 40, 30, 750, 300);
            } catch (e) {
                console.error("Gagal mengambil gambar grafik:", e);
                // Lanjutkan tanpa grafik jika gagal
            }
        }

        const table = document.querySelector('#table'); // Ambil tabel dari DOM
        if (table) {
            const clonedTable = table.cloneNode(true);
            const tfoot = clonedTable.querySelector('tfoot');
            let totalText = '';
            if (tfoot) {
                const totalCell = tfoot.querySelector('th:last-child');
                if (totalCell) {
                    totalText = totalCell.innerText;
                }
                tfoot.remove(); // Hapus tfoot agar tidak diduplikasi di PDF
            }

            // Tambahkan baris total secara manual ke tabel klon
            const trTotal = document.createElement('tr');
            const tdLabel = document.createElement('td');
            tdLabel.colSpan = 6; // Sesuaikan colspan sesuai jumlah kolom tabel Anda
            tdLabel.innerText = "TOTAL PENJUALAN HARIAN:";
            const tdValue = document.createElement('td');
            tdValue.innerText = totalText;
            trTotal.appendChild(tdLabel);
            trTotal.appendChild(tdValue);
            clonedTable.querySelector('tbody').appendChild(trTotal);

            await doc.autoTable({
                html: clonedTable,
                startY: chartCanvas ? 340 : 40, // Sesuaikan startY jika ada grafik
                theme: 'grid',
                styles: {
                    fontSize: 10,
                    cellPadding: 5
                },
                headStyles: {
                    fillColor: [111, 66, 193]
                },
            });
        } else {
            console.warn("Tabel dengan ID 'table' tidak ditemukan.");
        }
        
        const now = new Date();
        const tanggal = now.toISOString().slice(0, 10); // Format: YYYY-MM-DD
        const waktu = now.toTimeString().slice(0, 8).replace(/:/g, '-'); // Format: HH-MM-SS
        const namaFile = `laporan_${tanggal}_${waktu}.pdf`;
        doc.save(namaFile);
        // Setelah PDF diunduh, kirim permintaan hapus data
        fetch(window.location.pathname + '?do=hapus')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok for deletion');
                }
                return response.text();
            })
            .then(result => {
                console.log("Data dihapus:", result);
                // Setelah data dihapus, redirect untuk logout
                window.location.href = window.location.pathname + "?do=logout";
            })
            .catch(error => {
                console.error("Gagal menghapus data atau error fetch:", error);
                // Jika ada error dalam proses hapus, tetap coba logout
                window.location.href = window.location.pathname + "?do=logout";
            });
    }

    // Panggil fungsi saat halaman selesai dimuat
    window.onload = exportPDFAndLogout;
    </script>
</body>
</html>