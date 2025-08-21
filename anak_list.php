<?php
include 'db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'kader') {
    header("Location: login.php");
    exit();
}

// Proses hapus jika ada parameter ?delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Sebaiknya cek dulu data ini milik desa kader login, agar kader tidak bisa hapus data desa lain
    $desa = $_SESSION['desa'];

    // Cek kepemilikan data
    $stmt_check = $conn->prepare("SELECT id FROM anak WHERE id = ? AND desa = ?");
    $stmt_check->bind_param("is", $id, $desa);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Jika data sesuai desa kader, hapus
        $stmt = $conn->prepare("DELETE FROM anak WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: anak_list.php");
    exit();
}

// Ambil data anak berdasarkan desa kader yang login
$desa = $_SESSION['desa'];
$stmt = $conn->prepare("SELECT * FROM anak WHERE desa = ?");
$stmt->bind_param("s", $desa);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Data Anak</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f2f2f2; }
        a.button { padding: 6px 12px; background-color: green; color: white; text-decoration: none; border-radius: 4px; }
        a.button.red { background-color: red; }
    </style>
</head>
<body>
    <h2>👶 Daftar Data Anak - Desa <?= htmlspecialchars($desa) ?></h2>
    <a href="anak_add.php" class="button">+ Tambah Anak</a>
    <table>
        <tr>
            <th>Nama</th>
            <th>Umur</th>
            <th>Nama Orang Tua</th>
            <th>Alamat</th>
            <th>Aksi</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['nama']); ?></td>
            <td><?= htmlspecialchars($row['umur']); ?> bulan</td>
            <td><?= htmlspecialchars($row['orang_tua']); ?></td>
            <td><?= htmlspecialchars($row['alamat']); ?></td>
            <td>
                <a href="anak_edit.php?id=<?= $row['id']; ?>" class="button">Edit</a>
                <a href="anak_list.php?delete=<?= $row['id']; ?>" class="button red" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
