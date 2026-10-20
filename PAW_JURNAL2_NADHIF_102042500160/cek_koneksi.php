<?php
// cek_koneksi.php
require 'koneksi.php';

$hasil = mysqli_query($conn, 'SELECT COUNT(*) AS jumlah FROM mata_kuliah');
$baris = mysqli_fetch_assoc($hasil);
echo 'Koneksi berhasil. Jumlah mata kuliah: ' . $baris['jumlah'];
