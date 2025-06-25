<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "db_kasir"); 

if (isset($_GET['do']) && $_GET['do'] == 'hapus') {
    mysqli_query($conn, "DELETE FROM laporanku");
    echo "OK";
    exit;
}


if (isset($_GET['do']) && $_GET['do'] == 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

ob_start();
include 'laporan_chart.php';
include 'laporan_data.php';
$laporan_html = ob_get_clean();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Logout & Download</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
</head>

<body>
    <?= $laporan_html ?>
    <?php if (!empty($labels)): ?>
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
    async function exportPDFAndLogout() {
        const {
            jsPDF
        } = window.jspdf;
        const doc = new jsPDF('l', 'pt', 'a4');

        const chartCanvas = document.getElementById('grafikMenu');
        if (chartCanvas) {
            const chartImage = chartCanvas.toDataURL('image/png');
            doc.addImage(chartImage, 'PNG', 40, 30, 750, 300);
        }

        const table = document.querySelector('#table').cloneNode(true);
        const tfoot = table.querySelector('tfoot');
        const totalText = tfoot ? tfoot.querySelector('th:last-child').innerText : '';
        if (tfoot) tfoot.remove();

        const trTotal = document.createElement('tr');
        const tdLabel = document.createElement('td');
        tdLabel.colSpan = 6;
        tdLabel.innerText = "TOTAL PENJUALAN HARIAN:";
        const tdValue = document.createElement('td');
        tdValue.innerText = totalText;
        trTotal.appendChild(tdLabel);
        trTotal.appendChild(tdValue);
        table.querySelector('tbody').appendChild(trTotal);

        await doc.autoTable({
            html: table,
            startY: 340,
            theme: 'grid',
            styles: {
                fontSize: 10,
                cellPadding: 5
            },
            headStyles: {
                fillColor: [111, 66, 193]
            },
        });

        doc.save("laporan_harian.pdf");

        // Hapus isi tabel laporan, lalu logout
        fetch(window.location.pathname + '?do=hapus')
            .then(response => response.text())
            .then(result => {
                console.log("Data dihapus:", result);
                window.location.href = window.location.pathname + "?do=logout";
            })
            .catch(error => {
                console.error("Gagal hapus:", error);
                window.location.href = window.location.pathname + "?do=logout";
            });
    }


    window.onload = exportPDFAndLogout;
    </script>
</body>

</html>