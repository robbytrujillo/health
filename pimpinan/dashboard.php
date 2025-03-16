<?php
session_start();
include "../config.php";
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'pimpinan') {
    header("Location: ../login.php");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Pimpinan</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Dashboard Pimpinan</h2>

    <h3>Data Siswa</h3>
    <table class="table">
        <thead>
            <tr>
                <th>NIS</th>
                <th>Nama</th>
                <th>Kelas</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query_siswa = mysqli_query($conn, "SELECT * FROM tb_siswa");
            while ($row = mysqli_fetch_assoc($query_siswa)) {
                echo "<tr>
                        <td>{$row['nis']}</td>
                        <td>{$row['nama']}</td>
                        <td>{$row['kelas']}</td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>

    <h3>Cetak Data</h3>
    <a href="cetak_harian.php" class="btn btn-danger">Cetak Harian</a>
    <a href="cetak_bulanan.php" class="btn btn-success">Cetak Bulanan</a>
</div>
</body>
</html>
