<?php
session_start();
include 'config.php';

/* ======================
   Pagination
====================== */
$limit = 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = ($page < 1) ? 1 : $page;
$start = ($page - 1) * $limit;

/* ======================
   Search
====================== */
$whereClause = "";
$params = [];
$types  = "";

if (!empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $whereClause = "WHERE tb_sakit.nama LIKE ?";
    $params[] = $search;
    $types .= "s";
}

/* ======================
   COUNT DATA
====================== */
$countQuery = "SELECT COUNT(*) as total
               FROM tb_sakit
               LEFT JOIN tb_petugas 
               ON tb_sakit.id_petugas = tb_petugas.id
               $whereClause";

$stmt = $conn->prepare($countQuery);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$countResult = $stmt->get_result();
$totalData = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalData / $limit);

/* ======================
   SELECT DATA
====================== */
$sql = "SELECT tb_sakit.*, tb_petugas.nama_petugas
        FROM tb_sakit
        LEFT JOIN tb_petugas 
        ON tb_sakit.id_petugas = tb_petugas.id
        $whereClause
        ORDER BY tb_sakit.tgl_sakit DESC
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $params[] = $start;
    $params[] = $limit;
    $stmt->bind_param($types . "ii", ...$params);
} else {
    $stmt->bind_param("ii", $start, $limit);
}

$stmt->execute();
$result = $stmt->get_result();

/* ======================
   HARI INDO
====================== */
function hariIndonesia($tanggal) {
    $hari = date('l', strtotime($tanggal));

    $hariIndo = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu'
    ];

    return $hariIndo[$hari];
}

