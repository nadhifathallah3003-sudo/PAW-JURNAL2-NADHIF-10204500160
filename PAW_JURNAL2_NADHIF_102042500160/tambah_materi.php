<?php
// tambah_materi.php
session_start();
require 'koneksi.php';

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Ambil daftar mata kuliah untuk opsi select
$hasil_mk = mysqli_query($conn, "SELECT id, kode, nama FROM mata_kuliah ORDER BY kode ASC");

$mata_kuliah_id = (int) ($_GET['mata_kuliah_id'] ?? 0);
$judul = '';
$isi = '';
$tautan = '';
$pesan = '';
$pesan_tautan = '';

if (isset($_POST['simpan'])) {
    // 1. Verifikasi CSRF token
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $pesan = 'ERROR J05 — Token keamanan tidak valid atau telah kedaluwarsa.';
    } else {
        $mata_kuliah_id = (int) ($_POST['mata_kuliah_id'] ?? 0);
        $judul = trim($_POST['judul'] ?? '');
        $isi = trim($_POST['isi'] ?? '');
        $tautan = trim($_POST['tautan'] ?? '');

        // 2. Validasi kolom wajib
        if ($mata_kuliah_id <= 0 || $judul === '' || $isi === '') {
            $pesan = 'Mata kuliah, judul, dan isi materi wajib diisi.';
        } elseif (strlen($judul) < 3 || strlen($judul) > 150) {
            $pesan = 'Judul materi harus 3-150 karakter.';
        } elseif (strlen($isi) < 3 || strlen($isi) > 2000) {
            $pesan = 'Isi materi harus 3-2000 karakter.';
        }
        // 3. Validasi tautan: maks 255 karakter, diawali http:// atau https://
        // ERROR J01 sesuai Gambar 9 di modul
        elseif ($tautan !== '' && (strlen($tautan) > 255 || !preg_match('/^https?:\/\/.+/i', $tautan))) {
            $pesan_tautan = 'Gunakan tautan http/https yang valid.';
        } else {
            // Periksa apakah mata kuliah memang ada di database
            $stmt_cek = $conn->prepare("SELECT id FROM mata_kuliah WHERE id = ?");
            $stmt_cek->bind_param("i", $mata_kuliah_id);
            $stmt_cek->execute();
            if ($stmt_cek->get_result()->num_rows === 0) {
                $pesan = 'Mata kuliah yang dipilih tidak ditemukan.';
            } else {
                // Simpan materi dengan prepared statement
                $stmt_insert = $conn->prepare("INSERT INTO materi (mata_kuliah_id, judul, isi, tautan) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("isss", $mata_kuliah_id, $judul, $isi, $tautan);
                $stmt_insert->execute();

                $_SESSION['pesan'] = 'Materi "' . $judul . '" berhasil ditambahkan.';
                header('Location: daftar_materi.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Materi - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body><main>
    <div class="menu">
        <a href="index.php">Beranda</a> |
        <a href="daftar_mata_kuliah.php">Mata Kuliah</a> |
        <a href="daftar_materi.php">Materi</a> |
        <a href="daftar_tugas.php">Tugas</a>
    </div>

    <p class="label">LMS EAD / TAMBAH MATERI</p>
    <h1>Tambah materi</h1>
    <p><a href="daftar_materi.php">Kembali ke daftar</a></p>

    <?php if ($pesan != ''): ?>
        <div class="kesalahan"><?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token'], ENT_QUOTES, 'UTF-8') ?>">

        <label for="mata_kuliah_id">Mata kuliah</label>
        <select id="mata_kuliah_id" name="mata_kuliah_id" required>
            <option value="">Pilih Mata kuliah</option>
            <?php while ($mk = mysqli_fetch_assoc($hasil_mk)): ?>
                <option value="<?= $mk['id'] ?>" <?= ($mata_kuliah_id == $mk['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($mk['kode'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($mk['nama'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="judul">Judul materi</label>
        <input id="judul" name="judul" maxlength="150" value="<?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: Transaksi database" required>

        <label for="isi">Isi materi</label>
        <textarea id="isi" name="isi" rows="6" placeholder="Tuliskan isi materi..." required><?= htmlspecialchars($isi, ENT_QUOTES, 'UTF-8') ?></textarea>

        <label for="tautan">Tautan referensi</label>
        <input id="tautan" name="tautan" maxlength="255" value="<?= htmlspecialchars($tautan, ENT_QUOTES, 'UTF-8') ?>" placeholder="https://...">
        <?php if ($pesan_tautan != ''): ?>
            <!-- Pesan error J01 sesuai Gambar 9 modul -->
            <div class="pesan-field"><?= htmlspecialchars($pesan_tautan, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <button name="simpan">Simpan data</button>
        <a href="daftar_materi.php">Batal</a>
    </form>
</main></body>
</html>
