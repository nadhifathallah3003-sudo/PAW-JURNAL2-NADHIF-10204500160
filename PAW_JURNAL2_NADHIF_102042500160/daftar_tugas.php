<?php
// daftar_tugas.php
session_start();
require 'koneksi.php';

$hasil_mk = mysqli_query($conn, "SELECT id, kode, nama FROM mata_kuliah ORDER BY kode ASC");

$cari = trim($_GET['cari'] ?? '');
$filter_mk = (int) ($_GET['mata_kuliah_id'] ?? 0);
$filter_status = trim($_GET['status'] ?? '');

// Bangun query berparameter
$sql = "SELECT t.*, mk.kode AS mk_kode, mk.nama AS mk_nama 
        FROM tugas t 
        JOIN mata_kuliah mk ON t.mata_kuliah_id = mk.id";
$syarat = [];
$params = [];
$types = "";

if ($cari !== '') {
    $syarat[] = "t.judul LIKE ?";
    $params[] = '%' . $cari . '%';
    $types .= "s";
}

if ($filter_mk > 0) {
    $syarat[] = "t.mata_kuliah_id = ?";
    $params[] = $filter_mk;
    $types .= "i";
}

if (in_array($filter_status, ['Draf', 'Aktif', 'Selesai'])) {
    $syarat[] = "t.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if (!empty($syarat)) {
    $sql .= " WHERE " . implode(" AND ", $syarat);
}
$sql .= " ORDER BY t.tenggat ASC, t.id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$hasil = $stmt->get_result();
$total_data = $hasil->num_rows;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Tugas - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body><main>
    <div class="menu">
        <a href="index.php">Beranda</a> |
        <a href="daftar_mata_kuliah.php">Mata Kuliah</a> |
        <a href="daftar_materi.php">Materi</a> |
        <a href="daftar_tugas.php">Tugas</a>
    </div>

    <p class="label">LMS EAD / TUGAS</p>
    <h1>Tugas</h1>
    <p>Menampilkan <?= $total_data ?> data.</p>

    <?php if (isset($_SESSION['pesan'])): ?>
        <p class="pesan"><?= htmlspecialchars($_SESSION['pesan'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['pesan']); ?>
    <?php endif; ?>

    <p><a class="tombol" href="tambah_tugas.php">Tambah tugas</a></p>

    <form method="get" class="pencarian">
        <input name="cari" value="<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8') ?>" placeholder="Judul tugas...">

        <select name="mata_kuliah_id">
            <option value="">Semua mata kuliah</option>
            <?php while ($mk = mysqli_fetch_assoc($hasil_mk)): ?>
                <option value="<?= $mk['id'] ?>" <?= ($filter_mk == $mk['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($mk['kode'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($mk['nama'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endwhile; ?>
        </select>

        <select name="status">
            <option value="">Semua status</option>
            <option value="Draf" <?= ($filter_status === 'Draf') ? 'selected' : '' ?>>Draf</option>
            <option value="Aktif" <?= ($filter_status === 'Aktif') ? 'selected' : '' ?>>Aktif</option>
            <option value="Selesai" <?= ($filter_status === 'Selesai') ? 'selected' : '' ?>>Selesai</option>
        </select>

        <button type="submit">Cari</button>
        <a href="daftar_tugas.php">Reset</a>
    </form>

    <?php if ($total_data == 0): ?>
        <div class="kesalahan">
            <p>Data tidak ditemukan. Coba kata kunci lain.</p>
            <a href="daftar_tugas.php">Reset</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Judul tugas</th>
                    <th>Mata kuliah</th>
                    <th>Tenggat</th>
                    <th>Status</th>
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
                        <td><?= htmlspecialchars($row['tenggat'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <span class="status-badge"><?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?></span>
                        </td>
                        <td>
                            <a href="detail_tugas.php?id=<?= (int)$row['id'] ?>">Detail</a>
                            <a href="edit_tugas.php?id=<?= (int)$row['id'] ?>">Edit</a>
                            <a href="hapus_tugas.php?id=<?= (int)$row['id'] ?>">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main></body>
</html>
