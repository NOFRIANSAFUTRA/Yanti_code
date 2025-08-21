<?php
include 'db.php';
session_start();

// Cek login dan role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kader') {
    echo "Akses ditolak. Hanya untuk pengguna dengan peran 'kader'.";
    exit();
}

// Tentukan alamat/desa dan tabel berdasarkan username kader
$kader_config = [
    'Rian' => [
        'desa' => 'Ujung Padang',
        'tabel_anak' => 'anak',
        'tabel_remaja' => 'remaja_rian'
    ],
    'Yanti' => [
        'desa' => 'Sialang',
        'tabel_anak' => 'anak_yanti',
        'tabel_remaja' => 'remaja_yanti'
    ],
    'Bariah' => [
        'desa' => 'Barat Daya',
        'tabel_anak' => 'anak_bariahh',
        'tabel_remaja' => 'remaja_bariah'
    ],
    'Nawawi' => [
        'desa' => 'Kapeh',
        'tabel_anak' => 'anak_nawawi',
        'tabel_remaja' => 'remaja_nawawi'
    ],
    'Ali' => [
        'desa' => 'Kedai Kandang',
        'tabel_anak' => 'anak_al',
        'tabel_remaja' => 'remaja_al'
    ],
    'Farmala' => [
        'desa' => 'Kedai Runding',
        'tabel_anak' => 'anak_farmala',
        'tabel_remaja' => 'remaja_farmala'
    ],
    'Rahmad' => [
        'desa' => 'Suaq Bakung',
        'tabel_anak' => 'anak_Rahmad',
        'tabel_remaja' => 'remaja_rahmad'
    ],
    'Maulana' => [
        'desa' => 'Rantau Binuang',
        'tabel_anak' => 'anak_Maulana',
        'tabel_remaja' => 'remaja_maulana'
    ],
    'Ari' => [
        'desa' => 'Pulo Ie',
        'tabel_anak' => 'anak_ari',
        'tabel_remaja' => 'remaja_ari'
    ],
    'Rafif' => [
        'desa' => 'luar',
        'tabel_anak' => 'anak_rafif',
        'tabel_remaja' => 'remaja_rafif'
    ],
    'Andi' => [
        'desa'=> 'Ujung',
        'tabel_anak' => 'anak_andi',
        'tabel_remaja' => 'remaja_andi'
    ],
    'Siti' => [
        'desa'=> 'Jua',
        'tabel_anak' => 'anak_siti',
        'tabel_remaja' => 'remaja_siti'
    ],
    'Budi' => [
        'desa'=> 'Pasi Meurapat',
        'tabel_anak' => 'anak_budi',
        'tabel_remaja' => 'remaja_budi'
    ],
    'Fitri' => [
        'desa'=> 'Ujung Pasir',
        'tabel_anak' => 'anak_fitri',
        'tabel_remaja' => 'remaja_fitri'
    ],
    'Hasan' => [
        'desa'=> 'Geulumbuk',
        'tabel_anak' => 'anak_hasan',
        'tabel_remaja' => 'remaja_hasan'
    ],
    'Lina' => [
        'desa'=> 'Pasilembang',
        'tabel_anak' => 'anak_lina',
        'tabel_remaja' => 'remaja_lina'
    ],
    'Dedi' => [
        'desa'=> 'Indradamal',
        'tabel_anak' => 'anak_dedi',
        'tabel_remaja' => 'remaja_dedi'
    ]
];


// Cek apakah username termasuk dalam daftar kader yang diizinkan
$username = $_SESSION['username'];
if (!array_key_exists($username, $kader_config) || !$kader_config[$username]['tabel_remaja']) {
    echo "Akses ditolak. Kader tidak terdaftar atau tidak memiliki akses untuk input data remaja.";
    exit();
}

$desa = $kader_config[$username]['desa'];
$tabel = $kader_config[$username]['tabel_remaja'];
$notif = false;

// Function untuk menghitung umur dalam tahun
function hitungUmurTahun($tgl_lahir) {
    $lahir = new DateTime($tgl_lahir);
    $sekarang = new DateTime();
    $selisih = $lahir->diff($sekarang);
    return $selisih->y;
}

