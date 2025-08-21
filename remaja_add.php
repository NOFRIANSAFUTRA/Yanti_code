<?php
include 'db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kader') {
    die("Akses ditolak. Hanya untuk pengguna kader.");
}

$kader_config = [
    'Rian' => ['desa' => 'Ujung Padang', 'tabel' => 'remaja_ujungpadang'],
    'Yanti' => ['desa' => 'Sialang', 'tabel' => 'remaja_sialang'],
    'Bariah' => ['desa' => 'Barat Daya', 'tabel' => 'remaja_baratdaya'],
    'Nawawi' => ['desa' => 'Kapeh', 'tabel' => 'remaja_kapeh'],
    'Al' => ['desa' => 'Kedai Kandang', 'tabel' => 'remaja_kedaikandang'],
    'Farmala' => ['desa' => 'Kedai Runding', 'tabel' => 'remaja_kedairunding']
];

$username = $_SESSION['username'];
if (!isset($kader_config[$username])) {
    die("Kader tidak terdaftar.");
}

$desa = $kader_config[$username]['desa'];
$tabel = $kader_config[$username]['tabel'];

function hitungUmurTahun($tgl_lahir) {
    $lahir = new DateTime($tgl_lahir);
    $sekarang = new DateTime();
    $selisih = $lahir->diff($sekarang);
    return $selisih->y;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $jk = $_POST['jk'] ?? '';
    $tgl_lahir = $_POST['tgl_lahir'] ?? '';
    $bb = $_POST['bb'] ?? null;
    $tb = $_POST['tb'] ?? null;
    $status_kesehatan = $_POST['status_kesehatan'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';

    $umur = hitungUmurTahun($tgl_lahir);

    $stmt = $conn->prepare("INSERT INTO $tabel (nama, jk, tgl_lahir, umur, alamat, berat_badan, tinggi_badan, status_kesehatan, keterangan) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisisis", $nama, $jk, $tgl_lahir, $umur, $desa, $bb, $tb, $status_kesehatan, $keterangan);

    $sukses = $stmt->execute();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Data Remaja - <?= $desa ?></title>
</head>
<body>
<h1>Tambah Data Remaja - Desa <?= $desa ?></h1>
<?php if (!empty($sukses)) echo "<p>Data berhasil disimpan!</p>"; ?>


</body>
</html>
