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
        'tabel' => 'anak'
    ],
    'Yanti' => [
        'desa' => 'Sialang',
        'tabel' => 'anak_yanti'
    ],
    'Bariah' => [
        'desa' => 'Barat Daya',
        'tabel' => 'anak_bariahh'
    ],
     'Nawawi' => [
        'desa' => 'Kapeh',
        'tabel' => 'anak_nawawi'
     ],
      'Ali' => [
        'desa' => 'Kedai Kandang',
        'tabel' => 'anak_al'
      ],
     'Farmala' => [
        'desa' => 'Kedai Runding',
        'tabel' => 'anak_farmala'
     ],
     'Rahmad' => [
        'desa' => 'Suaq Bakung',
        'tabel' => 'anak_Rahmad'
     ],
      'Maulana' => [
        'desa' => 'Rantau Binuang',
        'tabel' => 'anak_Maulana'
      ],
       'Ari' => [
        'desa' => 'Pulo Ie',
        'tabel' => 'anak_ari'
       ],
     'Rafif' => [
        'desa' => 'luar',
        'tabel' => 'anak_rafif'
     ],
     'Andi' =>[
        'desa'=> 'Ujung',
        'tabel' => 'anak_andi'
    ],
         'Siti' =>[
        'desa'=> 'Jua',
        'tabel' => 'anak_siti'
    ],
          'Budi' =>[
        'desa'=> 'Pasi Meurapat',
        'tabel' => 'anak_budi'
    ],
            'Fitri' =>[
        'desa'=> 'Ujung Pasir',
        'tabel' => 'anak_fitri'
    ],
            'Hasan' =>[
        'desa'=> 'Geulumbuk',
        'tabel' => 'anak_hasan'
    ],
           'Lina' =>[
        'desa'=> 'Pasilembang',
        'tabel' => 'anak_lina'
    ],
             'Dedi' =>[
        'desa'=> 'Indradamal',
        'tabel' => 'anak_dedi'
    ]



];


// Cek apakah username termasuk dalam daftar kader yang diizinkan
$username = $_SESSION['username'];
if (!array_key_exists($username, $kader_config)) {
    echo "Akses ditolak. Kader tidak terdaftar.";
    exit();
}

$desa = $kader_config[$username]['desa'];
$tabel = $kader_config[$username]['tabel'];
$notif = false;

// Function untuk menghitung umur dalam bulan
function hitungUmurBulan($tgl_lahir) {
    $lahir = new DateTime($tgl_lahir);
    $sekarang = new DateTime();
    $selisih = $lahir->diff($sekarang);
    return ($selisih->y * 12) + $selisih->m;
}

// Proses simpan data anak
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama         = $_POST['nama'];
    $jk           = $_POST['jk'];
    $tgl_lahir    = $_POST['tgl_lahir'];
    $orang_tua    = $_POST['orang_tua'];
    $bb_lalu      = $_POST['bb_lalu'];
    $bb_ini       = $_POST['bb_ini'];
    $pb_lalu      = $_POST['pb_lalu'];
    $pb_ini       = $_POST['pb_ini'];
    $lk_lalu      = $_POST['lk_lalu'];
    $lk_ini       = $_POST['lk_ini'];
    $status_gizi  = $_POST['status_gizi'];
    $keterangan   = $_POST['keterangan'];

    $umur = hitungUmurBulan($tgl_lahir); // Hitung umur dalam bulan

    // Sesuaikan dengan tabel yang sesuai
    $stmt = $conn->prepare("INSERT INTO $tabel 
        (nama, jk, tgl_lahir, umur, orang_tua, alamat, bb_lalu, bb_ini, pbtb_lalu, pbtb_ini, lk_lalu, lk_ini, status_gizi, ket)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("sssissssssssss", 
        $nama, $jk, $tgl_lahir, $umur, $orang_tua, $desa, 
        $bb_lalu, $bb_ini, $pb_lalu, $pb_ini, $lk_lalu, $lk_ini, 
        $status_gizi, $keterangan);
    
    if($stmt->execute()) {
        $notif = true;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Data Anak - <?php echo $desa; ?></title>
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
        <h2><i class="fas fa-baby"></i> Tambah Data Anak</h2>
        <p>Desa <?php echo $desa; ?> - Sistem Informasi Balita (Kader <?php echo $username; ?>)</p>
    </div>
    
    <form method="POST">
        <div class="form-group">
            <label><i class="fas fa-user"></i> Nama Anak</label>
            <input type="text" name="nama" required placeholder="Masukkan nama lengkap anak">
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
                <label><i class="fas fa-weight"></i> Berat Badan Lalu (kg)</label>
                <input type="number" step="0.1" name="bb_lalu" placeholder="0.0">
            </div>
            <div class="form-group">
                <label><i class="fas fa-weight"></i> Berat Badan Sekarang (kg)</label>
                <input type="number" step="0.1" name="bb_ini" placeholder="0.0">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-ruler-vertical"></i> Panjang/Tinggi Badan Lalu (cm)</label>
                <input type="number" step="0.1" name="pb_lalu" placeholder="0.0">
            </div>
            <div class="form-group">
                <label><i class="fas fa-ruler-vertical"></i> Panjang/Tinggi Badan Sekarang (cm)</label>
                <input type="number" step="0.1" name="pb_ini" placeholder="0.0">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-circle"></i> Lingkar Kepala Lalu (cm)</label>
                <input type="number" step="0.1" name="lk_lalu" placeholder="0.0">
            </div>
            <div class="form-group">
                <label><i class="fas fa-circle"></i> Lingkar Kepala Sekarang (cm)</label>
                <input type="number" step="0.1" name="lk_ini" placeholder="0.0">
            </div>
        </div>

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
            <label><i class="fas fa-sticky-note"></i> Keterangan</label>
            <textarea name="keterangan" rows="4" placeholder="Catatan tambahan (opsional)"></textarea>
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

<?php if ($notif): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Data anak berhasil ditambahkan.',
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