function tanggalIndonesia($tanggal) {

    $bulan = [
        1 => 'Januari','Februari','Maret','April','Mei','Juni',
        'Juli','Agustus','September','Oktober','November','Desember'
    ];

    $tanggalExplode = explode('-', date('Y-m-d', strtotime($tanggal)));

    return $tanggalExplode[2] . ' ' .
           $bulan[(int)$tanggalExplode[1]] . ' ' .
           $tanggalExplode[0];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa Sakit</title>
    <link rel="icon" type="image/x-icon" href="assets/images/ihbs-logo-2.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
    html,
    body {
        height: 100%;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .main-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .container {
        flex: 1;
    }

    footer {
        margin-top: 3;
        background: #f8f9fa;
        padding: 15px 0;
    }

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
    th,
    td,
    tr,
    input,
    button {
        font-family: 'Poppins', sans-serif;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    table {
        min-width: 750px;
    }

    .pagination .page-link {
        border-radius: 20px;
        margin: 0 3px;
    }

    .pagination .active .page-link {
        background-color: #28a745;
        border-color: #28a745;
    }

    .modal-dialog {
        margin: 1rem auto;
    }

    @media (max-width: 576px) {
        .modal-lg {
            max-width: 95%;
        }

        .modal-body {
            padding: 15px;
        }

        .modal-title {
            font-size: 16px;
        }

        .table th {
            font-size: 13px;
        }

        .table td {
            font-size: 13px;
        }
    }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <div class="container mt-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <img src="assets/images/uks1.png" style="width:110px;">
                <a href="index.php" class="btn btn-outline-success rounded-pill">
                    <i class="fas fa-arrow-left"></i> <b>Kembali</b>
                </a>
            </div>

            <h3 class="text-center mb-4">Daftar Siswa Sakit</h3>

            <!-- SEARCH -->
            <div class="d-flex justify-content-end mb-3">
                <form method="GET" class="form-inline">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama siswa..."
                            value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-outline-secondary"><i
                                    class="fas fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- TABLE -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm text-nowrap">
                    <thead class="thead-light text-center">
                        <tr>
                            <th>No</th>
                            <th>Hari / Tanggal</th>
                            <th>Waktu</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Diagnosa</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php 
                        $no = $start + 1;
                        while ($row = $result->fetch_assoc()):
                    ?>

                        <tr>
                            <td><?= $no++; ?></td>
                            <!--<td><?= date('l, d F Y', strtotime($row['tgl_sakit'])); ?></td>-->
                            <td>
                                <?= hariIndonesia($row['tgl_sakit']); ?>,
                                <?= tanggalIndonesia($row['tgl_sakit']); ?>
                            </td>
                            <td><?= date('H:i', strtotime($row['tgl_sakit'])); ?></td>
                            <td><?= htmlspecialchars($row['nama']); ?></td>
                            <td><?= htmlspecialchars($row['kelas']); ?></td>
                            <td><?= htmlspecialchars($row['diagnosa']); ?></td>
                            <td class="text-center">
                                <button style="border-radius: 30px;" class="btn btn-info btn-sm btn-detail"
                                    data-nis="<?= htmlspecialchars($row['nis']); ?>"
                                    data-nama="<?= htmlspecialchars($row['nama']); ?>"
                                    data-kelas="<?= htmlspecialchars($row['kelas']); ?>" data-tanggal="
                                
                                    <?= hariIndonesia($row['tgl_sakit']); ?>,
                                    <?= tanggalIndonesia($row['tgl_sakit']); ?>
                                
                                " data-jam="<?= date('H:i', strtotime($row['tgl_sakit'])); ?>"
                                    data-tekanan="<?= htmlspecialchars($row['tekanan_darah']); ?>"
                                    data-suhu="<?= htmlspecialchars($row['suhu']); ?>"
                                    data-keluhan="<?= htmlspecialchars($row['keluhan']); ?>"
                                    data-diagnosa="<?= htmlspecialchars($row['diagnosa']); ?>"
                                    data-penanganan="<?= htmlspecialchars($row['penanganan']); ?>"
                                    data-petugas="<?= htmlspecialchars($row['nama_petugas']); ?>">
                                    <i class="fas fa-info-circle"></i> Detail
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>

                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <nav>
                <ul class="pagination justify-content-center">

                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link"
                            href="?page=<?= $page - 1 ?>&search=<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            ❮
                        </a>
                    </li>

                    <?php
                        $startPage = max(1, $page - 2);
                        $endPage   = min($totalPages, $page + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link"
                            href="?page=<?= $i ?>&search=<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>

                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link"
                            href="?page=<?= $page + 1 ?>&search=<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            ❯
                        </a>
                    </li>

                </ul>
            </nav>

        </div>

        <!-- =========================
             SINGLE MODAL RESPONSIVE
        ========================= -->
        <div class="modal fade" id="detailModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header bg-light">
                        <h5 class="modal-title">Detail Data Siswa</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body" id="printArea" style="text-align: center;">

                        <!-- Tambahkan ini supaya table bisa scroll di HP -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th style="width:40%">NIS</th>
                                    <td id="d_nis"></td>
                                </tr>
                                <tr>
                                    <th>Nama</th>
                                    <td id="d_nama"></td>
                                </tr>
                                <tr>
                                    <th>Kelas</th>
                                    <td id="d_kelas"></td>
                                </tr>
                                <tr>
                                    <th>Hari / Tanggal</th>
                                    <td id="d_tanggal"></td>
                                </tr>
                                <tr>
                                    <th>Jam</th>
                                    <td id="d_jam"></td>
                                </tr>
                                <tr>
                                    <th>Tekanan Darah</th>
                                    <td id="d_tekanan"></td>
                                </tr>
                                <tr>
                                    <th>Suhu</th>
                                    <td id="d_suhu"></td>
                                </tr>
                                <tr>
                                    <th>Keluhan</th>
                                    <td id="d_keluhan"></td>
                                </tr>
                                <tr>
                                    <th>Penanganan</th>
                                    <td id="d_penanganan"></td>
                                </tr>
                                <tr>
                                    <th>Diagnosa</th>
                                    <td id="d_diagnosa"></td>
                                </tr>
                                <tr>
                                    <th>Petugas</th>
                                    <td id="d_petugas"></td>
                                </tr>
                            </table>
                        </div>

                    </div>

                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-primary rounded-pill" onclick="printModal()">
                            <i class="fas fa-print"></i> Cetak
                        </button>

                        <!-- <button class="btn btn-secondary rounded-pill" data-dismiss="modal">
                            <i class="fas fa-times"></i> Tutup
                        </button> -->
                    </div>

                </div>
            </div>
        </div>

    </div>
    </div>

    <?php include 'includes/footer.php'; ?>



    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).on("click", ".btn-detail", function() {

        $("#d_nis").text($(this).data("nis"));
        $("#d_nama").text($(this).data("nama"));
        $("#d_kelas").text($(this).data("kelas"));
        $("#d_tanggal").text($(this).data("tanggal"));
        $("#d_jam").text($(this).data("jam"));
        $("#d_tekanan").text($(this).data("tekanan"));
        $("#d_suhu").text($(this).data("suhu") + " °C");
        $("#d_keluhan").text($(this).data("keluhan"));
        $("#d_diagnosa").text($(this).data("diagnosa"));
        $("#d_penanganan").text($(this).data("penanganan"));
        $("#d_petugas").text($(this).data("petugas"));

        $("#detailModal").modal("show");
    });
    </script>

    <!-- <script>
    function printModal() {

        var printContents = document.getElementById('printArea').innerHTML;

        var printWindow = window.open('', '', 'height=700,width=900');

        printWindow.document.write(`
        <html>
        <head>
            <title>Print Detail Data Siswa</title>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
            <style>
                body { padding: 30px; font-family: Arial; }
                table { width: 100%; }
                th { width: 30%; }
            </style>
        </head>
        <body>
            <h4 class="text-center mb-4">Detail Data Siswa Sakit</h4>
            ${printContents}
        </body>
        </html>
    `);

        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }
    </script> -->

    <?php
        $logoURL = "http://" . $_SERVER['HTTP_HOST'] . "/health/assets/images/logo-sma.png";
    ?>

    <script>
    function printModal() {

        var nis = $("#d_nis").text();
        var nama = $("#d_nama").text();
        var kelas = $("#d_kelas").text();
        var tanggal = $("#d_tanggal").text();
        var jam = $("#d_jam").text();
        var tekanan = $("#d_tekanan").text();
        var suhu = $("#d_suhu").text();
        var keluhan = $("#d_keluhan").text();
        var diagnosa = $("#d_diagnosa").text();
        var penanganan = $("#d_penanganan").text();
        var petugas = $("#d_petugas").text();

        var printWindow = window.open('', '', 'height=900,width=1000');
        var logoPath = "<?= $logoURL ?>";

        printWindow.document.write(`
    <html>
    <head>
        <title>Hasil Pemeriksaan UKS</title>

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

        <style>
            body{
                font-family:'Poppins',sans-serif;
                padding:40px;
                color:#222;
                font-size:14px;
            }

            .report-container{
                max-width:900px;
                margin:auto;
            }

            /* HEADER */
            .header{
                border-bottom:3px solid #222;
                padding-bottom:20px;
                margin-bottom:30px;
                display:flex;
                align-items:center;
                justify-content:space-between;
            }

            .header-left{
                display:flex;
                align-items:center;
            }

            .logo img{
                width:70px;
                margin-right:20px;
            }

            .school-info h2{
                margin:0;
                font-size:22px;
                font-weight:700;
            }

            .school-info p{
                margin:0;
                font-size:13px;
            }

            .report-code{
                text-align:right;
                font-size:13px;
            }

            .report-title{
                text-align:center;
                margin-bottom:30px;
            }

            .report-title h3{
                margin:0;
                font-weight:700;
                font-size:20px;
                letter-spacing:1px;
            }

            .report-title p{
                margin-top:6px;
                font-size:13px;
                color:#666;
            }

            /* INFO SECTION LIKE INVOICE */
            .invoice-box{
                display:flex;
                justify-content:space-between;
                margin-bottom:30px;
                gap:30px;
            }

            .info-card{
                flex:1;
                border:1px solid #ddd;
                padding:18px;
                border-radius:8px;
                background:#fafafa;
            }

            .info-card h5{
                font-size:14px;
                font-weight:700;
                margin-bottom:15px;
                text-transform:uppercase;
                color:#555;
            }

            .info-line{
                display:flex;
                margin-bottom:8px;
            }

            .info-label{
                width:120px;
                font-weight:600;
                color:#444;
            }

            .info-value{
                flex:1;
            }

            /* MEDICAL TABLE */
            .medical-table{
                width:100%;
                border-collapse:collapse;
                margin-top:10px;
            }

            .medical-table th{
                background:#f4f4f4;
                border:1px solid #ccc;
                padding:12px;
                text-align:left;
                font-size:14px;
            }

            .medical-table td{
                border:1px solid #ccc;
                padding:12px;
                vertical-align:top;
            }

            /* SIGNATURE */
            .signature-section{
                margin-top:70px;
                display:flex;
                justify-content:flex-end;
            }

            .signature-box{
                width:250px;
                text-align:center;
            }

            .signature-space{
                height:80px;
            }

            .signature-name{
                font-weight:700;
                border-top:1px solid #000;
                display:inline-block;
                padding-top:8px;
                min-width:180px;
            }

            .footer-note{
                margin-top:50px;
                font-size:12px;
                color:#666;
                text-align:center;
                border-top:1px dashed #ccc;
                padding-top:15px;
            }

            @media print{
                body{
                    padding:20px;
                }
            }
        </style>
    </head>
    <body>

        <div class="report-container">

            <!-- HEADER -->
            <div class="header">
                <div class="header-left">
                    <div class="logo">
                        <img src="${logoPath}">
                    </div>
                    <div class="school-info">
                        <h2>UNIT KESEHATAN SEKOLAH (UKS)</h2>
                        <p>Laporan Pemeriksaan Kesehatan Siswa</p>
                    </div>
                </div>

                <div class="report-code">
                    <strong>No. Dokumen:</strong><br>
                    UKS-${nis}-${new Date().getFullYear()}
                </div>
            </div>

            <!-- TITLE -->
            <div class="report-title">
                <h3>HASIL PEMERIKSAAN SISWA</h3>
                <p>Dokumen Resmi Pemeriksaan Kesehatan</p>
            </div>

            <!-- INVOICE STYLE INFO -->
            <div class="invoice-box">

                <div class="info-card">
                    <h5>Data Siswa</h5>

                    <div class="info-line">
                        <div class="info-label">NIS</div>
                        <div class="info-value">: ${nis}</div>
                    </div>

                    <div class="info-line">
                        <div class="info-label">Nama</div>
                        <div class="info-value">: ${nama}</div>
                    </div>

                    <div class="info-line">
                        <div class="info-label">Kelas</div>
                        <div class="info-value">: ${kelas}</div>
                    </div>
                </div>

                <div class="info-card">
                    <h5>Waktu Pemeriksaan</h5>

                    <div class="info-line">
                        <div class="info-label">Hari, Tanggal</div>
                        <div class="info-value">: ${tanggal}</div>
                    </div>

                    <div class="info-line">
                        <div class="info-label">Jam</div>
                        <div class="info-value">: ${jam} WIB</div>
                    </div>
                </div>

            </div>

            <!-- TABLE ONLY MEDICAL DATA -->
            <table class="medical-table">
                <thead>
                    <tr>
                        <th width="30%">Parameter Pemeriksaan</th>
                        <th style="text-align: center">Hasil</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Tekanan Darah</td>
                        <td>${tekanan}</td>
                    </tr>
                    <tr>
                        <td>Suhu Tubuh</td>
                        <td>${suhu}</td>
                    </tr>
                    <tr>
                        <td>Keluhan</td>
                        <td>${keluhan}</td>
                    </tr>
                    <tr>
                        <td>Diagnosa</td>
                        <td>${diagnosa}</td>
                    </tr>
                    <tr>
                        <td>Penanganan</td>
                        <td>${penanganan}</td>
                    </tr>
                </tbody>
            </table>

            <!-- SIGNATURE -->
            <div class="signature-section">
                <div class="signature-box">
                    <p>Petugas Pemeriksa</p>
                    <div class="signature-space"></div>
                    <div class="signature-name">${petugas}</div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="footer-note">
                Dokumen ini dicetak otomatis oleh Sistem Manajemen UKS SMA IHBS.
            </div>

        </div>

    </body>
    </html>
    `);

        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    }
    </script>

</body>

</html>