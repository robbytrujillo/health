<?php
session_start();
require 'config.php';
require 'phpqrcode/qrlib.php';

// echo "<pre>";
// print_r($conn->query("
// SELECT id_sakit, nis, nama, kelas, tgl_sakit
// FROM tb_sakit
// WHERE nama LIKE '%Sholihin%'
// ORDER BY tgl_sakit DESC
// ")->fetch_all(MYSQLI_ASSOC));
// echo "</pre>";
// exit;

/* ===============================
   GRAFIK SISWA SAKIT PER BULAN
================================= */

$tahunAktif = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$queryChart = "
SELECT 
    MONTH(tgl_sakit) as bulan,
    COUNT(*) as jumlah
FROM tb_sakit
WHERE YEAR(tgl_sakit) = '$tahunAktif'
GROUP BY MONTH(tgl_sakit)
ORDER BY bulan ASC
";

$resultChart = $conn->query($queryChart);

$dataBulanan = array_fill(1, 12, 0);

while($row = $resultChart->fetch_assoc()){
    $dataBulanan[(int)$row['bulan']] = (int)$row['jumlah'];
}

$namaBulan = [
    'Jan','Feb','Mar','Apr','Mei','Jun',
    'Jul','Agu','Sep','Okt','Nov','Des'
];

$jumlahChart = array_values($dataBulanan);

/* ===============================
   SISWA YANG SERING SAKIT
================================= */
$querySeringSakit = "
    SELECT nis, nama, kelas, alamat, COUNT(*) as jumlah_sakit
    FROM tb_sakit
    GROUP BY nis, nama, kelas, alamat
    ORDER BY jumlah_sakit DESC
    LIMIT 5
";

// ==========================
// PAGINATION SERING SAKIT
// ==========================

$limit = 5; // tampil 5 data per halaman
$pageSering = isset($_GET['page_sering']) ? (int)$_GET['page_sering'] : 1;
$startSering = ($pageSering - 1) * $limit;

// Hitung total data
// $countQuery = "SELECT COUNT(*) as total FROM (
//     SELECT nis FROM tb_sakit 
//     GROUP BY nis 
//     HAVING COUNT(*) >= 1
// ) as total_data";

$countQuery = "SELECT COUNT(*) as total FROM (
    SELECT nis, nama, kelas 
    FROM tb_sakit 
    GROUP BY nis, nama, kelas
) as total_data";



$countResult = $conn->query($countQuery);
$totalDataSering = $countResult->fetch_assoc()['total'];
$totalPagesSering = ceil($totalDataSering / $limit);

// Query utama dengan LIMIT

// $querySeringSakit = "
//     SELECT nis, nama, kelas, COUNT(*) as jumlah_sakit
//     FROM tb_sakit
//     GROUP BY nis
//     ORDER BY jumlah_sakit DESC
//     LIMIT $startSering, $limit
// ";

// $querySeringSakit = "
//     SELECT 
//         nis,
//         nama,
//         kelas,
//         COUNT(DISTINCT tgl_sakit) as jumlah_sakit
//     FROM tb_sakit
//     GROUP BY nis, nama, kelas
//     ORDER BY jumlah_sakit DESC
//     LIMIT $startSering, $limit
// ";

$querySeringSakit = "
    SELECT 
        nis,
        nama,
        kelas,
        COUNT(*) as jumlah_sakit
    FROM tb_sakit
    GROUP BY nis, nama, kelas
    ORDER BY jumlah_sakit DESC, nama ASC
    LIMIT $startSering, $limit
";

$resultSeringSakit = $conn->query($querySeringSakit);

/* ===============================
   TOTAL SAKIT BULAN INI
================================= */
$bulanSekarang = date('m');
$tahunSekarang = date('Y');

$queryBulanIni = "
SELECT COUNT(*) as total_bulan
FROM tb_sakit
WHERE MONTH(tgl_sakit) = '$bulanSekarang'
AND YEAR(tgl_sakit) = '$tahunSekarang'
";
$totalBulanIni = $conn->query($queryBulanIni)->fetch_assoc()['total_bulan'];

