<?php
session_start();
require 'config.php';
require 'phpqrcode/qrlib.php';

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
$countQuery = "SELECT COUNT(*) as total FROM (
    SELECT nis FROM tb_sakit 
    GROUP BY nis 
    HAVING COUNT(*) >= 1
) as total_data";

$countResult = $conn->query($countQuery);
$totalDataSering = $countResult->fetch_assoc()['total'];
$totalPagesSering = ceil($totalDataSering / $limit);

// Query utama dengan LIMIT
$querySeringSakit = "
    SELECT nis, nama, kelas, COUNT(*) as jumlah_sakit
    FROM tb_sakit
    GROUP BY nis
    ORDER BY jumlah_sakit DESC
    LIMIT $startSering, $limit
";


$resultSeringSakit = $conn->query($querySeringSakit);
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
         DASHBOARD SISWA SERING SAKIT
        ========================= -->
        <div class="card row mb-2 shadow-sm border-0">
            <div class="card-body">
                <h4 class="mb-3 font-weight-bold">📊 Siswa Yang Sering Sakit</h4>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <!-- <th>Alamat</th> -->
                                <th>Jumlah Sakit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                        // $no = 1;
                        $no = $startSering + 1;
                        if ($resultSeringSakit->num_rows > 0):
                            while ($row = $resultSeringSakit->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $row['nis'] ?></td>
                                <td><?= $row['nama'] ?></td>
                                <td><?= $row['kelas'] ?></td>
                                <!-- <td><?= $row['alamat'] ?? '-' ?></td> -->
                                <td>
                                    <?php if ($row['jumlah_sakit'] >= 5): ?>
                                    <span class="badge badge-danger px-3 py-2" style="border-radius: 30px;">
                                        <?= $row['jumlah_sakit'] ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="badge badge-primary px-3 py-2" style="border-radius: 30px;">
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
                                <td colspan="6">Belum ada data</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- PAGINATION -->
                    <nav>
                        <!-- <ul class="pagination justify-content-center mt-3"> -->
                        <ul class="pagination justify-content-center mt-3 pagination-sm">

                            <!-- Previous -->
                            <li class="page-item <?= ($pageSering <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page_sering=<?= $pageSering - 1 ?>">❮</a>
                            </li>

                            <?php for ($i = 1; $i <= $totalPagesSering; $i++): ?>
                            <li class="page-item <?= ($i == $pageSering) ? 'active' : '' ?>">
                                <a class="page-link" href="?page_sering=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                            <?php endfor; ?>

                            <!-- Next -->
                            <li class="page-item <?= ($pageSering >= $totalPagesSering) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page_sering=<?= $pageSering + 1 ?>">❯</a>
                            </li>

                        </ul>
                    </nav>

                </div>
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

</body>

</html>