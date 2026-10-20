<?php
// daftar_mata_kuliah.php
session_start();
require 'koneksi.php';

$cari = '';
if (isset($_GET['cari'])) {
    $cari = trim($_GET['cari']);
}

// Gunakan prepared statement untuk pencarian aman (bagian Memahami Query Berparameter)
if ($cari !== '') {
    $param = '%' . $cari . '%';
    $stmt = $conn->prepare("SELECT * FROM mata_kuliah WHERE kode LIKE ? OR nama LIKE ? ORDER BY kode ASC");
    $stmt->bind_param("ss", $param, $param);
} else {
    $stmt = $conn->prepare("SELECT * FROM mata_kuliah ORDER BY kode ASC");
}

$stmt->execute();
$hasil = $stmt->get_result();
$total_data = $hasil->num_rows;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Mata Kuliah - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body><main>
    <div class="menu">
        <a href="index.php">Beranda</a> |
        <a href="daftar_mata_kuliah.php">Mata Kuliah</a> |
        <a href="daftar_materi.php">Materi</a> |
        <a href="daftar_tugas.php">Tugas</a>
    </div>

    <p class="label">LMS EAD / MATA KULIAH</p>
    <h1>Mata kuliah</h1>
    <p>Menampilkan <?= $total_data ?> data.</p>

    <?php if (isset($_SESSION['pesan'])): ?>
        <p class="pesan"><?= htmlspecialchars($_SESSION['pesan'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['pesan']); ?>
    <?php endif; ?>

    <p><a class="tombol" href="tambah_mata_kuliah.php">Tambah mata kuliah</a></p>

    <!-- Form pencarian kode atau nama -->
    <form method="get" class="pencarian">
        <label for="cari" style="margin-top: 0; display: inline;">Cari:</label>
        <input id="cari" name="cari" value="<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8') ?>" placeholder="Kode atau nama...">
        <button type="submit">Cari</button>
        <a href="daftar_mata_kuliah.php">Reset</a>
    </form>

    <?php if ($total_data == 0): ?>
        <div class="kesalahan">
            <p>Data tidak ditemukan. Coba kata kunci lain.</p>
            <a href="daftar_mata_kuliah.php">Reset</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Mata kuliah</th>
                    <th>Dosen / semester</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $hasil->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($row['kode'], ENT_QUOTES, 'UTF-8') ?></strong> - 
                            <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['dosen'], ENT_QUOTES, 'UTF-8') ?> / Semester <?= (int)$row['semester'] ?>
                        </td>
                        <td>
                            <a href="detail_mata_kuliah.php?id=<?= (int)$row['id'] ?>">Detail</a>
                            <a href="edit_mata_kuliah.php?id=<?= (int)$row['id'] ?>">Edit</a>
                            <a href="hapus_mata_kuliah.php?id=<?= (int)$row['id'] ?>">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main></body>
</html>