/* ===============================
   TOTAL SAKIT TAHUN INI
================================= */
$queryTahunIni = "
SELECT COUNT(*) as total_tahun
FROM tb_sakit
WHERE YEAR(tgl_sakit) = '$tahunSekarang'
";
$totalTahunIni = $conn->query($queryTahunIni)->fetch_assoc()['total_tahun'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index | UKS Management</title>
    <link rel="icon" type="image/x-icon" href="assets/images/ihbs-logo-2.png">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style-index.css">
    <link rel="stylesheet" href="assets/css/style-hoverzoom.css">

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        font-family: 'Poppins', sans-serif;
        font-weight: bold;
    }

    p,
    a,
    input,
    strong,
    tr,
    th,
    td,
    button,
    div {
        font-family: 'Poppins', sans-serif;
    }

    .pagination .page-link {
        border-radius: 30px !important;
    }

    .chart-wrapper {
        position: relative;
        width: 100%;
        overflow-x: auto;
    }

    canvas {
        max-width: 100%;
    }

    @media (max-width: 768px) {
        .chart-navigation button {
            width: 100px;
            margin-bottom: 10px;
        }

        select[name="tahun"] {
            width: 180px;
            margin: auto;
        }

        h4 {
            text-align: center !important;
        }

        .badge-pill {
            display: inline-block;
            margin-top: 8px;
        }

        .table th,
        .table td {
            font-size: 13px;
            white-space: nowrap;
        }

        .pagination {
            justify-content: center;
        }

        .card-body h2 {
            font-size: 28px;
        }

        .card-body h6 {
            font-size: 14px;
        }
    }
    </style>
</head>

