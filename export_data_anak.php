<?php
include 'db.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=data_anak.xls");
header("Pragma: no-cache");
header("Expires: 0");

$sql = "SELECT id, nama, umur, orang_tua, alamat FROM anak";
$result = mysqli_query($conn, $sql);

echo "ID\tNama\tUmur\tOrang Tua\tAlamat\n";

while ($row = mysqli_fetch_assoc($result)) {
    echo $row['id'] . "\t" .
         $row['nama'] . "\t" .
         $row['umur'] . "\t" .
         $row['orang_tua'] . "\t" .
         $row['alamat'] . "\n";
}

mysqli_close($conn);
?>
