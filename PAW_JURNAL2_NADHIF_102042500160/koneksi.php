<?php
// koneksi.php
// Koneksi database LMS Praktikum PAW Modul 2

$conn = mysqli_connect('127.0.0.1', 'root', '', 'jurnal2db');

// Periksa koneksi
if (!$conn) {
    die('Koneksi ke database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
