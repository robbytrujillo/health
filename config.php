<?php
$host = "localhost";
$user = "root"; // Sesuaikan dengan username database
$pass = ""; // Sesuaikan dengan password database
$db   = "uks"; // Nama database

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
