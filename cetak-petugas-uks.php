<?php 
include 'config.php';

// Ambil Data Kedatangan menggunakan MySQLi
$query = "SELECT * FROM tb_petugas";
$result = mysqli_query($conn, $query);
?>

<html>
<head>
  <title>Data Siswa</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.dataTables.min.css">

  <!-- Pastikan jQuery hanya dipanggil satu kali -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light container sticky-top">
    <img src="assets/images/uks1.png" style="width: 110px; margin-left: 0%; margin-top: 0%">
</nav>

<div class="container mt-3 mb-3">
    <h4 class="mt-3 mb-3 text-center">Data Petugas</h4>
    <a href="petugas-uks.php" class="btn btn-outline-success rounded-pill">🔙 <b>Kembali</b></a>
    <br><br>

    <div class="data-tables datatable-dark">
        <table class="table table-bordered" id="mauexport" width="100%" cellspacing="0">                                       
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIP</th>
                    <th>Nama Petugas</th>
                    <th>Jabatan</th>
                    <th>No Telepon</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                while ($data = mysqli_fetch_assoc($result)) {                                          
                    $nip = $data['nip'];
                    $nama_petugas = $data['nama_petugas'];
                    $jabatan = $data['jabatan'];
                    $no_handphone = $data['no_handphone'];
                ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?php echo $nip; ?></td>
                    <td><?php echo $nama_petugas; ?></td>
                    <td><?php echo $jabatan; ?></td>
                    <td><?php echo $no_handphone; ?></td>
                </tr>   
                <?php 
                };
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- **Pastikan urutan script benar** -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    $('#mauexport').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        paging: true,      // Aktifkan pagination
        searching: true,   // Aktifkan fitur pencarian
        ordering: true     // Aktifkan fitur sorting
    });
});
</script>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

</body>
</html>
