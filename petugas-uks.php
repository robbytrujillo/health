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
$countQuery = "SELECT COUNT(*) as total FROM tb_petugas $whereClause";
$stmt = $conn->prepare($countQuery);

if (!empty($params)) {
    $stmt->bind_param("s", ...$params);
}

$stmt->execute();
$countResult = $stmt->get_result();
$totalData = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalData / $limit);

// Query untuk mengambil data siswa dengan pagination
$sql = "SELECT * FROM tb_petugas $whereClause LIMIT ?, ?";
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
    <title>Data Petugas</title>
    <link rel="icon" type="image/x-icon" href="assets/images/ihbs-logo-2.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

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
    }

    .main-wrapper {
        flex: 1;
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
    i {
        font-family: 'Poppins', sans-serif;
    }

    .petugas-card {
        border: none;
        border-radius: 20px;
        transition: 0.3s;
        background-color: #F6F8FD;
    }

    .petugas-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .avatar-circle {
        width: 80px;
        height: 80px;
        background: linear-gradient(45deg, #28a745, #20c997);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: auto;
    }

    .avatar-circle i {
        font-size: 32px;
        color: white;
    }
    </style>

    <!-- font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <div class="main-wrapper">
        <div class="container mt-3">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <img src="assets/images/uks1.png" style="width: 110px;">
                <a href="index.php" class="btn btn-outline-success rounded-pill"><i class="fas fa-arrow-left"></i>
                    <b>Kembali</b></a>
            </div>
            <h2 class="text-center mb-5">Daftar Petugas</h2>

            <!-- Tombol Cetak -->
            <!-- <a href="cetak-petugas-uks.php" class="btn btn-outline-secondary mb-3 rounded-pill"><i class="fas fa-print"></i>
                <b>Cetak</b></a> -->

            <!-- Form Pencarian -->
            <!-- <div class="d-flex justify-content-end mb-3">
                <form method="GET" class="form-inline">
                    <div class="input-group" style="width: 100%;">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama petugas..."
                            value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-outline-success">🔍</button>
                        </div>
                    </div>
                </form>
            </div> -->



            <!-- Card Data Petugas -->
            <div class="row">

                <?php 
                    while ($row = $result->fetch_assoc()): 

                        // Format nomor WA (hapus 0 depan → jadi 62)
                    $noWa = preg_replace('/^0/', '62', $row['no_handphone']);
                    $pesan = urlencode("Assalamu'alaikum, saya ingin menghubungi petugas UKS SMA Putra.");
                ?>

                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card petugas-card shadow-sm h-100">

                        <div class="card-body text-center">

                            <!-- Icon / Avatar -->
                            <div class="avatar-circle mb-3">
                                <i class="fas fa-user-md"></i>
                            </div>

                            <h5 class="font-weight-bold mb-1">
                                <?= htmlspecialchars($row['nama_petugas']); ?>
                            </h5>

                            <p class="text-muted mb-2">
                                <?= htmlspecialchars($row['jabatan']); ?>
                            </p>

                            <p class="mb-1">
                                <i class="fas fa-id-card text-success"></i><br>
                                <strong>NIK </strong> <?= htmlspecialchars($row['nip']); ?>
                            </p>

                            <p class="mb-3">
                                <i class="fas fa-phone-square-alt text-success"></i><br>
                                <?= htmlspecialchars($row['no_handphone']); ?>
                            </p>

                            <!-- <hr> -->

                            <!-- Tombol WhatsApp -->
                            <a href="https://wa.me/<?= $noWa ?>?text=<?= $pesan ?>" target="_blank"
                                class="btn btn-success btn-block rounded-pill">
                                <i class="fab fa-whatsapp"></i> Hubungi WhatsApp
                            </a>

                        </div>
                    </div>
                </div>

                <?php endwhile; ?>

            </div>




        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>



    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>