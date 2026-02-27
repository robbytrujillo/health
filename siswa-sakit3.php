<?php
session_start();
include 'config.php';

// ======================
// Pagination
// ======================
$limit = 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = ($page < 1) ? 1 : $page;
$start = ($page - 1) * $limit;

// ======================
// Search
// ======================
$whereClause = "";
$params = [];
$types  = "";

if (!empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $whereClause = "WHERE tb_sakit.nama LIKE ?";
    $params[] = $search;
    $types .= "s";
}

// ======================
// COUNT DATA
// ======================
$countQuery = "SELECT COUNT(*) as total
               FROM tb_sakit
               LEFT JOIN tb_petugas 
               ON tb_sakit.id_petugas = tb_petugas.id
               $whereClause";

$stmt = $conn->prepare($countQuery);
if (!$stmt) {
    die("COUNT Query Error: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$countResult = $stmt->get_result();
$totalData = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalData / $limit);

// ======================
// SELECT DATA
// ======================
$sql = "SELECT tb_sakit.*, tb_petugas.nama_petugas
        FROM tb_sakit
        LEFT JOIN tb_petugas 
        ON tb_sakit.id_petugas = tb_petugas.id
        $whereClause
        ORDER BY tb_sakit.tgl_sakit DESC
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SELECT Query Error: " . $conn->error);
}

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
    <div class="container mt-3">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <img src="assets/images/uks1.png" style="width:110px;">
            <a href="index.php" class="btn btn-outline-success rounded-pill">
                🔙 <b>Kembali</b>
            </a>
        </div>

        <h2 class="text-center mb-3">Daftar Siswa Sakit</h2>

        <a href="cetak-siswa-sakit.php" class="btn btn-outline-info mb-3 rounded-pill">
            📝 Cetak
        </a>

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
            <thead class="thead-light">
                <tr>
                    <th>No</th>
                    <th>Hari/Tanggal</th>
                    <th>Waktu</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Tekanan Darah</th>
                    <th>Suhu</th>
                    <th>Keluhan</th>
                    <th>Diagnosa</th>
                    <th>Penanganan</th>
                    <th>Petugas</th>
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
                    <td><?= htmlspecialchars($row['nis']); ?></td>
                    <td><?= htmlspecialchars($row['nama']); ?></td>
                    <td><?= htmlspecialchars($row['kelas']); ?></td>
                    <td><?= htmlspecialchars($row['tekanan_darah']); ?></td>
                    <td><?= htmlspecialchars($row['suhu']); ?></td>
                    <td><?= htmlspecialchars($row['keluhan']); ?></td>
                    <td><?= htmlspecialchars($row['diagnosa']); ?></td>
                    <td><?= htmlspecialchars($row['penanganan']); ?></td>
                    <td>
                        <?= !empty($row['nama_petugas'])
                        ? htmlspecialchars($row['nama_petugas'])
                        : '<span class="text-danger">Tidak ada</span>'; ?>
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

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>