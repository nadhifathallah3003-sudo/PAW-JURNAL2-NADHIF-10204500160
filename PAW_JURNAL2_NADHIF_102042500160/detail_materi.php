<?php
// detail_materi.php
require 'koneksi.php';

$id = (int) ($_GET['id'] ?? 0);

// Ambil data materi dan nama mata kuliahnya
$stmt = $conn->prepare("SELECT m.*, mk.kode AS mk_kode, mk.nama AS mk_nama 
                        FROM materi m 
                        JOIN mata_kuliah mk ON m.mata_kuliah_id = mk.id 
                        WHERE m.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$materi = $stmt->get_result()->fetch_assoc();

if (!$materi) {
    exit('Materi tidak ditemukan. Kembali ke daftar materi.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Materi - LMS EAD</title>
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
    <h1><?= htmlspecialchars($materi['judul'], ENT_QUOTES, 'UTF-8') ?></h1>

    <!-- Detail materi sesuai Gambar 5 di modul -->
    <dl>
        <dt>Mata kuliah</dt>
        <dd>
            <?= htmlspecialchars($materi['mk_kode'], ENT_QUOTES, 'UTF-8') ?> - 
            <?= htmlspecialchars($materi['mk_nama'], ENT_QUOTES, 'UTF-8') ?>
        </dd>

        <dt>Judul materi</dt>
        <dd><?= htmlspecialchars($materi['judul'], ENT_QUOTES, 'UTF-8') ?></dd>

        <dt>Isi materi</dt>
        <dd><?= nl2br(htmlspecialchars($materi['isi'], ENT_QUOTES, 'UTF-8')) ?></dd>

        <dt>Tautan referensi</dt>
        <dd>
            <?php if (!empty($materi['tautan'])): ?>
                <a href="<?= htmlspecialchars($materi['tautan'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                    <?= htmlspecialchars($materi['tautan'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php else: ?>
                -
            <?php endif; ?>
        </dd>
    </dl>

    <p style="margin-top: 24px;">
        <a class="tombol" href="edit_materi.php?id=<?= $id ?>">Edit data</a>
        <a href="daftar_materi.php">Kembali</a>
    </p>
</main></body>
</html>
