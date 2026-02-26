<?php
session_start();
require 'config.php'; // Pastikan ini adalah file koneksi ke database
require 'phpqrcode/qrlib.php'; // Pastikan ini adalah pustaka untuk membuat QR Code

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index | UKS Management</title>
    <link rel="icon" type="image/x-icon" href="assets/images/ihbs-logo-2.png">

    <!-- Bootstrap 4 CDN -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style-index.css">
    <link rel="stylesheet" href="assets/css/style-hoverzoom.css">
</head>

<body>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <img src="assets/images/uks1.png" style="width: 110px;">
            <a href="login.php" class="btn btn-outline-success rounded-pill">🔐 <b>Login</b></a>
        </div>

        <!-- Search Box -->
        <form method="GET" class="search-box">
            <div class="input-group mb-4">
                <input type="text" name="nama_siswa" class="form-control" placeholder="🔍 Cari siswa sakit..." required>
                <div class="input-group-append">
                    <button class="btn btn-success" type="submit">Cari</button>
                </div>
            </div>
        </form>

        <div class="row">
            <!-- Bagian Daftar Siswa -->
            <div class="col-md-9">
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
                        $qr_data = "Data Siswa: $nama_siswa\n\nDaftar Siswa:\n";

                        echo "<div class='col-12'>
                                <div class='alert alert-success'><strong>Siswa \"$nama_siswa\"</strong> ditemukan, berikut adalah data siswanya:</div>
                              </div>";

                        while ($row = $result->fetch_assoc()) {
                            echo "<div class='col-md-4 mb-4'>
                                    <div class='card'>
                                        <div class='card-body text-center'>
                                            <h5 class='card-title'>{$row['nama']}</h5>
                                            <p class='card-text'><strong>NIS: </strong>{$row['nis']}</p>
                                            <p class='card-text'><strong>Kelas: </strong>{$row['kelas']}</p>
                                            <p class='card-text'><strong>Tanggal Sakit: </strong>{$row['tgl_sakit']}</p>
                                            <p class='card-text'><strong>Tekanan Darah: </strong>{$row['tekanan_darah']}</p>
                                            <p class='card-text'><strong>Suhu: </strong>{$row['suhu']}</p>
                                            <p class='card-text'><strong>Keluhan: </strong>{$row['keluhan']}</p>
                                            <p class='card-text'><strong>Diagnosa: </strong>{$row['diagnosa']}</p>
                                            <p class='card-text'><strong>Penanganan: </strong>{$row['penanganan']}</p>
                                        </div>
                                    </div>
                                  </div>";

                            // Tambahkan data siswa ke dalam QR Code
                            $qr_data .= "- {$row['nama']} (NIS: {$row['nis']},Kelas: {$row['kelas']},Tanggal Sakit: {$row['tgl_sakit']},Tekanan Darah: {$row['tekanan_darah']},Suhu: {$row['suhu']},Keluhan: {$row['keluhan']},Diagnosa: {$row['diagnosa']},Penanganan: {$row['penanganan']})\n\n";
                        }

                        // Buat folder qrcodes jika belum ada
                        if (!file_exists("qrcodes")) {
                            mkdir("qrcodes", 0777, true);
                        }

                        // Buat QR Code
                        $qr_filename = "qrcodes/" . md5($nama_siswa) . ".png";
                        QRcode::png($qr_data, $qr_filename, QR_ECLEVEL_L, 5);
                    } else {
                        echo "<div class='col-12'><div class='alert alert-danger'>Siswa tidak ditemukan.</div></div>";
                    }

                    $stmt->close();
                }
                ?>
                </div>
            </div>

            <!-- Bagian QR Code -->
            <?php if (isset($qr_filename)) : ?>
            <div class="col-md-3 text-center">
                <div class="card p-4">
                    <h5>QR Code Siswa Sakit:</h5>
                    <img id="qrImage" src="<?= $qr_filename ?>" alt="QR Code Siswa">
                    <button class="btn btn-success mt-3" onclick="printQRCode('<?= $qr_filename ?>')">🖨 Cetak QR
                        Code</button>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Bagian Navigasi -->
        <div class="row">
            <div class="col-md-4">
                <div class="card bg-light mb-3 shadow-sm rounded-lg border-0">
                    <div class="card-body text-center">
                        <img src="assets/images/student.svg" style="height: 320px" class="cover img-fluid">
                        <h5 class="mt-3 mb-3">Melihat Data Siswa</h5>
                        <a href="siswa-user.php"
                            class="btn btn-outline-success btn-block font-weight-bold rounded-pill">Lihat Data</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-light mb-3 shadow-sm rounded-lg border-0">
                    <div class="card-body text-center">
                        <img src="assets/images/sick.svg" style="height: 320px" class="cover img-fluid">
                        <h5 class="mt-3 mb-3">Melihat Siswa Sakit</h5>
                        <a href="siswa-sakit.php"
                            class="btn btn-outline-success btn-block font-weight-bold rounded-pill">Lihat Data</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-light mb-3 shadow-sm rounded-lg border-0">
                    <div class="card-body text-center">
                        <img src="assets/images/petugas2.svg" style="height: 320px" class="cover img-fluid">
                        <h5 class="mt-3 mb-3">Melihat Data Petugas</h5>
                        <a href="petugas-uks.php"
                            class="btn btn-outline-success btn-block font-weight-bold rounded-pill">Lihat Data</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap 4 JS & jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
    function printQRCode(url) {
        var qrWindow = window.open('', '_blank');
        qrWindow.document.write('<html><head><title>Cetak QR Code</title></head><body>');
        qrWindow.document.write('<img src="' + url + '" style="width:750px;">');
        qrWindow.document.write('<script>window.onload = function() { window.print(); window.close(); }<' + '/script>');
        qrWindow.document.write('</body></html>');
        qrWindow.document.close();
    }
    </script>

</body>

</html>