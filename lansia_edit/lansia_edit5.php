<?php 
include('../db.php');

session_start();

// Function untuk tampilan access denied yang lebih baik
function showAccessDenied() {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Akses Ditolak - Sistem Posyandu</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            /* CSS tetap sama */
              * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .access-denied-container {
                background: white;
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                padding: 40px;
                text-align: center;
                max-width: 500px;
                width: 100%;
                animation: slideIn 0.5s ease-out;
            }
            
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .error-icon {
                font-size: 80px;
                color: #e74c3c;
                margin-bottom: 20px;
                animation: bounce 2s infinite;
            }
            
            @keyframes bounce {
                0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
                40% { transform: translateY(-10px); }
                60% { transform: translateY(-5px); }
            }
            
            .error-title {
                color: #2c3e50;
                font-size: 28px;
                font-weight: 700;
                margin-bottom: 15px;
            }
            
            .error-message {
                color: #7f8c8d;
                font-size: 16px;
                line-height: 1.6;
                margin-bottom: 30px;
                padding: 20px;
                background: #f8f9fa;
                border-radius: 10px;
                border-left: 4px solid #e74c3c;
            }
            
            .user-info {
                background: #ecf0f1;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 25px;
                font-size: 14px;
                color: #34495e;
            }
            
            .user-info strong {
                color: #2c3e50;
            }
            
            .btn-group {
                display: flex;
                gap: 15px;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .btn {
                padding: 12px 25px;
                border: none;
                border-radius: 25px;
                text-decoration: none;
                font-weight: 600;
                font-size: 14px;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                cursor: pointer;
            }
            
            .btn-primary {
                background: linear-gradient(45deg, #3498db, #2980b9);
                color: white;
            }
            
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 15px rgba(52, 152, 219, 0.3);
            }
            
            .btn-secondary {
                background: linear-gradient(45deg, #95a5a6, #7f8c8d);
                color: white;
            }
            
            .btn-secondary:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 15px rgba(149, 165, 166, 0.3);
            }
            
            .footer-info {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #ecf0f1;
                font-size: 12px;
                color: #95a5a6;
            }
            
            .security-badge {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                background: #e8f5e8;
                color: #27ae60;
                padding: 5px 10px;
                border-radius: 15px;
                font-size: 11px;
                margin-top: 10px;
            }
            
            @media (max-width: 480px) {
                .access-denied-container {
                    padding: 30px 20px;
                }
                
                .error-title {
                    font-size: 24px;
                }
                
                .btn-group {
                    flex-direction: column;
                }
                
                .btn {
                    width: 100%;
                    justify-content: center;
                }
            }
        </style>
    </head>
    <body>
        <div class="access-denied-container">
            <!-- Konten tetap sama -->
             <div class="error-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            
            <h1 class="error-title">Akses Terbatas</h1>
            
            <div class="error-message">
                <i class="fas fa-exclamation-triangle" style="color: #e74c3c; margin-right: 8px;"></i>
                <strong>Halaman Edit Data Lansia</strong> ini memiliki akses terbatas dan hanya dapat digunakan oleh <strong>Kader bernama Ali</strong> untuk menjaga keamanan dan integritas data.
            </div>
            
            <?php if (isset($_SESSION['username']) && isset($_SESSION['role'])): ?>
            <!-- <div class="user-info">
                <i class="fas fa-user-circle" style="margin-right: 8px;"></i>
                <strong>Sesi Anda:</strong><br>
                Username: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong><br>
                Role: <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong>
            </div> -->
            <?php endif; ?>
            
            <div class="btn-group">
                <a href="../dashboard_kader.php" class="btn btn-primary">
                    <i class="fas fa-tachometer-alt"></i>
                    Kembali ke Dashboard
                </a>
            </div>
            
            <div class="footer-info">
                <div class="security-badge">
                    <i class="fas fa-lock"></i>
                    Sistem Keamanan Aktif
                </div>
                <br><br>
                <i class="fas fa-info-circle"></i>
                Jika Anda merasa ini adalah kesalahan, silakan hubungi administrator sistem.
                <br>
                <small>Error Code: 403 - Forbidden Access</small>
            </div>
        </div>
        
        <script>
            // Script tetap sama
              // Auto redirect setelah 10 detik
            setTimeout(function() {
                if (confirm('Halaman akan kembali ke dashboard. Lanjutkan?')) {
                    window.location.href = '../dashboard_kader.php';
                }
            }, 10000);
            
            // Prevent back button abuse
            history.pushState(null, null, location.href);
            window.onpopstate = function () {
                history.go(1);
            };
        </script>
    </body>
    </html>
    <?php
    exit();
}

// Cek akses dengan tampilan yang lebih baik
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kader' || $_SESSION['username'] !== 'Ali') {
    showAccessDenied();
}

