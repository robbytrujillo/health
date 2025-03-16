<?php
session_start();
require 'config.php'; // Koneksi ke database

if (isset($_POST['login'])) {
    $nis = trim($_POST['username']); // Menggunakan NIS sebagai username
    $password = trim($_POST['password']);

    if (!empty($nis) && !empty($password)) {
        $query = $conn->prepare("SELECT * FROM tb_siswa WHERE nis = ?");
        $query->bind_param("s", $nis);
        $query->execute();
        $result = $query->get_result();
        
        if ($result->num_rows == 1) {
            $siswa = $result->fetch_assoc();
            if (password_verify($password, $siswa['password'])) {
                $_SESSION['siswa'] = $siswa;
                header("Location: siswa/dashboard.php");
                exit;
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "NIS tidak ditemukan!";
        }
    } else {
        $error = "NIS dan password harus diisi!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Login | UKS</title>
    <link rel="icon" type="image/x-icon" href="assets/images/ihbs-logo-2.png">
    <link href="assets/css/styles.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="bg-light">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <br><br>
                <div class="container mt-5">
                    <div class="row justify-content-center">
                        <div class="col-lg-4">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <img src="assets/images/uks1.png" style="width: 150px; margin-left: 30%; margin-top: 5%">
                                <div class="card-body">
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger text-center"><?= $error ?></div>
                                    <?php endif; ?>
                                    <form method="post">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="username" id="inputUsername" type="text" placeholder="NIS" required>
                                            <label class="small mb-1" for="inputUsername">Username</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="password" id="inputPassword" type="password" placeholder="Password" required>
                                            <label class="small mb-1" for="inputPassword">Password</label>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                            <button class="btn btn-success btn-lg btn-block rounded-pill" type="submit" name="login">
                                                🔐 <b>Log In</b>
                                            </button>
                                            <a href="index.php" class="btn btn-outline-success btn-lg rounded-pill"><b>Kembali</b></a>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center py-3">
                                    <div class="small">Copyright &copy; IT Development IHBS 2025</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>
