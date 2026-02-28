<?php
session_start();
include 'config.php'; // Pastikan ada koneksi database

// Pagination
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Pencarian berdasarkan nama
$whereClause = "";
$params = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $whereClause = "WHERE nama LIKE ?";
    $params[] = $search;
}

// Hitung total data
$countQuery = "SELECT COUNT(*) as total FROM tb_siswa $whereClause";
$stmt = $conn->prepare($countQuery);

if (!empty($params)) {
    $stmt->bind_param("s", ...$params);
}

$stmt->execute();
$countResult = $stmt->get_result();
$totalData = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalData / $limit);

// Query untuk mengambil data siswa dengan pagination
$sql = "SELECT * FROM tb_siswa $whereClause LIMIT ?, ?";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    // Jika ada filter pencarian
    $params[] = $start;
    $params[] = $limit;
    $types = str_repeat("s", count($params) - 2) . "ii"; // Sesuaikan tipe data
    $stmt->bind_param($types, ...$params);
} else {
    // Jika tanpa filter pencarian
    $stmt->bind_param("ii", $start, $limit);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa</title>
    <link rel="icon" type="image/x-icon" href="assets/images/ihbs-logo-2.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

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
    tr,
    td,
    th,
    input,
    strong,
    button,
    div {
        font-family: 'Poppins', sans-serif;
    }
    </style>
</head>

<body>
    <div class="container mt-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <img src="assets/images/uks1.png" style="width: 110px;">
            <a href="index.php" class="btn btn-outline-success rounded-pill"><i class="fas fa-arrow-left"></i>
                <b>Kembali</b></a>
        </div>
        <h2 class="text-center">Daftar Siswa</h2>

        <!-- Tombol cetak -->
        <a href="cetak-siswa-user.php" class="btn btn-outline-secondary mb-3 rounded-pill"><i class="fas fa-print"></i>
            Cetak</a>

        <!-- Form Pencarian -->
        <div class="d-flex justify-content-end mb-3">
            <form method="GET" class="form-inline">
                <div class="input-group" style="width: 100%;">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama siswa..."
                        value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-outline-success"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Data Siswa -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <!-- <th>Jenis Kelamin</th> -->
                    <!-- <th>Alamat</th> -->
                    <th>Kelas</th>
                    <!-- <th>Angkatan</th> -->
                </tr>
            </thead>
            <tbody>
                <?php 
            $i = 1;
            while ($row = $result->fetch_assoc()): ?>

                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= htmlspecialchars($row['nis']); ?></td>
                    <td><?= htmlspecialchars($row['nama']); ?></td>
                    <!-- <td><?= htmlspecialchars($row['jk']); ?></td> -->
                    <!-- <td><?= htmlspecialchars($row['alamat']); ?></td> -->
                    <td><?= htmlspecialchars($row['kelas']); ?></td>
                    <!-- <td><?= htmlspecialchars($row['angkatan']); ?></td> -->
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <!-- Tombol "Previous" -->
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>">❮</a>
                </li>

                <?php
        // Menentukan batas halaman yang ditampilkan
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);

        for ($i = $startPage; $i <= $endPage; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <!-- Tombol "Next" -->
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>">❯</a>
                </li>
            </ul>
        </nav>


    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>



    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>