<?php
// hapus_materi.php
session_start();
require 'koneksi.php';

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

$id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));

$stmt = $conn->prepare("SELECT * FROM materi WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$materi = $stmt->get_result()->fetch_assoc();

if (!$materi) {
    exit('Materi tidak ditemukan. Kembali ke daftar materi.');
}

if (isset($_POST['hapus'])) {
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        exit('ERROR J05 — Token keamanan tidak valid.');
    }

    $stmt_del = $conn->prepare("DELETE FROM materi WHERE id = ?");
    $stmt_del->bind_param("i", $id);
    $stmt_del->execute();

    $_SESSION['pesan'] = 'Materi "' . $materi['judul'] . '" berhasil dihapus.';
    header('Location: daftar_materi.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hapus Materi - LMS EAD</title>
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
    <h1>Hapus materi?</h1>
    <p>"<?= htmlspecialchars($materi['judul'], ENT_QUOTES, 'UTF-8') ?>" akan dihapus.</p>
    <p>Data baru dihapus setelah tombol konfirmasi dikirim.</p>

    <form method="post">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token'], ENT_QUOTES, 'UTF-8') ?>">

        <button name="hapus" class="bahaya">Ya, hapus data</button>
        <a href="daftar_materi.php">Batal</a>
    </form>
</main></body>
</html>
