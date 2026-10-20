<?php
// detail_tugas.php
require 'koneksi.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT t.*, mk.kode AS mk_kode, mk.nama AS mk_nama 
                        FROM tugas t 
                        JOIN mata_kuliah mk ON t.mata_kuliah_id = mk.id 
                        WHERE t.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$tugas = $stmt->get_result()->fetch_assoc();

if (!$tugas) {
    exit('Tugas tidak ditemukan. Kembali ke daftar tugas.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Tugas - LMS EAD</title>
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
    <h1><?= htmlspecialchars($tugas['judul'], ENT_QUOTES, 'UTF-8') ?></h1>

    <dl>
        <dt>Mata kuliah</dt>
        <dd>
            <?= htmlspecialchars($tugas['mk_kode'], ENT_QUOTES, 'UTF-8') ?> - 
            <?= htmlspecialchars($tugas['mk_nama'], ENT_QUOTES, 'UTF-8') ?>
        </dd>

        <dt>Status</dt>
        <dd>
            <span class="status-badge"><?= htmlspecialchars($tugas['status'], ENT_QUOTES, 'UTF-8') ?></span>
        </dd>

        <dt>Tenggat</dt>
        <dd><?= htmlspecialchars($tugas['tenggat'], ENT_QUOTES, 'UTF-8') ?></dd>

        <dt>Judul tugas</dt>
        <dd><?= htmlspecialchars($tugas['judul'], ENT_QUOTES, 'UTF-8') ?></dd>

        <dt>Instruksi tugas</dt>
        <dd><?= nl2br(htmlspecialchars($tugas['instruksi'], ENT_QUOTES, 'UTF-8')) ?></dd>
    </dl>

    <p style="margin-top: 24px;">
        <a class="tombol" href="edit_tugas.php?id=<?= $id ?>">Edit data</a>
        <a href="daftar_tugas.php">Kembali</a>
    </p>
</main></body>
</html>
