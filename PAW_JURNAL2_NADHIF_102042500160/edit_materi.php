<?php
// edit_materi.php
session_start();
require 'koneksi.php';

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

$id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));

// Ambil data materi lama
$stmt = $conn->prepare("SELECT * FROM materi WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$materi = $stmt->get_result()->fetch_assoc();

if (!$materi) {
    exit('Materi tidak ditemukan. Kembali ke daftar materi.');
}

$hasil_mk = mysqli_query($conn, "SELECT id, kode, nama FROM mata_kuliah ORDER BY kode ASC");

$mata_kuliah_id = $materi['mata_kuliah_id'];
$judul = $materi['judul'];
$isi = $materi['isi'];
$tautan = $materi['tautan'] ?? '';
$pesan = '';
$pesan_tautan = '';

if (isset($_POST['simpan'])) {
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $pesan = 'ERROR J05 — Token keamanan tidak valid atau telah kedaluwarsa.';
    } else {
        $new_mk_id = (int) ($_POST['mata_kuliah_id'] ?? 0);
        $new_judul = trim($_POST['judul'] ?? '');
        $new_isi = trim($_POST['isi'] ?? '');
        $new_tautan = trim($_POST['tautan'] ?? '');

        // Periksa apakah tidak ada data yang berubah
        if ($new_mk_id === (int)$materi['mata_kuliah_id'] && $new_judul === $materi['judul'] && $new_isi === $materi['isi'] && $new_tautan === (string)$materi['tautan']) {
            $_SESSION['pesan'] = 'Tidak ada perubahan data.';
            header('Location: daftar_materi.php');
            exit;
        }

        if ($new_mk_id <= 0 || $new_judul === '' || $new_isi === '') {
            $pesan = 'Mata kuliah, judul, dan isi materi wajib diisi.';
        } elseif (strlen($new_judul) < 3 || strlen($new_judul) > 150) {
            $pesan = 'Judul materi harus 3-150 karakter.';
        } elseif (strlen($new_isi) < 3 || strlen($new_isi) > 2000) {
            $pesan = 'Isi materi harus 3-2000 karakter.';
        } elseif ($new_tautan !== '' && (strlen($new_tautan) > 255 || !preg_match('/^https?:\/\/.+/i', $new_tautan))) {
            $pesan_tautan = 'Gunakan tautan http/https yang valid.';
        } else {
            $stmt_up = $conn->prepare("UPDATE materi SET mata_kuliah_id = ?, judul = ?, isi = ?, tautan = ? WHERE id = ?");
            $stmt_up->bind_param("isssi", $new_mk_id, $new_judul, $new_isi, $new_tautan, $id);
            $stmt_up->execute();

            $_SESSION['pesan'] = 'Data materi "' . $new_judul . '" berhasil diperbarui.';
            header('Location: daftar_materi.php');
            exit;
        }

        $mata_kuliah_id = $new_mk_id;
        $judul = $new_judul;
        $isi = $new_isi;
        $tautan = $new_tautan;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Materi - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body><main>
    <div class="menu">
        <a href="index.php">Beranda</a> |
        <a href="daftar_mata_kuliah.php">Mata Kuliah</a> |
        <a href="daftar_materi.php">Materi</a> |
        <a href="daftar_tugas.php">Tugas</a>
    </div>

    <p class="label">LMS EAD / EDIT MATERI</p>
    <h1>Edit materi</h1>
    <p><a href="daftar_materi.php">Kembali ke daftar</a></p>

    <?php if ($pesan != ''): ?>
        <div class="kesalahan"><?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?= $id ?>">
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
        <input id="judul" name="judul" maxlength="150" value="<?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?>" required>

        <label for="isi">Isi materi</label>
        <textarea id="isi" name="isi" rows="6" required><?= htmlspecialchars($isi, ENT_QUOTES, 'UTF-8') ?></textarea>

        <label for="tautan">Tautan referensi</label>
        <input id="tautan" name="tautan" maxlength="255" value="<?= htmlspecialchars($tautan, ENT_QUOTES, 'UTF-8') ?>">
        <?php if ($pesan_tautan != ''): ?>
            <div class="pesan-field"><?= htmlspecialchars($pesan_tautan, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <button name="simpan">Simpan data</button>
        <a href="daftar_materi.php">Batal</a>
    </form>
</main></body>
</html>
