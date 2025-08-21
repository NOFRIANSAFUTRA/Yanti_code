<?php
session_start();
include 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = mysqli_query($conn, "DELETE FROM users WHERE id=$id");

    if ($query) {
        $_SESSION['hapus_sukses'] = "Akun berhasil dihapus.";
    } else {
        $_SESSION['hapus_error'] = "Gagal menghapus akun.";
    }
}

header("Location: kelola_akun.php");
exit();
