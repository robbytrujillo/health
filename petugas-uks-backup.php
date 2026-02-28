<!-- Tabel Data Siswa -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th>No</th>
            <th>NIP</th>
            <th>Nama</th>
            <th>Jabatan</th>
            <th>No Telepon</th>
            <!-- <th>Kelas</th> -->
            <!-- <th>Angkatan</th> -->
        </tr>
    </thead>
    <tbody>
        <?php 
            $i = 1;
            while ($row = $result->fetch_assoc()): ?>

        <tr>
            <td><?= $i++; ?></td>
            <td><?= htmlspecialchars($row['nip']); ?></td>
            <td><?= htmlspecialchars($row['nama_petugas']); ?></td>
            <td><?= htmlspecialchars($row['jabatan']); ?></td>
            <td><?= htmlspecialchars($row['no_handphone']); ?></td>
            <!-- <td><?= htmlspecialchars($row['kelas']); ?></td> -->
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