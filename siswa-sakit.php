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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Siswa Sakit</title>
    <link rel="icon" type="image/x-icon" href="assets/images/ihbs-logo-2.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <img src="assets/images/uks1.png" style="width:110px;">
            <a href="index.php" class="btn btn-outline-success rounded-pill">
                🔙 <b>Kembali</b>
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
                        <button type="submit" class="btn btn-outline-success">🔍</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- TABLE -->
        <table class="table table-bordered table-striped">
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
                    <td><?= date('l, d F Y', strtotime($row['tgl_sakit'])); ?></td>
                    <td><?= date('H:i', strtotime($row['tgl_sakit'])); ?></td>
                    <td><?= htmlspecialchars($row['nama']); ?></td>
                    <td><?= htmlspecialchars($row['kelas']); ?></td>
                    <td><?= htmlspecialchars($row['diagnosa']); ?></td>
                    <td class="text-center">
                        <button class="btn btn-info btn-sm btn-detail" data-nis="<?= htmlspecialchars($row['nis']); ?>"
                            data-nama="<?= htmlspecialchars($row['nama']); ?>"
                            data-kelas="<?= htmlspecialchars($row['kelas']); ?>"
                            data-tanggal="<?= date('l, d F Y H:i', strtotime($row['tgl_sakit'])); ?>"
                            data-tekanan="<?= htmlspecialchars($row['tekanan_darah']); ?>"
                            data-suhu="<?= htmlspecialchars($row['suhu']); ?>"
                            data-keluhan="<?= htmlspecialchars($row['keluhan']); ?>"
                            data-diagnosa="<?= htmlspecialchars($row['diagnosa']); ?>"
                            data-penanganan="<?= htmlspecialchars($row['penanganan']); ?>"
                            data-petugas="<?= htmlspecialchars($row['nama_petugas']); ?>">
                            Detail
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>

            </tbody>
        </table>

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
     SINGLE MODAL
========================= -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Detail Data Siswa</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body" id="printArea">
                    <table class="table table-bordered">
                        <tr>
                            <th>NIS</th>
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
                            <th>Diagnosa</th>
                            <td id="d_diagnosa"></td>
                        </tr>
                        <tr>
                            <th>Penanganan</th>
                            <td id="d_penanganan"></td>
                        </tr>
                        <tr>
                            <th>Petugas</th>
                            <td id="d_petugas"></td>
                        </tr>
                    </table>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="printModal()">
                        🖨 Print
                    </button>
                    <button class="btn btn-secondary" data-dismiss="modal">Tutup</button>
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
        $("#d_tekanan").text($(this).data("tekanan"));
        $("#d_suhu").text($(this).data("suhu") + " °C");
        $("#d_keluhan").text($(this).data("keluhan"));
        $("#d_diagnosa").text($(this).data("diagnosa"));
        $("#d_penanganan").text($(this).data("penanganan"));
        $("#d_petugas").text($(this).data("petugas"));

        $("#detailModal").modal("show");
    });
    </script>

    <script>
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
    </script>

</body>

</html>