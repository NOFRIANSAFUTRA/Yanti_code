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
        'tabel_remaja' => 'remaja_rian',
        'tabel_lansia' => 'lansia_rian'
    ],
    'Yanti' => [
        'desa' => 'Sialang',
        'tabel_anak' => 'anak_yanti',
        'tabel_remaja' => 'remaja_yanti',
        'tabel_lansia' => 'lansia_yanti'
    ],
    'Bariah' => [
        'desa' => 'Barat Daya',
        'tabel_anak' => 'anak_bariahh',
        'tabel_remaja' => 'remaja_bariah',
        'tabel_lansia' => 'lansia_bariah'
    ],
    'Nawawi' => [
        'desa' => 'Kapeh',
        'tabel_anak' => 'anak_nawawi',
        'tabel_remaja' => 'remaja_nawawi',
        'tabel_lansia' => 'lansia_nawawi'
    ],
    'Ali' => [
        'desa' => 'Kedai Kandang',
        'tabel_anak' => 'anak_al',
        'tabel_remaja' => 'remaja_al',
        'tabel_lansia' => 'lansia_al'
    ],
    'Farmala' => [
        'desa' => 'Kedai Runding',
        'tabel_anak' => 'anak_farmala',
        'tabel_remaja' => 'remaja_farmala',
        'tabel_lansia' => 'lansia_farmala'
    ],
    'Rahmad' => [
        'desa' => 'Suaq Bakung',
        'tabel_anak' => 'anak_Rahmad',
        'tabel_remaja' => 'remaja_rahmad',
        'tabel_lansia' => 'lansia_rahmad'
    ],
    'Maulana' => [
        'desa' => 'Rantau Binuang',
        'tabel_anak' => 'anak_Maulana',
        'tabel_remaja' => 'remaja_maulana',
        'tabel_lansia' => 'lansia_maulana'
    ],
    'Ari' => [
        'desa' => 'Pulo Ie',
        'tabel_anak' => 'anak_ari',
        'tabel_remaja' => 'remaja_ari',
        'tabel_lansia' => 'lansia_ari'
    ],
    'Rafif' => [
        'desa' => 'luar',
        'tabel_anak' => 'anak_rafif',
        'tabel_remaja' => 'remaja_rafif',
        'tabel_lansia' => 'lansia_rafif'
    ],
    'Andi' => [
        'desa'=> 'Ujung',
        'tabel_anak' => 'anak_andi',
        'tabel_remaja' => 'remaja_andi',
        'tabel_lansia' => 'lansia_andi'
    ],
    'Siti' => [
        'desa'=> 'Jua',
        'tabel_anak' => 'anak_siti',
        'tabel_remaja' => 'remaja_siti',
        'tabel_lansia' => 'lansia_siti'
    ],
    'Budi' => [
        'desa'=> 'Pasi Meurapat',
        'tabel_anak' => 'anak_budi',
        'tabel_remaja' => 'remaja_budi',
        'tabel_lansia' => 'lansia_budi'
    ],
    'Fitri' => [
        'desa'=> 'Ujung Pasir',
        'tabel_anak' => 'anak_fitri',
        'tabel_remaja' => 'remaja_fitri',
        'tabel_lansia' => 'lansia_fitri'
    ],
    'Hasan' => [
        'desa'=> 'Geulumbuk',
        'tabel_anak' => 'anak_hasan',
        'tabel_remaja' => 'remaja_hasan',
        'tabel_lansia' => 'lansia_hasan'
    ],
    'Lina' => [
        'desa'=> 'Pasilembang',
        'tabel_anak' => 'anak_lina',
        'tabel_remaja' => 'remaja_lina',
        'tabel_lansia' => 'lansia_lina'
    ],
    'Dedi' => [
        'desa'=> 'Indradamal',
        'tabel_anak' => 'anak_dedi',
        'tabel_remaja' => 'remaja_dedi',
        'tabel_lansia' => 'lansia_dedi'
    ]
];


// Cek apakah username termasuk dalam daftar kader yang diizinkan
$username = $_SESSION['username'];
if (!array_key_exists($username, $kader_config) || !$kader_config[$username]['tabel_lansia']) {
    echo "Akses ditolak. Kader tidak terdaftar atau tidak memiliki akses untuk input data lansia.";
    exit();
}

$desa = $kader_config[$username]['desa'];
$tabel = $kader_config[$username]['tabel_lansia'];
$notif = false;

// Function untuk menghitung umur dalam tahun
function hitungUmurTahun($tgl_lahir) {
    $lahir = new DateTime($tgl_lahir);
    $sekarang = new DateTime();
    $selisih = $lahir->diff($sekarang);
    return $selisih->y;
}

