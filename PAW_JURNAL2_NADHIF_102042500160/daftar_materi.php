<?php
// daftar_materi.php
session_start();
require 'koneksi.php';

// Ambil daftar mata kuliah untuk opsi filter
$hasil_mk = mysqli_query($conn, "SELECT id, kode, nama FROM mata_kuliah ORDER BY kode ASC");

$cari = trim($_GET['cari'] ?? '');
$filter_mk = (int) ($_GET['mata_kuliah_id'] ?? 0);

// Bangun query berparameter
if ($cari !== '' && $filter_mk > 0) {
    $param_cari = '%' . $cari . '%';
    $stmt = $conn->prepare("SELECT m.*, mk.kode AS mk_kode, mk.nama AS mk_nama 
                            FROM materi m 
                            JOIN mata_kuliah mk ON m.mata_kuliah_id = mk.id 
                            WHERE m.judul LIKE ? AND m.mata_kuliah_id = ? 
                            ORDER BY m.id DESC");
    $stmt->bind_param("si", $param_cari, $filter_mk);
} elseif ($cari !== '') {
    $param_cari = '%' . $cari . '%';
    $stmt = $conn->prepare("SELECT m.*, mk.kode AS mk_kode, mk.nama AS mk_nama 
                            FROM materi m 
                            JOIN mata_kuliah mk ON m.mata_kuliah_id = mk.id 
                            WHERE m.judul LIKE ? 
                            ORDER BY m.id DESC");
    $stmt->bind_param("s", $param_cari);
} elseif ($filter_mk > 0) {
    $stmt = $conn->prepare("SELECT m.*, mk.kode AS mk_kode, mk.nama AS mk_nama 
                            FROM materi m 
                            JOIN mata_kuliah mk ON m.mata_kuliah_id = mk.id 
                            WHERE m.mata_kuliah_id = ? 
                            ORDER BY m.id DESC");
    $stmt->bind_param("i", $filter_mk);
} else {
    $stmt = $conn->prepare("SELECT m.*, mk.kode AS mk_kode, mk.nama AS mk_nama 
                            FROM materi m 
                            JOIN mata_kuliah mk ON m.mata_kuliah_id = mk.id 
                            ORDER BY m.id DESC");
}

$stmt->execute();
$hasil = $stmt->get_result();
$total_data = $hasil->num_rows;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Materi - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body><main>
    <div class="menu">
        <a href="index.php">Beranda</a> |
        <a href="daftar_mata_kuliah.php">Mata Kuliah</a> |
        <a href="daftar_materi.php">Materi</a> |
        <a href="daftar_tugas.php">Tugas</a>
    </div>

    <p class="label">LMS EAD / MATERI</p>
    <h1>Materi</h1>
    <p>Menampilkan <?= $total_data ?> data.</p>

    <?php if (isset($_SESSION['pesan'])): ?>
        <p class="pesan"><?= htmlspecialchars($_SESSION['pesan'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['pesan']); ?>
    <?php endif; ?>

    <p><a class="tombol" href="tambah_materi.php">Tambah materi</a></p>

    <!-- Toolbar pencarian dan filter mata kuliah (Gambar 7) -->
    <form method="get" class="pencarian">
        <input name="cari" value="<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8') ?>" placeholder="Kata kunci judul...">
        
        <select name="mata_kuliah_id">
            <option value="">Semua mata kuliah</option>
            <?php while ($mk = mysqli_fetch_assoc($hasil_mk)): ?>
                <option value="<?= $mk['id'] ?>" <?= ($filter_mk == $mk['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($mk['kode'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($mk['nama'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Cari</button>
        <a href="daftar_materi.php">Reset</a>
    </form>

    <?php if ($total_data == 0): ?>
        <div class="kesalahan">
            <p>Data tidak ditemukan. Coba kata kunci lain.</p>
            <a href="daftar_materi.php">Reset</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Mata kuliah</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $hasil->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['judul'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <strong><?= htmlspecialchars($row['mk_kode'], ENT_QUOTES, 'UTF-8') ?></strong> - 
                            <?= htmlspecialchars($row['mk_nama'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td>
                            <a href="detail_materi.php?id=<?= (int)$row['id'] ?>">Detail</a>
                            <a href="edit_materi.php?id=<?= (int)$row['id'] ?>">Edit</a>
                            <a href="hapus_materi.php?id=<?= (int)$row['id'] ?>">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main></body>
</html>
