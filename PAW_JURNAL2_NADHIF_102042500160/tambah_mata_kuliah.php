<?php
// tambah_mata_kuliah.php
session_start();
require 'koneksi.php';

// Buat token CSRF pada session jika belum ada (sesuai bagian 4 Memahami Query Berparameter dan Session)
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

$kode = '';
$nama = '';
$dosen = '';
$semester = '';
$pesan = '';
$pesan_kode = '';

if (isset($_POST['simpan'])) {
    // 1. Periksa token CSRF
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $pesan = 'ERROR J05 — Token keamanan tidak valid atau telah kedaluwarsa.';
    } else {
        $kode = strtoupper(trim($_POST['kode'] ?? ''));
        $nama = trim($_POST['nama'] ?? '');
        $dosen = trim($_POST['dosen'] ?? '');
        $semester_input = trim($_POST['semester'] ?? '');

        // 2. Validasi kolom tidak boleh kosong
        if ($kode === '' || $nama === '' || $dosen === '' || $semester_input === '') {
            $pesan = 'Semua kolom wajib diisi.';
        }
        // Validasi format kode: 3-20 karakter, huruf kapital, angka, atau tanda -
        elseif (strlen($kode) < 3 || strlen($kode) > 20 || !preg_match('/^[A-Z0-9\-]+$/', $kode)) {
            $pesan_kode = 'Kode mata kuliah harus 3-20 karakter (huruf kapital, angka, tanda -).';
        }
        // Validasi panjang nama & dosen
        elseif (strlen($nama) < 3 || strlen($nama) > 100) {
            $pesan = 'Nama mata kuliah harus 3-100 karakter.';
        } elseif (strlen($dosen) < 3 || strlen($dosen) > 100) {
            $pesan = 'Nama dosen pengampu harus 3-100 karakter.';
        }
        // Validasi semester: bilangan bulat 1-14
        elseif (!is_numeric($semester_input) || (int)$semester_input < 1 || (int)$semester_input > 14) {
            $pesan = 'Semester harus berupa bilangan bulat antara 1 sampai 14.';
        } else {
            $semester = (int)$semester_input;

            // 3. Periksa keunikan kode di database
            $stmt_cek = $conn->prepare("SELECT id FROM mata_kuliah WHERE kode = ?");
            $stmt_cek->bind_param("s", $kode);
            $stmt_cek->execute();
            $cek = $stmt_cek->get_result();

            if ($cek->num_rows > 0) {
                // ERROR J02 — Kode mata kuliah sudah digunakan
                $pesan_kode = 'Kode mata kuliah sudah digunakan.';
            } else {
                // 4. Simpan dengan prepared statement (INSERT dengan parameter)
                $stmt_insert = $conn->prepare("INSERT INTO mata_kuliah (kode, nama, dosen, semester) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("sssi", $kode, $nama, $dosen, $semester);
                $stmt_insert->execute();

                $_SESSION['pesan'] = 'Mata kuliah ' . $kode . ' berhasil ditambahkan.';
                header('Location: daftar_mata_kuliah.php');
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
    <title>Tambah Mata Kuliah - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body><main>
    <div class="menu">
        <a href="index.php">Beranda</a> |
        <a href="daftar_mata_kuliah.php">Mata Kuliah</a> |
        <a href="daftar_materi.php">Materi</a> |
        <a href="daftar_tugas.php">Tugas</a>
    </div>

    <p class="label">LMS EAD / TAMBAH</p>
    <h1>Tambah mata kuliah</h1>
    <p><a href="daftar_mata_kuliah.php">Kembali ke daftar</a></p>

    <?php if ($pesan != ''): ?>
        <div class="kesalahan"><?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post">
        <!-- Token CSRF tersembunyi -->
        <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token'], ENT_QUOTES, 'UTF-8') ?>">

        <label for="kode">Kode mata kuliah</label>
        <input id="kode" name="kode" maxlength="20" value="<?= htmlspecialchars($kode, ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: PAW-101" required>
        <?php if ($pesan_kode != ''): ?>
            <div class="pesan-field"><?= htmlspecialchars($pesan_kode, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <label for="nama">Nama mata kuliah</label>
        <input id="nama" name="nama" maxlength="100" value="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: Pengembangan Aplikasi Website" required>

        <label for="dosen">Dosen pengampu</label>
        <input id="dosen" name="dosen" maxlength="100" value="<?= htmlspecialchars($dosen, ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: Dosen PAW" required>

        <label for="semester">Semester (1 - 14)</label>
        <input id="semester" name="semester" type="number" min="1" max="14" value="<?= htmlspecialchars($semester, ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: 3" required>

        <button name="simpan">Simpan data</button>
        <a href="daftar_mata_kuliah.php">Batal</a>
    </form>
</main></body>
</html>
