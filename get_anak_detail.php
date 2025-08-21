<?php
include 'db.php';
session_start();

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

$id = $_GET['id'] ?? 0;

// Ambil data anak
$stmt = $conn->prepare("SELECT * FROM anak WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$anak = $result->fetch_assoc();

if (!$anak) {
    echo "<div class='alert alert-danger'>Data tidak ditemukan.</div>";
    exit();
}

// Hitung pertumbuhan
$bb_growth = ($anak['bb_lalu'] && $anak['bb_ini']) ? $anak['bb_ini'] - $anak['bb_lalu'] : 0;
$tb_growth = ($anak['pbtb_lalu'] && $anak['pbtb_ini']) ? $anak['pbtb_ini'] - $anak['pbtb_lalu'] : 0;
$lk_growth = ($anak['lk_lalu'] && $anak['lk_ini']) ? $anak['lk_ini'] - $anak['lk_lalu'] : 0;
?>

<div class="container-fluid">
    <div class="row">
        <!-- Informasi Dasar -->
        <div class="col-md-6">
            <h6 class="text-primary"><i class="fas fa-user"></i> Informasi Dasar</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>Nama Lengkap</strong></td>
                    <td><?php echo htmlspecialchars($anak['nama']); ?></td>
                </tr>
                <tr>
                    <td><strong>Jenis Kelamin</strong></td>
                    <td>
                        <span class="badge badge-<?php echo $anak['jk'] == 'Laki-laki' ? 'primary' : 'pink'; ?>">
                            <?php echo $anak['jk']; ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Tanggal Lahir</strong></td>
                    <td><?php echo date('d F Y', strtotime($anak['tgl_lahir'])); ?></td>
                </tr>
                <tr>
                    <td><strong>Umur</strong></td>
                    <td><?php echo $anak['umur']; ?> bulan</td>
                </tr>
                <tr>
                    <td><strong>Nama Orang Tua</strong></td>
                    <td><?php echo htmlspecialchars($anak['orang_tua']); ?></td>
                </tr>
                <tr>
                    <td><strong>Alamat</strong></td>
                    <td><?php echo htmlspecialchars($anak['alamat']); ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Status Gizi -->
        <div class="col-md-6">
            <h6 class="text-success"><i class="fas fa-heartbeat"></i> Status Gizi & Catatan</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>Status Gizi</strong></td>
                    <td>
                        <?php 
                        $status = $anak['status_gizi'];
                        $badge_class = '';
                        switch($status) {
                            case 'Gizi Baik': $badge_class = 'success'; break;
                            case 'Gizi Kurang': $badge_class = 'warning'; break;
                            case 'Gizi Lebih': $badge_class = 'info'; break;
                            case 'Stunting': $badge_class = 'danger'; break;
                            default: $badge_class = 'secondary';
                        }
                        ?>
                        <?php if ($status): ?>
                            <span class="badge badge-<?php echo $badge_class; ?>" style="font-size: 12px;">
                                <?php echo $status; ?>
                            </span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Belum Dinilai</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Keterangan</strong></td>
                    <td><?php echo $anak['ket'] ? htmlspecialchars($anak['ket']) : '<em class="text-muted">Tidak ada catatan</em>'; ?></td>
                </tr>
                <tr>
                    <td><strong>Tanggal Input</strong></td>
                    <td><?php echo date('d F Y, H:i', strtotime($anak['created_at'])); ?></td>
                </tr>
                <?php if ($anak['updated_at'] && $anak['updated_at'] != $anak['created_at']): ?>
                <tr>
                    <td><strong>Terakhir Update</strong></td>
                    <td><?php echo date('d F Y, H:i', strtotime($anak['updated_at'])); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    
    <hr>
    
    <!-- Data Antropometri -->
    <h6 class="text-info"><i class="fas fa-chart-line"></i> Data Antropometri & Pertumbuhan</h6>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-weight"></i> Berat Badan (kg)</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Pengukuran Lalu</small>
                            <h5><?php echo $anak['bb_lalu'] ?: '-'; ?></h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Pengukuran Sekarang</small>
                            <h5><?php echo $anak['bb_ini'] ?: '-'; ?></h5>
                        </div>
                    </div>
                    <?php if ($bb_growth != 0): ?>
                    <div class="mt-2">
                        <small class="text-muted">Pertumbuhan:</small>
                        <span class="badge badge-<?php echo $bb_growth > 0 ? 'success' : 'warning'; ?>">
                            <?php echo ($bb_growth > 0 ? '+' : '') . $bb_growth; ?> kg
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-ruler-vertical"></i> Tinggi/Panjang Badan (cm)</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Pengukuran Lalu</small>
                            <h5><?php echo $anak['pbtb_lalu'] ?: '-'; ?></h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Pengukuran Sekarang</small>
                            <h5><?php echo $anak['pbtb_ini'] ?: '-'; ?></h5>
                        </div>
                    </div>
                    <?php if ($tb_growth != 0): ?>
                    <div class="mt-2">
                        <small class="text-muted">Pertumbuhan:</small>
                        <span class="badge badge-<?php echo $tb_growth > 0 ? 'success' : 'warning'; ?>">
                            <?php echo ($tb_growth > 0 ? '+' : '') . $tb_growth; ?> cm
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0"><i class="fas fa-circle"></i> Lingkar Kepala (cm)</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Pengukuran Lalu</small>
                            <h5><?php echo $anak['lk_lalu'] ?: '-'; ?></h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Pengukuran Sekarang</small>
                            <h5><?php echo $anak['lk_ini'] ?: '-'; ?></h5>
                        </div>
                    </div>
                    <?php if ($lk_growth != 0): ?>
                    <div class="mt-2">
                        <small class="text-muted">Pertumbuhan:</small>
                        <span class="badge badge-<?php echo $lk_growth > 0 ? 'success' : 'warning'; ?>">
                            <?php echo ($lk_growth > 0 ? '+' : '') . $lk_growth; ?> cm
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-pink {  
    background-color: #e91e63;
    color: white;
}

.card {
    margin-bottom: 15px;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-header {
    border-bottom: none;
}

.card-body h5 {
    font-weight: bold;
    color: #333;
}
</style>