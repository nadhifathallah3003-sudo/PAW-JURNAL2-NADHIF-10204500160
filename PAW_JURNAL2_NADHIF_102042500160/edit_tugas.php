<?php
// edit_tugas.php
session_start();
require 'koneksi.php';

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

$id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));

$stmt = $conn->prepare("SELECT * FROM tugas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$tugas = $stmt->get_result()->fetch_assoc();

if (!$tugas) {
    exit('Tugas tidak ditemukan. Kembali ke daftar tugas.');
}

$hasil_mk = mysqli_query($conn, "SELECT id, kode, nama FROM mata_kuliah ORDER BY kode ASC");

$mata_kuliah_id = $tugas['mata_kuliah_id'];
$judul = $tugas['judul'];
$instruksi = $tugas['instruksi'];
$tenggat = $tugas['tenggat'];
$status = $tugas['status'];
$pesan = '';

if (isset($_POST['simpan'])) {
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $pesan = 'ERROR J05 — Token keamanan tidak valid atau telah kedaluwarsa.';
    } else {
        $new_mk_id = (int) ($_POST['mata_kuliah_id'] ?? 0);
        $new_judul = trim($_POST['judul'] ?? '');
        $new_instruksi = trim($_POST['instruksi'] ?? '');
        $new_tenggat = trim($_POST['tenggat'] ?? '');
        $new_status = trim($_POST['status'] ?? '');

        // Periksa apakah tidak ada perubahan data
        if ($new_mk_id === (int)$tugas['mata_kuliah_id'] && $new_judul === $tugas['judul'] && $new_instruksi === $tugas['instruksi'] && $new_tenggat === $tugas['tenggat'] && $new_status === $tugas['status']) {
            $_SESSION['pesan'] = 'Tidak ada perubahan data.';
            header('Location: daftar_tugas.php');
            exit;
        }

        $cek_tanggal = DateTime::createFromFormat('Y-m-d', $new_tenggat);
        $tanggal_valid = ($cek_tanggal && $cek_tanggal->format('Y-m-d') === $new_tenggat);

        if ($new_mk_id <= 0 || $new_judul === '' || $new_instruksi === '' || $new_tenggat === '' || $new_status === '') {
            $pesan = 'Semua kolom wajib diisi.';
        } elseif (strlen($new_judul) < 3 || strlen($new_judul) > 150) {
            $pesan = 'Judul tugas harus 3-150 karakter.';
        } elseif (strlen($new_instruksi) < 3 || strlen($new_instruksi) > 2000) {
            $pesan = 'Instruksi tugas harus 3-2000 karakter.';
        } elseif (!$tanggal_valid) {
            $pesan = 'Pilih tanggal tenggat yang valid (format: YYYY-MM-DD).';
        } elseif (!in_array($new_status, ['Draf', 'Aktif', 'Selesai'])) {
            $pesan = 'Status hanya boleh Draf, Aktif, atau Selesai.';
        } else {
            $stmt_up = $conn->prepare("UPDATE tugas SET mata_kuliah_id = ?, judul = ?, instruksi = ?, tenggat = ?, status = ? WHERE id = ?");
            $stmt_up->bind_param("issssi", $new_mk_id, $new_judul, $new_instruksi, $new_tenggat, $new_status, $id);
            $stmt_up->execute();

            $_SESSION['pesan'] = 'Data tugas "' . $new_judul . '" berhasil diperbarui.';
            header('Location: daftar_tugas.php');
            exit;
        }

        $mata_kuliah_id = $new_mk_id;
        $judul = $new_judul;
        $instruksi = $new_instruksi;
        $tenggat = $new_tenggat;
        $status = $new_status;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Tugas - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body><main>
    <div class="menu">
        <a href="index.php">Beranda</a> |
        <a href="daftar_mata_kuliah.php">Mata Kuliah</a> |
        <a href="daftar_materi.php">Materi</a> |
        <a href="daftar_tugas.php">Tugas</a>
    </div>

    <p class="label">LMS EAD / EDIT TUGAS</p>
    <h1>Edit tugas</h1>
    <p><a href="daftar_tugas.php">Kembali ke daftar</a></p>

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

        <label for="judul">Judul tugas</label>
        <input id="judul" name="judul" maxlength="150" value="<?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?>" required>

        <label for="instruksi">Instruksi tugas</label>
        <textarea id="instruksi" name="instruksi" rows="6" required><?= htmlspecialchars($instruksi, ENT_QUOTES, 'UTF-8') ?></textarea>

        <label for="tenggat">Tenggat</label>
        <input id="tenggat" name="tenggat" type="date" value="<?= htmlspecialchars($tenggat, ENT_QUOTES, 'UTF-8') ?>" required>

        <label for="status">Status</label>
        <select id="status" name="status" required>
            <option value="Draf" <?= ($status === 'Draf') ? 'selected' : '' ?>>Draf</option>
            <option value="Aktif" <?= ($status === 'Aktif') ? 'selected' : '' ?>>Aktif</option>
            <option value="Selesai" <?= ($status === 'Selesai') ? 'selected' : '' ?>>Selesai</option>
        </select>

        <button name="simpan">Simpan data</button>
        <a href="daftar_tugas.php">Batal</a>
    </form>
</main></body>
</html>
