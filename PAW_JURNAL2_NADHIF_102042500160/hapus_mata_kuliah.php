<?php
// hapus_mata_kuliah.php
session_start();
require 'koneksi.php';

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

$id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));

// Ambil data mata kuliah
$stmt = $conn->prepare("SELECT * FROM mata_kuliah WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$mk = $stmt->get_result()->fetch_assoc();

if (!$mk) {
    exit('Mata kuliah tidak ditemukan. Kembali ke daftar mata kuliah.');
}

// Periksa apakah masih dipakai oleh materi atau tugas (ON DELETE RESTRICT)
$stmt_cek = $conn->prepare("SELECT 
    (SELECT COUNT(*) FROM materi WHERE mata_kuliah_id = ?) AS jml_materi,
    (SELECT COUNT(*) FROM tugas WHERE mata_kuliah_id = ?) AS jml_tugas");
$stmt_cek->bind_param("ii", $id, $id);
$stmt_cek->execute();
$relasi = $stmt_cek->get_result()->fetch_assoc();
$masih_dipakai = ($relasi['jml_materi'] > 0 || $relasi['jml_tugas'] > 0);

$pesan_error = '';

if (isset($_POST['hapus'])) {
    // 1. Periksa token CSRF
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $pesan_error = 'ERROR J05 — Token keamanan tidak valid atau telah kedaluwarsa.';
    } elseif ($masih_dipakai) {
        // ERROR J03 — Perlindungan ON DELETE RESTRICT
        $pesan_error = 'Data belum tersimpan. Mata kuliah masih memiliki materi atau tugas. Hapus data terkait terlebih dahulu.';
    } else {
        $stmt_del = $conn->prepare("DELETE FROM mata_kuliah WHERE id = ?");
        $stmt_del->bind_param("i", $id);
        $stmt_del->execute();

        $_SESSION['pesan'] = 'Mata kuliah ' . $mk['nama'] . ' berhasil dihapus.';
        header('Location: daftar_mata_kuliah.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hapus Mata Kuliah - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body><main>
    <div class="menu">
        <a href="index.php">Beranda</a> |
        <a href="daftar_mata_kuliah.php">Mata Kuliah</a> |
        <a href="daftar_materi.php">Materi</a> |
        <a href="daftar_tugas.php">Tugas</a>
    </div>

    <p class="label">LMS EAD / KONFIRMASI</p>
    <h1>Hapus <?= htmlspecialchars($mk['nama'], ENT_QUOTES, 'UTF-8') ?>?</h1>

    <?php if ($masih_dipakai || $pesan_error != ''): ?>
        <!-- ERROR J03 sesuai Gambar 11 di modul -->
        <div class="kesalahan">
            Data belum tersimpan. Mata kuliah masih memiliki materi atau tugas. Hapus data terkait terlebih dahulu.
        </div>
    <?php endif; ?>

    <p>Data baru dihapus setelah tombol konfirmasi dikirim.</p>

    <form method="post">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token'], ENT_QUOTES, 'UTF-8') ?>">

        <?php if ($masih_dipakai): ?>
            <button type="button" class="bahaya" disabled style="opacity: 0.5; cursor: not-allowed;">Ya, hapus data</button>
        <?php else: ?>
            <button name="hapus" class="bahaya">Ya, hapus data</button>
        <?php endif; ?>
        <a href="daftar_mata_kuliah.php">Batal</a>
    </form>
</main></body>
</html>
