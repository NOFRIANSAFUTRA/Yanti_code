<?php
session_start();  // Pastikan ini di paling atas
include 'db.php';

// Debug: Cek data yang diterima
// file_put_contents('debug.txt', print_r($_POST, true), FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_messages'] = ['Metode request tidak valid'];
    header("Location: index.php");
    exit();
}

// Filter dan validasi data
$judul    = mysqli_real_escape_string($conn, $_POST['judul'] ?? '');
$materi   = mysqli_real_escape_string($conn, $_POST['materi'] ?? '');
$tanggal  = mysqli_real_escape_string($conn, $_POST['tanggal'] ?? '');
$lokasi   = mysqli_real_escape_string($conn, $_POST['lokasi'] ?? '');
$peserta  = intval($_POST['peserta'] ?? 0);
$desa     = mysqli_real_escape_string($conn, $_POST['desa'] ?? '');
$petugas  = mysqli_real_escape_string($conn, $_POST['petugas'] ?? '');

// Debug: Simpan data POST ke log
// error_log("Desa: " . $desa);

// Validasi
$errors = [];
if (empty($desa)) $errors[] = "Nama desa tidak boleh kosong";
// ... validasi lainnya ...

if (!empty($errors)) {
    $_SESSION['error_messages'] = $errors;
    header("Location: index.php");
    exit();
}

// Simpan ke database
$sql = "INSERT INTO penyuluhan (judul, materi, tanggal, lokasi, peserta, desa, petugas, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssiss", $judul, $materi, $tanggal, $lokasi, $peserta, $desa, $petugas);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Data berhasil disimpan untuk Desa " . htmlspecialchars($desa);
} else {
    $_SESSION['error_messages'] = ["Gagal menyimpan: " . $conn->error];
}

header("Location: index.php");
exit();
?>