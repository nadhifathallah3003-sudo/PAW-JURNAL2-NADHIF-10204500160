<?php
// detail_mata_kuliah.php
require 'koneksi.php';

$id = (int) ($_GET['id'] ?? 0);

// Prepared statement membaca mata kuliah berdasarkan ID
$stmt = $conn->prepare("SELECT * FROM mata_kuliah WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$mk = $stmt->get_result()->fetch_assoc();

if (!$mk) {
    exit('Mata kuliah tidak ditemukan. Kembali ke daftar mata kuliah.');
}

// Ambil materi terkait
$stmt_mat = $conn->prepare("SELECT id, judul FROM materi WHERE mata_kuliah_id = ? ORDER BY id ASC");
$stmt_mat->bind_param("i", $id);
$stmt_mat->execute();
$materi_terkait = $stmt_mat->get_result();

// Ambil tugas terkait
$stmt_tug = $conn->prepare("SELECT id, judul, tenggat, status FROM tugas WHERE mata_kuliah_id = ? ORDER BY tenggat ASC");
$stmt_tug->bind_param("i", $id);
$stmt_tug->execute();
$tugas_terkait = $stmt_tug->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Mata Kuliah - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body><main>
    <div class="menu">
        <a href="index.php">Beranda</a> |
        <a href="daftar_mata_kuliah.php">Mata Kuliah</a> |
        <a href="daftar_materi.php">Materi</a> |
        <a href="daftar_tugas.php">Tugas</a>
    </div>

    <p class="label">LMS EAD / DETAIL</p>
    <h1><?= htmlspecialchars($mk['nama'], ENT_QUOTES, 'UTF-8') ?></h1>

    <dl>
        <dt>Kode</dt>
        <dd><?= htmlspecialchars($mk['kode'], ENT_QUOTES, 'UTF-8') ?></dd>

        <dt>Nama mata kuliah</dt>
        <dd><?= htmlspecialchars($mk['nama'], ENT_QUOTES, 'UTF-8') ?></dd>

        <dt>Dosen pengampu</dt>
        <dd><?= htmlspecialchars($mk['dosen'], ENT_QUOTES, 'UTF-8') ?></dd>

        <dt>Semester</dt>
        <dd>Semester <?= (int)$mk['semester'] ?></dd>
    </dl>

    <h2>Materi Pembelajaran</h2>
    <?php if ($materi_terkait->num_rows > 0): ?>
        <ul>
            <?php while ($m = $materi_terkait->fetch_assoc()): ?>
                <li>
                    <a href="detail_materi.php?id=<?= $m['id'] ?>">
                        <?= htmlspecialchars($m['judul'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>Belum ada materi untuk mata kuliah ini.</p>
    <?php endif; ?>

    <h2>Tugas Terkait</h2>
    <?php if ($tugas_terkait->num_rows > 0): ?>
        <ul>
            <?php while ($t = $tugas_terkait->fetch_assoc()): ?>
                <li>
                    <a href="detail_tugas.php?id=<?= $t['id'] ?>">
                        <?= htmlspecialchars($t['judul'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                    (Tenggat: <?= htmlspecialchars($t['tenggat'], ENT_QUOTES, 'UTF-8') ?> | Status: <?= htmlspecialchars($t['status'], ENT_QUOTES, 'UTF-8') ?>)
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>Belum ada tugas untuk mata kuliah ini.</p>
    <?php endif; ?>

    <p style="margin-top: 24px;">
        <a class="tombol" href="edit_mata_kuliah.php?id=<?= $id ?>">Edit data</a>
        <a class="tombol bahaya" href="hapus_mata_kuliah.php?id=<?= $id ?>">Hapus</a>
        <a href="daftar_mata_kuliah.php">Kembali</a>
    </p>
</main></body>
</html>