$id = $_GET['id'] ?? 0;
$notif = false;
$error_message = '';

// Function untuk menghitung umur dalam tahun
function hitungUmur($tgl_lahir) {
    $lahir = new DateTime($tgl_lahir);
    $sekarang = new DateTime();
    $selisih = $lahir->diff($sekarang);
    return $selisih->y;
}

// Validasi ID
if ($id == 0 || !is_numeric($id)) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'ID Tidak Valid!',
                text: 'ID data lansia tidak valid.',
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                window.location.href = '../dashboard_kader.php';
            });
        });
    </script>";
    exit();
}

$stmt = $conn->prepare("SELECT * FROM lansia_al WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Data Tidak Ditemukan!',
                text: 'Data lansia yang Anda cari tidak tersedia.',
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                window.location.href = '../dashboard_kader.php';
            });
        });
    </script>";
    exit();
}

// Proses update data lansia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan sanitize data
    $nama = $conn->real_escape_string($_POST['nama']);
    $nik = $conn->real_escape_string($_POST['nik']);
    $jk = $conn->real_escape_string($_POST['jk']);
    $tgl_lahir = $conn->real_escape_string($_POST['tgl_lahir']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $no_telepon = $conn->real_escape_string($_POST['no_telepon']);
    $nama_keluarga = $conn->real_escape_string($_POST['nama_keluarga']);
    $no_telepon_keluarga = $conn->real_escape_string($_POST['no_telepon_keluarga']);
    $riwayat_penyakit = $conn->real_escape_string($_POST['riwayat_penyakit']);
    $obat_rutin = $conn->real_escape_string($_POST['obat_rutin']);
    $status_kesehatan = $conn->real_escape_string($_POST['status_kesehatan']);
    $keterangan = $conn->real_escape_string($_POST['ket']);
    
    $umur = hitungUmur($tgl_lahir); // Hitung umur dalam tahun
    
    // Debug: Tampilkan data yang akan diupdate
    error_log("Updating data lansia ID: $id");
    error_log("Nama: $nama, NIK: $nik, Umur: $umur");
    
    // Update data - PERBAIKAN DI SINI
    $stmt = $conn->prepare("UPDATE lansia_al SET 
        nama = ?, nik = ?, jk = ?, tgl_lahir = ?, umur = ?, alamat = ?, 
        no_telepon = ?, nama_keluarga = ?, no_telepon_keluarga = ?, 
        riwayat_penyakit = ?, obat_rutin = ?, status_kesehatan = ?, ket = ?,
        updated_at = NOW()
        WHERE id = ?");
        
    // PERBAIKAN: Pastikan urutan parameter sesuai dengan query
    $stmt->bind_param("ssssissssssssi",
        $nama, $nik, $jk, $tgl_lahir, $umur, $alamat,
        $no_telepon, $nama_keluarga, $no_telepon_keluarga,
        $riwayat_penyakit, $obat_rutin, $status_kesehatan, $keterangan, $id);
        
    if($stmt->execute()) {
        // Cek apakah data benar-benar terupdate
        if ($stmt->affected_rows > 0) {
            $notif = true;
            error_log("Data berhasil diupdate. Affected rows: " . $stmt->affected_rows);
        } else {
            $error_message = "Tidak ada perubahan data atau data tidak ditemukan.";
            error_log("Tidak ada perubahan data untuk ID: $id");
        }
    } else {
        $error_message = "Gagal memperbarui data lansia: " . $conn->error;
        error_log("Error updating data: " . $conn->error);
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Data Lansia - Kedai Kandang</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS tetap sama */
          body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .header h2 {
            margin: 0;
            color: #4a5568;
            font-size: 28px;
        }
        
        .header p {
            color: #718096;
            margin: 5px 0 0 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600;
            color: #4a5568;
        }
        
        input, textarea, select {
            width: 100%; 
            padding: 12px; 
            border: 2px solid #e2e8f0; 
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 8px;
        }
        
        .radio-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .radio-item input[type="radio"] {
            width: auto;
            margin: 0;
        }
        
        .form-row { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 20px; 
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
            color: white;
        }
        
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(237, 137, 54, 0.4);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        
        .form-actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }
        
        .error-alert {
            background: #fed7d7;
            border: 1px solid #feb2b2;
            color: #c53030;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .container {
                margin: 10px;
                padding: 20px;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2><i class="fas fa-edit"></i> Edit Data Lansia</h2>
        <p>Desa Kedai Kandang - Sistem Informasi Lansia</p>
    </div>
    
    <?php if (!empty($error_message)): ?>
        <div class="error-alert">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($data): ?>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $id ?>">
        
        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Nama Lansia</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']); ?>" required>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-id-card"></i> NIK</label>
                <input type="text" name="nik" value="<?= htmlspecialchars($data['nik']); ?>" required>
            </div>
        </div>

        <!-- Form fields lainnya tetap sama -->
        <div class="form-group">
        <label><i class="fas fa-venus-mars"></i> Jenis Kelamin</label>
        <div class="radio-group">
            <div class="radio-item">
                <input type="radio" name="jk" value="Laki-laki" id="laki" <?= $data['jk'] == 'Laki-laki' ? 'checked' : ''; ?> required>
                <label for="laki">Laki-laki</label>
            </div>
            <div class="radio-item">
                <input type="radio" name="jk" value="Perempuan" id="perempuan" <?= $data['jk'] == 'Perempuan' ? 'checked' : ''; ?> required>
                <label for="perempuan">Perempuan</label>
            </div>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label><i class="fas fa-calendar"></i> Tanggal Lahir</label>
            <input type="date" name="tgl_lahir" value="<?= $data['tgl_lahir']; ?>" required>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-map-marker-alt"></i> Alamat</label>
            <input type="text" name="alamat" value="<?= htmlspecialchars($data['alamat']); ?>" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label><i class="fas fa-phone"></i> No. Telepon</label>
            <input type="text" name="no_telepon" value="<?= htmlspecialchars($data['no_telepon']); ?>">
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-users"></i> Nama Keluarga</label>
            <input type="text" name="nama_keluarga" value="<?= htmlspecialchars($data['nama_keluarga']); ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label><i class="fas fa-phone-alt"></i> No. Telepon Keluarga</label>
            <input type="text" name="no_telepon_keluarga" value="<?= htmlspecialchars($data['no_telepon_keluarga']); ?>">
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-heartbeat"></i> Status Kesehatan</label>
            <select name="status_kesehatan" required>
                <option value="Sehat" <?= $data['status_kesehatan'] == 'Sehat' ? 'selected' : ''; ?>>Sehat</option>
                <option value="Kurang Sehat" <?= $data['status_kesehatan'] == 'Kurang Sehat' ? 'selected' : ''; ?>>Kurang Sehat</option>
                <option value="Sakit" <?= $data['status_kesehatan'] == 'Sakit' ? 'selected' : ''; ?>>Sakit</option>
                <option value="Dalam Perawatan" <?= $data['status_kesehatan'] == 'Dalam Perawatan' ? 'selected' : ''; ?>>Dalam Perawatan</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label><i class="fas fa-file-medical"></i> Riwayat Penyakit</label>
        <textarea name="riwayat_penyakit" rows="3"><?= htmlspecialchars($data['riwayat_penyakit']); ?></textarea>
    </div>

    <div class="form-group">
        <label><i class="fas fa-pills"></i> Obat Rutin</label>
        <textarea name="obat_rutin" rows="3"><?= htmlspecialchars($data['obat_rutin']); ?></textarea>
    </div>

    <div class="form-group">
        <label><i class="fas fa-sticky-note"></i> Keterangan</label>
        <textarea name="ket" rows="3"><?= htmlspecialchars($data['ket']); ?></textarea>
    </div>


        <div class="form-actions">
            <a href="../dashboard_kader.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save"></i> Update Data
            </button>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php if ($notif): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data lansia berhasil diupdate.',
            showConfirmButton: true,
            confirmButtonColor: '#ed8936',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../dashboard_kader.php';
            }
        });
    });
</script>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?= addslashes($error_message); ?>',
            confirmButtonColor: '#e74c3c'
        });
    });
</script>
<?php endif; ?>

</body>
</html>