// Proses simpan data remaja
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama             = $_POST['nama'];
    $jk               = $_POST['jk'];
    $tgl_lahir        = $_POST['tgl_lahir'];
    $orang_tua        = $_POST['orang_tua'];
    $bb               = $_POST['bb'];
    $tb               = $_POST['tb'];
    $status_gizi      = $_POST['status_gizi'];
    $status_pubertas  = $_POST['status_pubertas'];
    $menstruasi_pertama = $_POST['menstruasi_pertama'];
    $status_reproduksi = $_POST['status_reproduksi'];
    $keterangan       = $_POST['keterangan'];

    $umur = hitungUmurTahun($tgl_lahir); // Hitung umur dalam tahun

    $stmt = $conn->prepare("INSERT INTO $tabel 
        (nama, jk, tgl_lahir, umur, orang_tua, alamat, bb, tb, status_gizi, 
         status_pubertas, menstruasi_pertama, status_reproduksi, ket)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("sssisssssssss", 
        $nama, $jk, $tgl_lahir, $umur, $orang_tua, $desa, 
        $bb, $tb, $status_gizi, $status_pubertas, 
        $menstruasi_pertama, $status_reproduksi, $keterangan);
    
    if($stmt->execute()) {
        $notif = true;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Data Remaja - <?php echo $desa; ?></title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 20px;
            background: linear-gradient(135deg, <?php echo $username === 'Rian' ? '#667eea 0%, #764ba2 100%' : '#4facfe 0%, #00f2fe 100%'; ?>);
            min-height: 100vh;
        }
        
        .container {
            max-width: 800px;
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
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: <?php echo $username === 'Rian' ? '#667eea' : '#4facfe'; ?>;
            box-shadow: 0 0 0 3px rgba(<?php echo $username === 'Rian' ? '102, 126, 234' : '79, 172, 254'; ?>, 0.1);
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
        
        .btn-primary {
            background: linear-gradient(135deg, <?php echo $username === 'Rian' ? '#667eea 0%, #764ba2 100%' : '#4facfe 0%, #00f2fe 100%'; ?>);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(<?php echo $username === 'Rian' ? '102, 126, 234' : '79, 172, 254'; ?>, 0.4);
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
        <h2><i class="fas fa-user-graduate"></i> Tambah Data Remaja</h2>
        <p>Desa <?php echo $desa; ?> - Sistem Informasi Remaja (Kader <?php echo $username; ?>)</p>
    </div>
    
    <form method="POST">
        <div class="form-group">
            <label><i class="fas fa-user"></i> Nama Remaja</label>
            <input type="text" name="nama" required placeholder="Masukkan nama lengkap remaja">
        </div>

        <div class="form-group">
            <label><i class="fas fa-venus-mars"></i> Jenis Kelamin</label>
            <div class="radio-group">
                <div class="radio-item">
                    <input type="radio" name="jk" value="Laki-laki" id="laki" required>
                    <label for="laki">Laki-laki</label>
                </div>
                <div class="radio-item">
                    <input type="radio" name="jk" value="Perempuan" id="perempuan" required>
                    <label for="perempuan">Perempuan</label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label><i class="fas fa-calendar"></i> Tanggal Lahir</label>
            <input type="date" name="tgl_lahir" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-users"></i> Nama Orang Tua</label>
            <input type="text" name="orang_tua" required placeholder="Masukkan nama orang tua/wali">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-weight"></i> Berat Badan (kg)</label>
                <input type="number" step="0.1" name="bb" placeholder="0.0" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-ruler-vertical"></i> Tinggi Badan (cm)</label>
                <input type="number" step="0.1" name="tb" placeholder="0.0" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-heartbeat"></i> Status Gizi</label>
                <select name="status_gizi" required>
                    <option value="">- Pilih Status Gizi -</option>
                    <option value="Gizi Baik">Gizi Baik</option>
                    <option value="Gizi Kurang">Gizi Kurang</option>
                    <option value="Gizi Lebih">Gizi Lebih</option>
                    <option value="Stunting">Stunting</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-child"></i> Status Pubertas</label>
                <select name="status_pubertas" required>
                    <option value="">- Pilih Status Pubertas -</option>
                    <option value="Belum">Belum</option>
                    <option value="Sedang">Sedang</option>
                    <option value="Sudah">Sudah</option>
                </select>
            </div>
        </div>

        <div class="form-row" id="menstruasi-field" style="display: none;">
            <div class="form-group">
                <label><i class="fas fa-calendar-check"></i> Menstruasi Pertama</label>
                <input type="date" name="menstruasi_pertama" id="menstruasi-input">
            </div>
        </div>

        <div class="form-group">
            <label><i class="fas fa-heart"></i> Status Reproduksi</label>
            <textarea name="status_reproduksi" rows="3" placeholder="Masukkan informasi kesehatan reproduksi"></textarea>
        </div>

        <div class="form-group">
            <label><i class="fas fa-sticky-note"></i> Keterangan</label>
            <textarea name="keterangan" rows="3" placeholder="Catatan tambahan (opsional)"></textarea>
        </div>

        <div class="form-actions">
            <a href="dashboard_kader.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Data
            </button>
        </div>
    </form>
</div>

<script>
    // Tampilkan field menstruasi hanya untuk perempuan
    document.querySelectorAll('input[name="jk"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const menstruasiField = document.getElementById('menstruasi-field');
            const menstruasiInput = document.getElementById('menstruasi-input');
            
            if (this.value === 'Perempuan' && this.checked) {
                menstruasiField.style.display = 'block';
                menstruasiInput.required = true;
            } else {
                menstruasiField.style.display = 'none';
                menstruasiInput.required = false;
            }
        });
    });
</script>

<?php if ($notif): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Data remaja berhasil ditambahkan.',
        showConfirmButton: true,
        confirmButtonColor: '<?php echo $username === 'Rian' ? '#667eea' : '#4facfe'; ?>',
        confirmButtonText: 'OK'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'dashboard_kader.php';
        }
    });
</script>
<?php endif; ?>

</body>
</html>