<body>

    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <img src="assets/images/uks1.png" style="width:110px;">
        </div>

        <!-- Search Box -->
        <form method="GET" class="search-box">
            <div class="input-group mb-4">
                <input type="text" name="nama_siswa" class="form-control" placeholder="Cari siswa sakit..." required>
                <div class="input-group-append">
                    <button class="btn btn-success" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>

        <!-- =========================
            HASIL PENCARIAN
        ========================= -->

        <div class="row">
            <?php
                if (isset($_GET['nama_siswa'])) {

                $nama_siswa = $_GET['nama_siswa'];
                $sql = "SELECT * FROM tb_sakit WHERE nama LIKE ?";
                $stmt = $conn->prepare($sql);
                $searchTerm = "%".$nama_siswa."%";
                $stmt->bind_param("s", $searchTerm);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {

                    $qr_data = "Data Siswa: $nama_siswa\n\n";

                    echo "<div class='col-12'>
                            <div class='alert alert-success'>
                                <strong>Siswa \"$nama_siswa\"</strong> ditemukan
                            </div>
                        </div>";

                    while ($row = $result->fetch_assoc()) {

                    // Format tanggal & jam
                    $tanggal = date('d-m-Y', strtotime($row['tgl_sakit']));
                    $jam     = date('H:i', strtotime($row['tgl_sakit']));

                        echo "<div class='col-md-4 mb-4'>
                                <div class='card shadow-sm'>
                                    <div class='card-body text-center'>
                                        <h5>{$row['nama']}</h5>
                                        <p><strong>NIS:</strong> {$row['nis']}</p>
                                        <p><strong>Kelas:</strong> {$row['kelas']}</p>
                                        <p><strong>Tanggal:</strong> $tanggal</p>
                                        <p><strong>Jam:</strong> $jam</p>
                                        <p><strong>Diagnosa:</strong> {$row['diagnosa']}</p>
                                    </div>
                                </div>
                            </div>";

                        $qr_data .= "- {$tanggal} - {$jam} - {$row['nama']} (Kelas: {$row['kelas']}) (Diagnosa: {$row['diagnosa']})\n";
                    }

                    if (!file_exists("qrcodes")) {
                        mkdir("qrcodes", 0777, true);
                    }

                    $qr_filename = "qrcodes/" . md5($nama_siswa) . ".png";
                    QRcode::png($qr_data, $qr_filename, QR_ECLEVEL_L, 5);

                    echo "<div class='col-md-3'>
                            <div class='card p-3 text-center'>
                                <h5>QR Code</h5>
                                <img src='$qr_filename'>
                                <button style='border-radius: 30px;' class='btn btn-success mt-3' onclick=\"printQRCode('$qr_filename')\"><i class='fas fa-print'></i> Cetak</button>
                            </div>
                        </div>";

                } else {
                    echo "<div class='col-12'>
                            <div class='alert alert-danger'>Siswa tidak ditemukan</div>
                        </div>";
                }

                $stmt->close();
            }
            ?>
        </div>

        <!-- =========================
            NAVIGASI MENU
        ========================= -->
        <div class="row mt-5">

            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-light mb-3 shadow-sm rounded-lg border-0">
                        <div class="card-body text-center">
                            <img src="assets/images/student.svg" style="height: 320px" class="cover img-fluid">
                            <h5 class="mt-3 mb-3">Data Siswa</h5>
                            <a href="siswa-user.php"
                                class="btn btn-outline-success btn-block font-weight-bold rounded-pill">Lihat Data</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-light mb-3 shadow-sm rounded-lg border-0">
                        <div class="card-body text-center">
                            <img src="assets/images/sick.svg" style="height: 320px" class="cover img-fluid">
                            <h5 class="mt-3 mb-3">Siswa Sakit</h5>
                            <a href="siswa-sakit.php"
                                class="btn btn-outline-success btn-block font-weight-bold rounded-pill">Lihat Data</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-light mb-3 shadow-sm rounded-lg border-0">
                        <div class="card-body text-center">
                            <img src="assets/images/petugas2.svg" style="height: 320px" class="cover img-fluid">
                            <h5 class="mt-3 mb-3">Data Petugas</h5>
                            <a href="petugas-uks.php"
                                class="btn btn-outline-success btn-block font-weight-bold rounded-pill">Lihat Data</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- =========================
        GRAFIK SISWA SAKIT PER BULAN
        ========================= -->
        <div class="row align-items-center mb-4">

            <div class="col-md-6 col-12 mb-2 mb-md-0">
                <h4 class="font-weight-bold text-center text-md-left">
                    <!--📈 Grafik Siswa Sakit-->
                    Grafik Siswa Sakit
                </h4>
            </div>

            <div class="col-md-6 col-12 text-center text-md-right">
                <form method="GET" class="d-inline-block">
                    <select name="tahun" class="form-control rounded-pill px-4" onchange="this.form.submit()">
                        <?php
                        $tahunNow = date('Y');
                        for($t = $tahunNow; $t >= 2020; $t--):
                        ?>
                        <option value="<?= $t ?>" <?= ($tahunAktif == $t) ? 'selected' : '' ?>>
                            Tahun <?= $t ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </form>
            </div>

        </div>

        <!-- CARD CHART RESPONSIVE MODERN -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">

                <div class="chart-navigation text-center mb-3">
                    <button onclick="prevChart()" class="btn btn-outline-success rounded-pill btn-sm px-4 mr-2">
                        ◀ Prev
                    </button>

                    <button onclick="nextChart()" class="btn btn-outline-success rounded-pill btn-sm px-4">
                        Next ▶
                    </button>
                </div>

                <div class="chart-wrapper">
                    <canvas id="chartSakitBulanan"></canvas>
                </div>

            </div>
        </div>


        <!-- =========================
        DASHBOARD SISWA SERING SAKIT
        ========================= -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">

                <!-- HEADER RESPONSIVE -->
                <div class="row align-items-center mb-4">

                    <!-- Judul -->
                    <div class="col-md-6 col-12 mb-3 mb-md-0">
                        <h4 class="font-weight-bold text-center text-md-left mb-0">
                            <!--📊 Siswa Yang Sering Sakit-->
                            Siswa Yang Sering Sakit
                        </h4>
                    </div>

                    <!-- Badge Ringkasan -->
                    <div class="col-md-6 col-12 text-center text-md-right">
                        <span class="badge badge-pill badge-primary px-3 py-2 mr-2 mb-2">
                            Bulan Ini: <?= $totalBulanIni ?>
                        </span>

                        <span class="badge badge-pill badge-success px-3 py-2 mb-2">
                            Tahun Ini: <?= $totalTahunIni ?>
                        </span>
                    </div>

                </div>

                <!-- CARD RINGKASAN -->
                <div class="row mb-4">

                    <!-- Total Bulan Ini -->
                    <div class="col-md-6 col-12 mb-3">
                        <div class="card border-0 shadow-sm text-center h-100">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">
                                    Total Siswa Sakit Bulan Ini
                                </h6>

                                <h2 class="font-weight-bold text-primary mb-1">
                                    <?= $totalBulanIni ?>
                                </h2>

                                <small class="text-muted">
                                    <?= date('F Y') ?>
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Total Tahun Ini -->
                    <div class="col-md-6 col-12 mb-3">
                        <div class="card border-0 shadow-sm text-center h-100">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">
                                    Total Siswa Sakit Tahun Ini
                                </h6>

                                <h2 class="font-weight-bold text-success mb-1">
                                    <?= $totalTahunIni ?>
                                </h2>

                                <small class="text-muted">
                                    Tahun <?= $tahunSekarang ?>
                                </small>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- TABLE RESPONSIVE -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <!--<th>NIS</th>-->
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Jumlah Sakit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                    $no = $startSering + 1;
                    if ($resultSeringSakit->num_rows > 0):
                        while ($row = $resultSeringSakit->fetch_assoc()):
                    ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <!--<td><?= $row['nis'] ?></td>-->
                                <td><?= $row['nama'] ?></td>
                                <td><?= $row['kelas'] ?></td>
                                <td>
                                    <?php if ($row['jumlah_sakit'] >= 5): ?>
                                    <span class="badge badge-danger px-3 py-2 rounded-pill">
                                        <?= $row['jumlah_sakit'] ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="badge badge-primary px-3 py-2 rounded-pill">
                                        <?= $row['jumlah_sakit'] ?>
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                        endwhile;
                    else:
                    ?>
                            <tr>
                                <td colspan="5">Belum ada data</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <nav class="mt-4">
                    <ul class="pagination justify-content-center pagination-sm flex-wrap">

                        <!-- Previous -->
                        <li class="page-item <?= ($pageSering <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-pill"
                                href="?tahun=<?= $tahunAktif ?>&page_sering=<?= $pageSering - 1 ?>">
                                ❮
                            </a>
                        </li>

                        <!-- Nomor Halaman -->
                        <?php for ($i = 1; $i <= $totalPagesSering; $i++): ?>
                        <li class="page-item <?= ($i == $pageSering) ? 'active' : '' ?>">
                            <a class="page-link rounded-pill mx-1"
                                href="?tahun=<?= $tahunAktif ?>&page_sering=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <!-- Next -->
                        <li class="page-item <?= ($pageSering >= $totalPagesSering) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-pill"
                                href="?tahun=<?= $tahunAktif ?>&page_sering=<?= $pageSering + 1 ?>">
                                ❯
                            </a>
                        </li>

                    </ul>
                </nav>

            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
    function printQRCode(url) {
        var qrWindow = window.open('', '_blank');
        qrWindow.document.write('<html><body style="text-align:center;">');
        qrWindow.document.write('<img src="' + url + '" style="width:600px;">');
        qrWindow.document.write('<script>window.onload = function() { window.print(); window.close(); }<' + '/script>');
        qrWindow.document.write('</body></html>');
        qrWindow.document.close();
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    const allLabels = <?= json_encode($namaBulan); ?>;
    const allData = <?= json_encode($jumlahChart); ?>;

    const itemsPerPage = 3;

    // Bulan sekarang
    const currentMonth = new Date().getMonth();

    // Fokus awal: 2 bulan sebelum bulan sekarang
    let startIndex = currentMonth - 2;
    if (startIndex < 0) startIndex = 0;

    // Batasi supaya tidak lewat Desember
    if (startIndex > allLabels.length - itemsPerPage) {
        startIndex = allLabels.length - itemsPerPage;
    }

    const ctx = document.getElementById('chartSakitBulanan').getContext('2d');

    let chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: allLabels.slice(startIndex, startIndex + itemsPerPage),
            datasets: [{
                label: 'Jumlah Siswa Sakit',
                data: allData.slice(startIndex, startIndex + itemsPerPage),
                borderWidth: 2,
                borderRadius: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    function updateChart() {
        chart.data.labels = allLabels.slice(startIndex, startIndex + itemsPerPage);
        chart.data.datasets[0].data = allData.slice(startIndex, startIndex + itemsPerPage);
        chart.update();
    }

    // NEXT = maju 1 bulan
    function nextChart() {
        if (startIndex < allLabels.length - itemsPerPage) {
            startIndex++;
            updateChart();
        }
    }

    // PREV = mundur 1 bulan
    function prevChart() {
        if (startIndex > 0) {
            startIndex--;
            updateChart();
        }
    }
    </script>
</body>

</html>