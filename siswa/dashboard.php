<?php
session_start();
include "../config.php";
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../login.php");
}
$nis = $_SESSION['user'];
$query = mysqli_query($conn, "SELECT * FROM tb_siswa WHERE nis='$nis'");
$data = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Siswa</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Selamat datang, <?php echo $data['nama']; ?></h2>
    <h5>NIS: <?php echo $data['nis']; ?></h5>
    
    <h3>Data Sakit</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Keluhan</th>
                <th>Diagnosa</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query_sakit = mysqli_query($conn, "SELECT * FROM tb_sakit WHERE nis='$nis'");
            while ($row = mysqli_fetch_assoc($query_sakit)) {
                echo "<tr>
                        <td>{$row['tgl_sakit']}</td>
                        <td>{$row['keluhan']}</td>
                        <td>{$row['diagnosa']}</td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>

    <h3>QR Code</h3>
    <img src="../libraries/qrcode.php?text=<?php echo $nis; ?>" width="150px">
    
    <br><br>
    <a href="cetak_pdf.php" class="btn btn-danger">Cetak PDF</a>
    <a href="cetak_excel.php" class="btn btn-success">Cetak Excel</a>
</div>
</body>
</html>