// Proses simpan data lansia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $nik = $_POST['nik'];
    $jk = $_POST['jk'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $no_telepon = $_POST['no_telepon'];
    $nama_keluarga = $_POST['nama_keluarga'];
    $no_telepon_keluarga = $_POST['no_telepon_keluarga'];
    $riwayat_penyakit = $_POST['riwayat_penyakit'];
    $obat_rutin = $_POST['obat_rutin'];
    $status_kesehatan = $_POST['status_kesehatan'];
    $keterangan = $_POST['keterangan'];

    $umur = hitungUmurTahun($tgl_lahir); // Hitung umur dalam tahun

    // Cek apakah NIK sudah ada
    $check_nik = $conn->prepare("SELECT nik FROM $tabel WHERE nik = ?");
    $check_nik->bind_param("s", $nik);
    $check_nik->execute();
    $result = $check_nik->get_result();
    
    if ($result->num_rows > 0) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'NIK sudah terdaftar dalam sistem.',
                confirmButtonColor: '#dc3545'
            });
        </script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO $tabel 
            (nama, nik, jk, tgl_lahir, umur, alamat, no_telepon, nama_keluarga, 
             no_telepon_keluarga, riwayat_penyakit, obat_rutin, status_kesehatan, ket)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("sssisssssssss", 
            $nama, $nik, $jk, $tgl_lahir, $umur, $desa, $no_telepon, 
            $nama_keluarga, $no_telepon_keluarga, $riwayat_penyakit, 
            $obat_rutin, $status_kesehatan, $keterangan);
        
        if($stmt->execute()) {
            $notif = true;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Data Lansia - <?php echo $desa; ?></title>
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
        
        .section-title {
            color: #4a5568;
            font-size: 18px;
            font-weight: 600;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .form-row-3 { 
            display: grid; 
            grid-template-columns: 1fr 1fr 1fr; 
            gap: 20px; 
        }
        
        .required {
            color: #e53e3e;
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
        
        .info-text {
            font-size: 12px;
            color: #718096;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .form-row, .form-row-3 {
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
        <h2><i class="fas fa-user-friends"></i> Tambah Data Lansia</h2>
        <p>Desa <?php echo $desa; ?> - Sistem Informasi Lansia (Kader <?php echo $username; ?>)</p>
    </div>
    
    <form method="POST">
        <!-- Data Pribadi -->
        <div class="section-title">
            <i class="fas fa-user"></i> Data Pribadi
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Nama Lengkap <span class="required">*</span></label>
                <input type="text" name="nama" required placeholder="Masukkan nama lengkap lansia">
            </div>
            <div class="form-group">
                <label><i class="fas fa-id-card"></i> NIK <span class="required">*</span></label>
                <input type="text" name="nik" maxlength="16" pattern="[0-9]{16}" required placeholder="16 digit NIK">
                <div class="info-text">Masukkan 16 digit NIK</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-venus-mars"></i> Jenis Kelamin <span class="required">*</span></label>
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
                <label><i class="fas fa-calendar"></i> Tanggal Lahir <span class="required">*</span></label>
                <input type="date" name="tgl_lahir" required id="tgl_lahir">
            </div>
        </div>

        <div class="form-group">
            <label><i class="fas fa-phone"></i> No. Telepon</label>
            <input type="tel" name="no_telepon" placeholder="Contoh: 08123456789">
        </div>

        <!-- Kontak Darurat -->
        <div class="section-title">
            <i class="fas fa-phone-alt"></i> Kontak Darurat
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-users"></i> Nama Keluarga/Wali</label>
                <input type="text" name="nama_keluarga" placeholder="Nama keluarga terdekat">
            </div>
            <div class="form-group">
                <label><i class="fas fa-phone"></i> No. Telepon Keluarga</label>
                <input type="tel" name="no_telepon_keluarga" placeholder="Nomor telepon keluarga">
            </div>
        </div>

        <!-- Data Kesehatan -->
        <div class="section-title">
            <i class="fas fa-heartbeat"></i> Data Kesehatan
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-notes-medical"></i> Riwayat Penyakit</label>
            <textarea name="riwayat_penyakit" rows="3" placeholder="Contoh: Hipertensi, Diabetes, Jantung, dll."></textarea>
        </div>

        <div class="form-group">
            <label><i class="fas fa-pills"></i> Obat Rutin</label>
            <textarea name="obat_rutin" rows="3" placeholder="Daftar obat yang dikonsumsi secara rutin"></textarea>
        </div>

        <div class="form-group">
            <label><i class="fas fa-heart"></i> Status Kesehatan</label>
            <select name="status_kesehatan" required>
                <option value="">- Pilih Status Kesehatan -</option>
                <option value="Sehat">Sehat</option>
                <option value="Kurang Sehat">Kurang Sehat</option>
                <option value="Sakit">Sakit</option>
                <option value="Dalam Perawatan">Dalam Perawatan</option>
            </select>
        </div>

        <div class="form-group">
            <label><i class="fas fa-sticky-note"></i> Keterangan</label>
            <textarea name="keterangan" rows="3" placeholder="Catatan tambahan mengenai kondisi lansia (opsional)"></textarea>
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
    // Validasi NIK hanya angka
    document.querySelector('input[name="nik"]').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    // Auto format nomor telepon
    function formatPhone(input) {
        input.addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9]/g, '');
            if (value.startsWith('0')) {
                // Biarkan format lokal Indonesia
                this.value = value;
            } else if (value.startsWith('62')) {
                this.value = '0' + value.substring(2);
            }
        });
    }
    
    formatPhone(document.querySelector('input[name="no_telepon"]'));
    formatPhone(document.querySelector('input[name="no_telepon_keluarga"]'));
    
    // Validasi umur untuk lansia (minimal 60 tahun)
    document.getElementById('tgl_lahir').addEventListener('change', function() {
        const birthDate = new Date(this.value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        if (age < 60) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: 'Usia kurang dari 60 tahun. Pastikan data yang dimasukkan adalah data lansia.',
                confirmButtonColor: '<?php echo $username === 'Rian' ? '#667eea' : '#4facfe'; ?>'
            });
        }
    });
</script>

<?php if ($notif): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Data lansia berhasil ditambahkan.',
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