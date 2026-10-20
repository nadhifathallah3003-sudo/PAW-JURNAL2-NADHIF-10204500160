<?php
// edit_mata_kuliah.php
session_start();
require 'koneksi.php';

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

$id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));

// Ambil data lama
$stmt = $conn->prepare("SELECT * FROM mata_kuliah WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$mk = $stmt->get_result()->fetch_assoc();

if (!$mk) {
    exit('Mata kuliah tidak ditemukan. Kembali ke daftar mata kuliah.');
}

$kode = $mk['kode'];
$nama = $mk['nama'];
$dosen = $mk['dosen'];
$semester = $mk['semester'];
$pesan = '';
$pesan_kode = '';

if (isset($_POST['simpan'])) {
    // 1. Verifikasi CSRF token
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $pesan = 'ERROR J05 — Token keamanan tidak valid atau telah kedaluwarsa.';
    } else {
        $new_kode = strtoupper(trim($_POST['kode'] ?? ''));
        $new_nama = trim($_POST['nama'] ?? '');
        $new_dosen = trim($_POST['dosen'] ?? '');
        $new_semester = trim($_POST['semester'] ?? '');

        // 2. Periksa apakah tidak ada data yang berubah (sesuai ketentuan Jurnal 2)
        if ($new_kode === $mk['kode'] && $new_nama === $mk['nama'] && $new_dosen === $mk['dosen'] && (int)$new_semester === (int)$mk['semester']) {
            $_SESSION['pesan'] = 'Tidak ada perubahan data.';
            header('Location: daftar_mata_kuliah.php');
            exit;
        }

        // 3. Validasi kolom kosong
        if ($new_kode === '' || $new_nama === '' || $new_dosen === '' || $new_semester === '') {
            $pesan = 'Semua kolom wajib diisi.';
        } elseif (strlen($new_kode) < 3 || strlen($new_kode) > 20 || !preg_match('/^[A-Z0-9\-]+$/', $new_kode)) {
            $pesan_kode = 'Kode mata kuliah harus 3-20 karakter (huruf kapital, angka, tanda -).';
        } elseif (strlen($new_nama) < 3 || strlen($new_nama) > 100) {
            $pesan = 'Nama mata kuliah harus 3-100 karakter.';
        } elseif (strlen($new_dosen) < 3 || strlen($new_dosen) > 100) {
            $pesan = 'Nama dosen pengampu harus 3-100 karakter.';
        } elseif (!is_numeric($new_semester) || (int)$new_semester < 1 || (int)$new_semester > 14) {
            $pesan = 'Semester harus berupa bilangan bulat antara 1 sampai 14.';
        } else {
            // 4. Periksa apakah kode baru sudah dipakai mata kuliah lain (id <> $id)
            $stmt_cek = $conn->prepare("SELECT id FROM mata_kuliah WHERE kode = ? AND id <> ?");
            $stmt_cek->bind_param("si", $new_kode, $id);
            $stmt_cek->execute();
            $cek = $stmt_cek->get_result();

            if ($cek->num_rows > 0) {
                $pesan_kode = 'Kode mata kuliah sudah digunakan.';
            } else {
                // 5. Update data dengan prepared statement
                $sem_val = (int)$new_semester;
                $stmt_up = $conn->prepare("UPDATE mata_kuliah SET kode = ?, nama = ?, dosen = ?, semester = ? WHERE id = ?");
                $stmt_up->bind_param("sssii", $new_kode, $new_nama, $new_dosen, $sem_val, $id);
                $stmt_up->execute();

                $_SESSION['pesan'] = 'Data mata kuliah ' . $new_kode . ' berhasil diperbarui.';
                header('Location: daftar_mata_kuliah.php');
                exit;
            }
        }
        $kode = $new_kode;
        $nama = $new_nama;
        $dosen = $new_dosen;
        $semester = $new_semester;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Mata Kuliah - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body><main>
    <div class="menu">
        <a href="index.php">Beranda</a> |
        <a href="daftar_mata_kuliah.php">Mata Kuliah</a> |
        <a href="daftar_materi.php">Materi</a> |
        <a href="daftar_tugas.php">Tugas</a>
    </div>

    <p class="label">LMS EAD / EDIT</p>
    <h1>Edit mata kuliah</h1>
    <p><a href="daftar_mata_kuliah.php">Kembali ke daftar</a></p>

    <?php if ($pesan != ''): ?>
        <div class="kesalahan"><?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token'], ENT_QUOTES, 'UTF-8') ?>">

        <label for="kode">Kode mata kuliah</label>
        <input id="kode" name="kode" maxlength="20" value="<?= htmlspecialchars($kode, ENT_QUOTES, 'UTF-8') ?>" required>
        <?php if ($pesan_kode != ''): ?>
            <div class="pesan-field"><?= htmlspecialchars($pesan_kode, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <label for="nama">Nama mata kuliah</label>
        <input id="nama" name="nama" maxlength="100" value="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?>" required>

        <label for="dosen">Dosen pengampu</label>
        <input id="dosen" name="dosen" maxlength="100" value="<?= htmlspecialchars($dosen, ENT_QUOTES, 'UTF-8') ?>" required>

        <label for="semester">Semester</label>
        <input id="semester" name="semester" type="number" min="1" max="14" value="<?= htmlspecialchars($semester, ENT_QUOTES, 'UTF-8') ?>" required>

        <button name="simpan">Simpan data</button>
        <a href="daftar_mata_kuliah.php">Batal</a>
    </form>
</main></body>
</html>
