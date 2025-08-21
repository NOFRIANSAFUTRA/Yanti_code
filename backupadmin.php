<?php
session_start();
include 'db.php';

// Cek apakah user sudah login dan memiliki role 'admin'
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    header("Location: unauthorized.php");
    exit;
}

// Ambil data akun kader dan petugas dari database
$sql = "SELECT * FROM users WHERE role IN ('kader', 'petugas') ORDER BY username ASC";
$query = mysqli_query($conn, $sql);

if (!$query) {
    die("Error dalam query: " . mysqli_error($conn));
}
if (!$query) {
    die("Query gagal: " . mysqli_error($conn));
}

// Ambil data anak untuk laporan
$anak_query = mysqli_query($conn, "SELECT * FROM anak LIMIT 10");

if (!$anak_query) {
    die("Query gagal: " . mysqli_error($conn));
}


// Proses hapus akun
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_btn'])) {
    $hapus_id = intval($_POST['hapus_id']);
    $hapus_query = mysqli_query($conn, "DELETE FROM users WHERE id = $hapus_id");

    if ($hapus_query) {
        // Simpan status di session agar bisa munculkan SweetAlert
        $_SESSION['hapus_sukses'] = true;
        header("Location: dashboard_admin.php?section=kelola_akun");
        exit;
    } else {
        $_SESSION['hapus_gagal'] = true;
        header("Location: dashboard_admin.php?section=kelola_akun");
        exit;
    }
}
$desa_tables = [
    'ujungpadang' => 'remaja_rian',
    'sialang' => 'remaja_yanti',
    'baratdaya' => 'remaja_bariah',
    'kapeh' => 'remaja_nawawi',
    'kedaikandang' => 'remaja_al',
    'kedairunding' => 'remaja_farmala'
];




?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Posyandu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hidden {
            display: none !important;
        }

        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --white: #ffffff;
            --sidebar-width: 260px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            position: fixed;
            height: 100%;
            padding: 1.5rem 0;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 100;
            transition: var(--transition);
        }

        .sidebar-header {
            text-align: center;
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .sidebar-header p {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .sidebar-menu {
            padding: 1.5rem 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            margin: 0.3rem 0;
            color: var(--white);
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: var(--transition);
        }

        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid var(--white);
        }

        .menu-item.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 3px solid var(--white);
        }

        .menu-item i {
            margin-right: 0.8rem;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .menu-item span {
            font-size: 0.95rem;
            font-weight: 500;
        }

        .logout-btn {
            position: absolute;
            bottom: 1.5rem;
            left: 0;
            right: 0;
            width: calc(100% - 3rem);
            margin: 0 1.5rem;
            background-color: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 5px;
        }

        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: var(--transition);
        }

        /* Admin Header */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .admin-header h1 {
            font-size: 1.8rem;
            color: var(--primary);
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card i {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .stat-card h3 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: var(--gray);
        }

        .stat-card p {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        /* Section Styles */
        .section {
            background-color: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .section.hidden {
            display: none;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .section-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
        }

        .section-header h3 i {
            margin-right: 0.5rem;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            outline: none;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.2);
        }

        .btn-danger {
            background-color: var(--danger);
            color: var(--white);
        }

        .btn-danger:hover {
            background-color: #e51773;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(247, 37, 133, 0.2);
        }

        .btn-success {
            background-color: var(--success);
            color: var(--white);
        }

        .btn-success:hover {
            background-color: #3ab5d9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(76, 201, 240, 0.2);
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th,
        .table td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }

        .table th {
            background-color: var(--light);
            color: var(--dark);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .table tr:hover td {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .table .actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Chart Container */
        .chart-container {
            height: 300px;
            margin-top: 1.5rem;
            position: relative;
        }

        .chart-placeholder {
            width: 100%;
            height: 100%;
            background-color: var(--light-gray);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
    </style>
    <!-- Tambahkan ini di dalam <head> -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Posyandu Sehat</h3>
                <p>Admin Dashboard</p>
            </div>

            <nav class="sidebar-menu">
                <a href="javascript:void(0);" class="menu-item active" onclick="showSection('dashboard')">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="javascript:void(0);" class="menu-item" onclick="showSection('kelola_akun_kader')">
                    <i class="fas fa-users-cog"></i>
                    <span>Kelola Akun Kader</span>
                </a>
                <a href="javascript:void(0);" class="menu-item" onclick="showSection('kelola_akun_petugas')">
                    <i class="fas fa-users-cog"></i>
                    <span>Kelola Akun Petugas</span>
                </a>
                <a href="javascript:void(0);" class="menu-item" onclick="showSection('data_anak')">
                    <i class="fas fa-baby"></i>
                    <span>Data Anak</span>
                </a>
                <a href="javascript:void(0);" class="menu-item" onclick="showSection('data_remaja')">
                    <i class="fas fa-baby"></i>
                    <span>Data Remaja</span>
                </a>
                <a href="javascript:void(0);" class="menu-item" onclick="showSection('data_lansia')">
                    <i class="fas fa-baby"></i>
                    <span>Data Lansia</span>
                </a>


            </nav>

            <a href="#" id="logoutBtn" class="btn btn-primary logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>

        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Admin Header -->
            <div class="admin-header">
                <h1>Dashboard Admin</h1>
                <div class="admin-info">
                    <span>Halo, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <div class="admin-avatar">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container" id="dashboard">
                <div class="stat-card fade-in">
                    <i class="fas fa-users"></i>
                    <h3>Total Kader & Petugas</h3>
                    <p><?php echo mysqli_num_rows($query); ?></p>
                </div>

            </div>

            <?php
            // Di bagian atas file dashboard_admin.php, sebelum section kelola_akun_kader
            // Pastikan query ini ada dan benar

            // Query untuk mengambil hanya akun kader
            $query_kader = mysqli_query($conn, "SELECT * FROM users WHERE role = 'kader' ORDER BY username ASC");

            // Jika Anda ingin mengecek apakah ada data
            $jumlah_kader = mysqli_num_rows($query_kader);

            // Handle error jika query gagal
            if (!$query_kader) {
                die("Query error: " . mysqli_error($conn));
            }
            ?>

            <!-- Kelola Akun Kader Section -->
            <div class="section hidden fade-in" id="kelola_akun_kader">
                <div class="section-header d-flex justify-content-between align-items-center mb-3">
                    <h3><i class="fas fa-users-cog"></i> Kelola Akun Kader (<?= $jumlah_kader; ?> akun)</h3>
                    <a href="tambah_akun.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Akun
                    </a>
                </div>

                <div class="table-responsive">
                    <?php if ($jumlah_kader > 0): ?>
                        <table class="table table-bordered table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Desa</th>
                                    <!-- <th>Tanggal Dibuat</th> -->
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = mysqli_fetch_assoc($query_kader)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['username']); ?></td>
                                        <td><?= htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-success"><?= htmlspecialchars($user['role']); ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($user['desa']); ?></td>

                                        <td class="actions">
                                            <a href="edit_akun.php?id=<?= $user['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form method="post" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus akun ini?')">
                                                <input type="hidden" name="hapus_id" value="<?= $user['id']; ?>">
                                                <button type="submit" name="hapus_btn" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            Belum ada akun kader yang terdaftar.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            // Di bagian atas file dashboard_admin.php, sebelum section kelola_akun_petugas
            // Pastikan query ini ada dan benar

            // Query untuk mengambil hanya akun petugas
            $query_petugas = mysqli_query($conn, "SELECT * FROM users WHERE role = 'petugas' ORDER BY username ASC");

            // Jika Anda ingin mengecek apakah ada data
            $jumlah_petugas = mysqli_num_rows($query_petugas);
            ?>

            <!-- Kemudian di bagian HTML -->
            <div class="section hidden fade-in" id="kelola_akun_petugas">
                <div class="section-header d-flex justify-content-between align-items-center mb-3">
                    <h3><i class="fas fa-users-cog"></i> Kelola Akun Petugas (<?= $jumlah_petugas; ?> akun)</h3>
                    <a href="tambah_akun_petugas.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Akun
                    </a>
                </div>

                <div class="table-responsive">
                    <?php if ($jumlah_petugas > 0): ?>
                        <table class="table table-bordered table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Desa</th>
                                    <!-- <th>Tanggal Dibuat</th> -->
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = mysqli_fetch_assoc($query_petugas)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['username']); ?></td>
                                        <td><?= htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?= htmlspecialchars($user['role']); ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($user['desa']); ?></td>

                                        <td class="actions">
                                            <a href="edit_akun.php?id=<?= $user['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form method="post" action="hapus_akun.php" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus akun petugas ini?')">
                                                <input type="hidden" name="hapus_id" value="<?= $user['id']; ?>">
                                                <input type="hidden" name="role" value="petugas">
                                                <button type="submit" name="hapus_btn" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            Belum ada akun petugas yang terdaftar.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Data Anak Section -->
            <div class="section hidden fade-in" id="data_anak">
            <div class="section-header">
        <h3><i class="fas fa-baby"></i> Data Anak</h3>
        <div class="header-actions">
           <div class="btn-group">
    <button class="btn btn-primary active" onclick="showAnakTab('ujungpadang')">Desa Ujung Padang</button>
    <button class="btn btn-primary" onclick="showAnakTab('sialang')">Desa Sialang</button>
    <button class="btn btn-primary" onclick="showAnakTab('baratdaya')">Desa Barat Daya</button>
    <button class="btn btn-primary" onclick="showAnakTab('kapeh')">Desa Kapeh</button>
    <button class="btn btn-primary" onclick="showAnakTab('kedaikandang')">Desa Kedai Kandang</button>
    <button class="btn btn-primary" onclick="showAnakTab('kedairunding')">Desa Kedai Runding</button>
    <button class="btn btn-primary" onclick="showAnakTab('suaqbakung')">Desa Suaq Bakung</button>
    <button class="btn btn-primary" onclick="showAnakTab('rantaubinuang')">Desa Rantau Binuang</button>
    <button class="btn btn-primary" onclick="showAnakTab('puloie')">Desa Pulo Ie</button>
    <button class="btn btn-primary" onclick="showAnakTab('luar')">Desa Luar</button>
    <button class="btn btn-primary" onclick="showAnakTab('ujung')">Desa Ujung</button>
    <button class="btn btn-primary" onclick="showAnakTab('jua')">Desa Jua</button>
    <button class="btn btn-primary" onclick="showAnakTab('pasimeurapat')">Desa Pasi Meurapat</button>
    <button class="btn btn-primary" onclick="showAnakTab('ujungpasir')">Desa Ujung Pasir</button>
    <button class="btn btn-primary" onclick="showAnakTab('geulumbuk')">Desa Geulumbuk</button>
    <button class="btn btn-primary" onclick="showAnakTab('pasielembang')">Desa Pasie Lembang</button>
    <button class="btn btn-primary" onclick="showAnakTab('indradamal')">Desa Indra Damal</button>
</div>

                <a href="export_data_anak.php" class="btn btn-success">
                    <i class="fas fa-download"></i> Export
                </a>
            <a href="print_data_anak.php" class="btn btn-info" target="_blank">
                <i class="fas fa-print"></i> Print
            </a>
        </div>
    </div>

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <?php
                    // Get stats for both villages
                    $up_anak = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM anak"));
                    $sialang_anak = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM anak_yanti"));
                      $baratdaya_anak = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM anak_bariahh"));

                    $total_anak = $up_anak + $sialang_anak + $baratdaya_anak ;

                    // Gizi Baik
                    $up_gizi_baik = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM anak WHERE status_gizi = 'Gizi Baik'"));
                    $sialang_gizi_baik = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM anak_yanti WHERE status_gizi = 'Gizi Baik'"));
                    $total_gizi_baik = $up_gizi_baik + $sialang_gizi_baik;

                    // Gizi Kurang
                    $up_gizi_kurang = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM anak WHERE status_gizi = 'Gizi Kurang'"));
                    $sialang_gizi_kurang = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM anak_yanti WHERE status_gizi = 'Gizi Kurang'"));
                    $total_gizi_kurang = $up_gizi_kurang + $sialang_gizi_kurang;

                    // Stunting
                    $up_stunting = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM anak WHERE status_gizi = 'Stunting'"));
                    $sialang_stunting = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM anak_yanti WHERE status_gizi = 'Stunting'"));
                    $total_stunting = $up_stunting + $sialang_stunting;
                    ?>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #3498db;">
                            <i class="fas fa-baby"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?= $total_anak ?></h4>
                            <p>Total Anak</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #2ecc71;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?= $total_gizi_baik ?></h4>
                            <p>Gizi Baik</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #f39c12;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?= $total_gizi_kurang ?></h4>
                            <p>Gizi Kurang</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #e74c3c;">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?= $total_stunting ?></h4>
                            <p>Stunting</p>
                        </div>
                    </div>
                </div>

                <!-- Tab Ujung Padang -->
                <div id="anak-ujungpadang-tab" class="anak-tab">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>JK</th>
                                    <th>Umur (bln)</th>
                                    <th>Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB (kg)</th>
                                    <th>TB/PB (cm)</th>
                                    <th>Status Gizi</th>
                                
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM anak ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['jk']) ?></td>
                                        <td><?= (int)$row['umur'] ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']) ?></td>
                                        <td><?= htmlspecialchars($row['alamat']) ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']) ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']) ?></td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_gizi'] == 'Gizi Baik' ? 'success' : ($row['status_gizi'] == 'Gizi Kurang' ? 'warning' : ($row['status_gizi'] == 'Stunting' ? 'danger' : 'secondary')) ?>">
                                                <?= $row['status_gizi'] ?: 'Belum dinilai' ?>
                                            </span>
                                        </td>
                                       
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Sialang -->
                <div id="anak-sialang-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>JK</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur (bln)</th>
                                    <th>Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB (kg)</th>
                                    <th>TB/PB (cm)</th>
                                    <th>Status Gizi</th>
                                 
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM anak_yanti ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['jk']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tgl_lahir'])) ?></td>
                                        <td><?= (int)$row['umur'] ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']) ?></td>
                                        <td><?= htmlspecialchars($row['alamat']) ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']) ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']) ?></td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_gizi'] == 'Gizi Baik' ? 'success' : ($row['status_gizi'] == 'Gizi Kurang' ? 'warning' : ($row['status_gizi'] == 'Stunting' ? 'danger' : 'secondary')) ?>">
                                                <?= $row['status_gizi'] ?: 'Belum dinilai' ?>
                                            </span>
                                        </td>
                                       
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                
             <div id="anak-baratdaya-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>JK</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur (bln)</th>
                                    <th>Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB (kg)</th>
                                    <th>TB/PB (cm)</th>
                                    <th>Status Gizi</th>
                                 
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM anak_bariahh ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['jk']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tgl_lahir'])) ?></td>
                                        <td><?= (int)$row['umur'] ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']) ?></td>
                                        <td><?= htmlspecialchars($row['alamat']) ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']) ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']) ?></td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_gizi'] == 'Gizi Baik' ? 'success' : ($row['status_gizi'] == 'Gizi Kurang' ? 'warning' : ($row['status_gizi'] == 'Stunting' ? 'danger' : 'secondary')) ?>">
                                                <?= $row['status_gizi'] ?: 'Belum dinilai' ?>
                                            </span>
                                        </td>
                                       
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="anak-kedaikandang-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>JK</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur (bln)</th>
                                    <th>Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB (kg)</th>
                                    <th>TB/PB (cm)</th>
                                    <th>Status Gizi</th>
                                 
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM anak_al ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['jk']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tgl_lahir'])) ?></td>
                                        <td><?= (int)$row['umur'] ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']) ?></td>
                                        <td><?= htmlspecialchars($row['alamat']) ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']) ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']) ?></td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_gizi'] == 'Gizi Baik' ? 'success' : ($row['status_gizi'] == 'Gizi Kurang' ? 'warning' : ($row['status_gizi'] == 'Stunting' ? 'danger' : 'secondary')) ?>">
                                                <?= $row['status_gizi'] ?: 'Belum dinilai' ?>
                                            </span>
                                        </td>
                                       
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="anak-kapeh-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>JK</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur (bln)</th>
                                    <th>Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB (kg)</th>
                                    <th>TB/PB (cm)</th>
                                    <th>Status Gizi</th>
                                 
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM anak_nawawi ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['jk']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tgl_lahir'])) ?></td>
                                        <td><?= (int)$row['umur'] ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']) ?></td>
                                        <td><?= htmlspecialchars($row['alamat']) ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']) ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']) ?></td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_gizi'] == 'Gizi Baik' ? 'success' : ($row['status_gizi'] == 'Gizi Kurang' ? 'warning' : ($row['status_gizi'] == 'Stunting' ? 'danger' : 'secondary')) ?>">
                                                <?= $row['status_gizi'] ?: 'Belum dinilai' ?>
                                            </span>
                                        </td>
                                       
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="section hidden fade-in" id="data_remaja">
            <div class="section-header">
    <h3><i class="fas fa-users"></i> Data Remaja</h3>
    <div class="header-actions">
        <div class="btn-group">
            <button class="btn btn-primary active" onclick="showRemajaTab('ujungpadang', event)">Desa Ujung Padang</button>
            <button class="btn btn-primary" onclick="showRemajaTab('sialang', event)">Desa Sialang</button>
            <button class="btn btn-primary" onclick="showRemajaTab('baratdaya', event)">Desa Barat Daya</button>
            <button class="btn btn-primary" onclick="showRemajaTab('kapeh', event)">Desa Kapeh</button>
            <button class="btn btn-primary" onclick="showRemajaTab('kedaikandang', event)">Desa Kedai Kandang</button>
            <button class="btn btn-primary" onclick="showRemajaTab('kedairunding', event)">Desa Kedai Runding</button>
            <button class="btn btn-primary" onclick="showRemajaTab('suaqbakung', event)">Desa Suaq Bakung</button>
            <button class="btn btn-primary" onclick="showRemajaTab('rantaubinuang', event)">Desa Rantau Binuang</button>
            <button class="btn btn-primary" onclick="showRemajaTab('puloie', event)">Desa Pulo Ie</button>
            <button class="btn btn-primary" onclick="showRemajaTab('luar', event)">Desa Luar</button>
            <button class="btn btn-primary" onclick="showRemajaTab('ujung', event)">Desa Ujung</button>
            <button class="btn btn-primary" onclick="showRemajaTab('jua', event)">Desa Jua</button>
            <button class="btn btn-primary" onclick="showRemajaTab('pasimeurapat', event)">Desa Pasi Meurapat</button>
            <button class="btn btn-primary" onclick="showRemajaTab('ujungpasir', event)">Desa Ujung Pasir</button>
            <button class="btn btn-primary" onclick="showRemajaTab('geulumbuk', event)">Desa Geulumbuk</button>
            <button class="btn btn-primary" onclick="showRemajaTab('pasielembang', event)">Desa Pasie Lembang</button>
            <button class="btn btn-primary" onclick="showRemajaTab('indradamal', event)">Desa Indra Damal</button>
        </div>
        <a href="export_data_remaja.php" class="btn btn-success">
            <i class="fas fa-download"></i> Export
        </a>
        <a href="print_data_remaja.php" class="btn btn-info" target="_blank">
            <i class="fas fa-print"></i> Print
        </a>
    </div>
</div>


                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <?php
                    // Get stats for all villages
                    $rian_remaja = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_rian"));
                    $yanti_remaja = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_yanti"));
                    $bariah_remaja = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_bariah"));
                    $nawawi_remaja = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_nawawi"));
                    $al_remaja = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_al"));
                    $farmala_remaja = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_farmala"));
                    $total_remaja = $rian_remaja + $yanti_remaja + $bariah_remaja + $nawawi_remaja + $al_remaja + $farmala_remaja;

                    // Gizi Baik
                    $rian_gizi_baik = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_rian WHERE status_gizi = 'Gizi Baik'"));
                    $yanti_gizi_baik = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_yanti WHERE status_gizi = 'Gizi Baik'"));
                    $bariah_gizi_baik = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_bariah WHERE status_gizi = 'Gizi Baik'"));
                    $nawawi_gizi_baik = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_nawawi WHERE status_gizi = 'Gizi Baik'"));
                    $al_gizi_baik = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_al WHERE status_gizi = 'Gizi Baik'"));
                    $farmala_gizi_baik = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_farmala WHERE status_gizi = 'Gizi Baik'"));
                    $total_gizi_baik = $rian_gizi_baik + $yanti_gizi_baik + $bariah_gizi_baik + $nawawi_gizi_baik + $al_gizi_baik + $farmala_gizi_baik;

                    // Gizi Kurang
                    $rian_gizi_kurang = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_rian WHERE status_gizi = 'Gizi Kurang'"));
                    $yanti_gizi_kurang = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_yanti WHERE status_gizi = 'Gizi Kurang'"));
                    $bariah_gizi_kurang = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_bariah WHERE status_gizi = 'Gizi Kurang'"));
                    $nawawi_gizi_kurang = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_nawawi WHERE status_gizi = 'Gizi Kurang'"));
                    $al_gizi_kurang = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_al WHERE status_gizi = 'Gizi Kurang'"));
                    $farmala_gizi_kurang = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_farmala WHERE status_gizi = 'Gizi Kurang'"));
                    $total_gizi_kurang = $rian_gizi_kurang + $yanti_gizi_kurang + $bariah_gizi_kurang + $nawawi_gizi_kurang + $al_gizi_kurang + $farmala_gizi_kurang;

                    // Stunting
                    $rian_stunting = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_rian WHERE status_gizi = 'Stunting'"));
                    $yanti_stunting = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_yanti WHERE status_gizi = 'Stunting'"));
                    $bariah_stunting = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_bariah WHERE status_gizi = 'Stunting'"));
                    $nawawi_stunting = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_nawawi WHERE status_gizi = 'Stunting'"));
                    $al_stunting = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_al WHERE status_gizi = 'Stunting'"));
                    $farmala_stunting = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_farmala WHERE status_gizi = 'Stunting'"));
                    $total_stunting = $rian_stunting + $yanti_stunting + $bariah_stunting + $nawawi_stunting + $al_stunting + $farmala_stunting;

                    // Gizi Lebih
                    $rian_gizi_lebih = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_rian WHERE status_gizi = 'Gizi Lebih'"));
                    $yanti_gizi_lebih = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_yanti WHERE status_gizi = 'Gizi Lebih'"));
                    $bariah_gizi_lebih = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_bariah WHERE status_gizi = 'Gizi Lebih'"));
                    $nawawi_gizi_lebih = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_nawawi WHERE status_gizi = 'Gizi Lebih'"));
                    $al_gizi_lebih = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_al WHERE status_gizi = 'Gizi Lebih'"));
                    $farmala_gizi_lebih = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM remaja_farmala WHERE status_gizi = 'Gizi Lebih'"));
                    $total_gizi_lebih = $rian_gizi_lebih + $yanti_gizi_lebih + $bariah_gizi_lebih + $nawawi_gizi_lebih + $al_gizi_lebih + $farmala_gizi_lebih;
                    ?>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #3498db;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?= $total_remaja ?></h4>
                            <p>Total Remaja</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #2ecc71;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?= $total_gizi_baik ?></h4>
                            <p>Gizi Baik</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #f39c12;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?= $total_gizi_kurang ?></h4>
                            <p>Gizi Kurang</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #e74c3c;">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?= $total_stunting ?></h4>
                            <p>Stunting</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #9b59b6;">
                            <i class="fas fa-weight"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?= $total_gizi_lebih ?></h4>
                            <p>Gizi Lebih</p>
                        </div>
                    </div>
                </div>

                <!-- Tab Ujung Padang -->
                <div id="remaja-ujungpadang-tab" class="remaja-tab">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>JK</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur (thn)</th>
                                    <th>Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_rian ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['jk']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tgl_lahir'])) ?></td>
                                        <td><?= (int)$row['umur'] ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']) ?></td>
                                        <td><?= htmlspecialchars($row['alamat']) ?></td>
                                        <td><?= htmlspecialchars($row['bb']) ?></td>
                                        <td><?= htmlspecialchars($row['tb']) ?></td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_gizi'] == 'Gizi Baik' ? 'success' : ($row['status_gizi'] == 'Gizi Kurang' ? 'warning' : ($row['status_gizi'] == 'Stunting' ? 'danger' : ($row['status_gizi'] == 'Gizi Lebih' ? 'info' : 'secondary'))) ?>">
                                                <?= $row['status_gizi'] ?: 'Belum dinilai' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_pubertas'] == 'Sudah' ? 'success' : ($row['status_pubertas'] == 'Sedang' ? 'warning' : 'secondary') ?>">
                                                <?= $row['status_pubertas'] ?: 'Belum' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= $row['jk'] == 'Perempuan' && $row['menstruasi_pertama'] ?
                                                date('d/m/Y', strtotime($row['menstruasi_pertama'])) : ($row['jk'] == 'Laki-laki' ? '-' : 'Belum') ?>
                                        </td>
                                        <td>
                                            <a href="remaja_edit.php?id=<?= $row['id'] ?>&kader=rian" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="remaja_delete.php?id=<?= $row['id'] ?>&kader=rian" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Sialang -->
                <div id="remaja-sialang-tab" class="remaja-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>JK</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur (thn)</th>
                                    <th>Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_yanti ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['jk']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tgl_lahir'])) ?></td>
                                        <td><?= (int)$row['umur'] ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']) ?></td>
                                        <td><?= htmlspecialchars($row['alamat']) ?></td>
                                        <td><?= htmlspecialchars($row['bb']) ?></td>
                                        <td><?= htmlspecialchars($row['tb']) ?></td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_gizi'] == 'Gizi Baik' ? 'success' : ($row['status_gizi'] == 'Gizi Kurang' ? 'warning' : ($row['status_gizi'] == 'Stunting' ? 'danger' : ($row['status_gizi'] == 'Gizi Lebih' ? 'info' : 'secondary'))) ?>">
                                                <?= $row['status_gizi'] ?: 'Belum dinilai' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_pubertas'] == 'Sudah' ? 'success' : ($row['status_pubertas'] == 'Sedang' ? 'warning' : 'secondary') ?>">
                                                <?= $row['status_pubertas'] ?: 'Belum' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= $row['jk'] == 'Perempuan' && $row['menstruasi_pertama'] ?
                                                date('d/m/Y', strtotime($row['menstruasi_pertama'])) : ($row['jk'] == 'Laki-laki' ? '-' : 'Belum') ?>
                                        </td>
                                        <td>
                                            <a href="remaja_edit.php?id=<?= $row['id'] ?>&kader=yanti" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="remaja_delete.php?id=<?= $row['id'] ?>&kader=yanti" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Barat Daya -->
                <div id="remaja-baratdaya-tab" class="remaja-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>JK</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur (thn)</th>
                                    <th>Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_bariah ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['jk']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tgl_lahir'])) ?></td>
                                        <td><?= (int)$row['umur'] ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']) ?></td>
                                        <td><?= htmlspecialchars($row['alamat']) ?></td>
                                        <td><?= htmlspecialchars($row['bb']) ?></td>
                                        <td><?= htmlspecialchars($row['tb']) ?></td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_gizi'] == 'Gizi Baik' ? 'success' : ($row['status_gizi'] == 'Gizi Kurang' ? 'warning' : ($row['status_gizi'] == 'Stunting' ? 'danger' : ($row['status_gizi'] == 'Gizi Lebih' ? 'info' : 'secondary'))) ?>">
                                                <?= $row['status_gizi'] ?: 'Belum dinilai' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_pubertas'] == 'Sudah' ? 'success' : ($row['status_pubertas'] == 'Sedang' ? 'warning' : 'secondary') ?>">
                                                <?= $row['status_pubertas'] ?: 'Belum' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= $row['jk'] == 'Perempuan' && $row['menstruasi_pertama'] ?
                                                date('d/m/Y', strtotime($row['menstruasi_pertama'])) : ($row['jk'] == 'Laki-laki' ? '-' : 'Belum') ?>
                                        </td>
                                        <td>
                                            <a href="remaja_edit.php?id=<?= $row['id'] ?>&kader=bariahh" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="remaja_delete.php?id=<?= $row['id'] ?>&kader=bariahh" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Kapeh -->
                <div id="remaja-kapeh-tab" class="remaja-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>JK</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur (thn)</th>
                                    <th>Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_nawawi ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['jk']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tgl_lahir'])) ?></td>
                                        <td><?= (int)$row['umur'] ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']) ?></td>
                                        <td><?= htmlspecialchars($row['alamat']) ?></td>
                                        <td><?= htmlspecialchars($row['bb']) ?></td>
                                        <td><?= htmlspecialchars($row['tb']) ?></td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_gizi'] == 'Gizi Baik' ? 'success' : ($row['status_gizi'] == 'Gizi Kurang' ? 'warning' : ($row['status_gizi'] == 'Stunting' ? 'danger' : ($row['status_gizi'] == 'Gizi Lebih' ? 'info' : 'secondary'))) ?>">
                                                <?= $row['status_gizi'] ?: 'Belum dinilai' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_pubertas'] == 'Sudah' ? 'success' : ($row['status_pubertas'] == 'Sedang' ? 'warning' : 'secondary') ?>">
                                                <?= $row['status_pubertas'] ?: 'Belum' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= $row['jk'] == 'Perempuan' && $row['menstruasi_pertama'] ?
                                                date('d/m/Y', strtotime($row['menstruasi_pertama'])) : ($row['jk'] == 'Laki-laki' ? '-' : 'Belum') ?>
                                        </td>
                                        <td>
                                            <a href="remaja_edit.php?id=<?= $row['id'] ?>&kader=nawawi" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="remaja_delete.php?id=<?= $row['id'] ?>&kader=nawawi" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Kedai Kandang -->
                <div id="remaja-kedaikandang-tab" class="remaja-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>JK</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur (thn)</th>
                                    <th>Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_al ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['jk']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tgl_lahir'])) ?></td>
                                        <td><?= (int)$row['umur'] ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']) ?></td>
                                        <td><?= htmlspecialchars($row['alamat']) ?></td>
                                        <td><?= htmlspecialchars($row['bb']) ?></td>
                                        <td><?= htmlspecialchars($row['tb']) ?></td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_gizi'] == 'Gizi Baik' ? 'success' : ($row['status_gizi'] == 'Gizi Kurang' ? 'warning' : ($row['status_gizi'] == 'Stunting' ? 'danger' : ($row['status_gizi'] == 'Gizi Lebih' ? 'info' : 'secondary'))) ?>">
                                                <?= $row['status_gizi'] ?: 'Belum dinilai' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_pubertas'] == 'Sudah' ? 'success' : ($row['status_pubertas'] == 'Sedang' ? 'warning' : 'secondary') ?>">
                                                <?= $row['status_pubertas'] ?: 'Belum' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= $row['jk'] == 'Perempuan' && $row['menstruasi_pertama'] ?
                                                date('d/m/Y', strtotime($row['menstruasi_pertama'])) : ($row['jk'] == 'Laki-laki' ? '-' : 'Belum') ?>
                                        </td>
                                        <td>
                                            <a href="remaja_edit.php?id=<?= $row['id'] ?>&kader=al" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="remaja_delete.php?id=<?= $row['id'] ?>&kader=al" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Kedai Runding -->
                <div id="remaja-kedairunding-tab" class="remaja-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>JK</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur (thn)</th>
                                    <th>Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_farmala ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['jk']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tgl_lahir'])) ?></td>
                                        <td><?= (int)$row['umur'] ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']) ?></td>
                                        <td><?= htmlspecialchars($row['alamat']) ?></td>
                                        <td><?= htmlspecialchars($row['bb']) ?></td>
                                        <td><?= htmlspecialchars($row['tb']) ?></td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_gizi'] == 'Gizi Baik' ? 'success' : ($row['status_gizi'] == 'Gizi Kurang' ? 'warning' : ($row['status_gizi'] == 'Stunting' ? 'danger' : ($row['status_gizi'] == 'Gizi Lebih' ? 'info' : 'secondary'))) ?>">
                                                <?= $row['status_gizi'] ?: 'Belum dinilai' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?=
                                                                        $row['status_pubertas'] == 'Sudah' ? 'success' : ($row['status_pubertas'] == 'Sedang' ? 'warning' : 'secondary') ?>">
                                                <?= $row['status_pubertas'] ?: 'Belum' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= $row['jk'] == 'Perempuan' && $row['menstruasi_pertama'] ?
                                                date('d/m/Y', strtotime($row['menstruasi_pertama'])) : ($row['jk'] == 'Laki-laki' ? '-' : 'Belum') ?>
                                        </td>
                                        <td>
                                            <a href="remaja_edit.php?id=<?= $row['id'] ?>&kader=farmala" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="remaja_delete.php?id=<?= $row['id'] ?>&kader=farmala" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="section hidden fade-in" id="data_lansia">
    <div class="section-header">
        <h3><i class="fas fa-user"></i> Data Lansia</h3>
        <div class="header-actions">
            <div class="btn-group">
                <button class="btn btn-primary active" onclick="showLansiaTab('ujungpadang')">Desa Ujung Padang</button>
                <button class="btn btn-primary" onclick="showLansiaTab('sialang')">Desa Sialang</button>
                <button class="btn btn-primary" onclick="showLansiaTab('baratdaya')">Desa Barat Daya</button>
                <button class="btn btn-primary" onclick="showLansiaTab('kapeh')">Desa Kapeh</button>
                <button class="btn btn-primary" onclick="showLansiaTab('kedaikandang')">Desa Kedai Kandang</button>
                <button class="btn btn-primary" onclick="showLansiaTab('kedairunding')">Desa Kedai Runding</button>
                <button class="btn btn-primary" onclick="showLansiaTab('suaqbakung')">Desa Suaq Bakung</button>
                <button class="btn btn-primary" onclick="showLansiaTab('rantaubinuang')">Desa Rantau Binuang</button>
                <button class="btn btn-primary" onclick="showLansiaTab('puloie')">Desa Pulo Ie</button>
                <button class="btn btn-primary" onclick="showLansiaTab('luar')">Desa Luar</button>
                <button class="btn btn-primary" onclick="showLansiaTab('ujung')">Desa Ujung</button>
                <button class="btn btn-primary" onclick="showLansiaTab('jua')">Desa Jua</button>
                <button class="btn btn-primary" onclick="showLansiaTab('pasimeurapat')">Desa Pasi Meurapat</button>
                <button class="btn btn-primary" onclick="showLansiaTab('ujungpasir')">Desa Ujung Pasir</button>
                <button class="btn btn-primary" onclick="showLansiaTab('geulumbuk')">Desa Geulumbuk</button>
                <button class="btn btn-primary" onclick="showLansiaTab('pasielembang')">Desa Pasie Lembang</button>
                <button class="btn btn-primary" onclick="showLansiaTab('indradamal')">Desa Indra Damal</button>
            </div>
            <a href="export_data_lansia.php" class="btn btn-success">
                <i class="fas fa-download"></i> Export
            </a>
            <a href="print_data_lansia.php" class="btn btn-info" target="_blank">
                <i class="fas fa-print"></i> Print
            </a>
        </div>
    </div>

    
</div>


            <style>
                .stats-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 15px;
                    margin-bottom: 20px;
                }

                .stat-card {
                    background: white;
                    padding: 15px;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    display: flex;
                    align-items: center;
                    gap: 15px;
                }

                .stat-icon {
                    width: 50px;
                    height: 50px;
                    border-radius: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: 20px;
                }

                .stat-content h4 {
                    margin: 0;
                    font-size: 24px;
                    font-weight: bold;
                    color: #333;
                }

                .stat-content p {
                    margin: 0;
                    color: #666;
                    font-size: 14px;
                }

                .badge {
                    padding: 5px 10px;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: 500;
                }

                .badge-success {
                    background-color: #28a745;
                    color: white;
                }

                .badge-warning {
                    background-color: #ffc107;
                    color: #212529;
                }

                .badge-danger {
                    background-color: #dc3545;
                    color: white;
                }

                .badge-secondary {
                    background-color: #6c757d;
                    color: white;
                }

            .header-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    /* kemungkinan ada padding atau width fixed di sini */
}

.btn-group {
    display: flex;
    flex-wrap: wrap; /* tombol bisa turun ke baris berikutnya */
    gap: 5px;
}

.btn-group .btn {
    white-space: nowrap; /* biar nama desa nggak pecah di tengah kata */
}


                .anak-tab {
                    transition: all 0.3s ease;
                }

                .anak-tab.hidden {
                    display: none;
                }

                @media (max-width: 768px) {
                    .header-actions {
                        flex-direction: column;
                        align-items: flex-start;
                    }

                    .btn-group {
                       width: 100%;
        justify-content: flex-start;
                    }

                    .stats-grid {
                        grid-template-columns: 1fr 1fr;
                    }
                }

                @media (max-width: 480px) {
                    .stats-grid {
                        grid-template-columns: 1fr;
                    }
                }
            </style>

            <script>
                function showAnakTab(desa) {
                    // Hide all tabs
                    document.querySelectorAll('.anak-tab').forEach(tab => {
                        tab.classList.add('hidden');
                    });

                    // Show selected tab
                    document.getElementById(`anak-${desa}-tab`).classList.remove('hidden');

                    // Update active button
                    const buttons = document.querySelectorAll('#data_anak .btn-group button');
                    buttons.forEach(btn => {
                        btn.classList.remove('active');
                        if (btn.textContent.includes(desa === 'ujungpadang' ? 'Ujung Padang' : 'Sialang')) {
                            btn.classList.add('active');
                        }
                    });
                }

                // Initialize with Ujung Padang tab shown by default
                document.addEventListener('DOMContentLoaded', function() {
                    showAnakTab('ujungpadang');
                });
            </script>
            <!-- Grafik Section -->

            <!-- Laporan Section -->

    </div>
    </main>
    </div>
    <div class="mb-3">
        <label>Filter Role:</label>
        <select class="form-select" onchange="filterRole(this.value)">
            <option value="all">Semua</option>
            <option value="kader">Kader</option>
            <option value="petugas">Petugas</option>
        </select>
    </div>

    <script>
        function filterRole(role) {
            window.location.href = 'dashboard_admin.php?role=' + role;
        }
    </script>

    <?php
    $role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';
    $query = "SELECT * FROM users WHERE role != 'admin'";
    if ($role_filter != 'all') {
        $query .= " AND role = '" . mysqli_real_escape_string($conn, $role_filter) . "'";
    }
    $query .= " ORDER BY desa, username";
    ?>
    <script>
        // Function untuk menampilkan tab data remaja

        function showRemajaTab(desa, event) {
            event.preventDefault();

            // Sembunyikan semua tab
            const tabs = document.querySelectorAll('.remaja-tab');
            tabs.forEach(tab => tab.classList.add('hidden'));

            // Hapus kelas active dari semua tombol
            const buttons = document.querySelectorAll('.btn-group .btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Tampilkan tab yang sesuai
            const selectedTab = document.getElementById(`remaja-${desa}-tab`);
            if (selectedTab) {
                selectedTab.classList.remove('hidden');
            }

            // Tambahkan kelas active pada tombol yang diklik
            event.target.classList.add('active');
        }



        // Fungsi alternatif tab remaja (jika menggunakan referensi langsung button)
        function setActiveRemajaTab(buttonElement, village) {
            showRemajaTab(village); // panggil fungsi utama
            buttonElement.classList.add('active');
        }

        // Fungsi untuk menampilkan tab data anak (balita)
        function showAnakTab(village, event) {
            if (event) event.preventDefault();

            // Sembunyikan semua tab anak
            document.querySelectorAll('.anak-tab').forEach(tab => {
                tab.classList.add('hidden');
                tab.classList.remove('fade-in');
            });

            // Hapus kelas aktif pada semua tombol
            document.querySelectorAll('#data_anak .btn-group .btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Tampilkan tab yang dipilih
            const tabId = 'anak-' + village + '-tab';
            const selectedTab = document.getElementById(tabId);
            if (selectedTab) {
                selectedTab.classList.remove('hidden');
                selectedTab.classList.add('fade-in');
            }

            // Tambahkan kelas aktif pada tombol yang diklik
            if (event && event.target) {
                event.target.classList.add('active');
            }
        }

        // Fungsi untuk menampilkan section utama
        function showSection(sectionId) {
            // Hapus kelas aktif pada semua menu
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });

            // Tambahkan kelas aktif ke menu yang diklik
            const activeMenu = document.querySelector(`[onclick="showSection('${sectionId}')"]`);
            if (activeMenu) {
                activeMenu.classList.add('active');
            }

            // Sembunyikan semua section
            document.querySelectorAll('.section').forEach(section => {
                section.classList.add('hidden');
                section.classList.remove('fade-in');
            });

            // Tampilkan section yang dipilih
            const selectedSection = document.getElementById(sectionId);
            if (selectedSection) {
                selectedSection.classList.remove('hidden');
                selectedSection.classList.add('fade-in');
            }
        }

        // Inisialisasi default saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Tampilkan dashboard sebagai default
            showSection('dashboard');

            // Tab default data remaja
            const defaultRemajaTab = document.getElementById('remaja-ujungpadang-tab');
            if (defaultRemajaTab) {
                defaultRemajaTab.classList.remove('hidden');
                defaultRemajaTab.classList.add('fade-in');
            }

            const firstRemajaButton = document.querySelector('#data_remaja .btn-group .btn:first-child');
            if (firstRemajaButton) {
                firstRemajaButton.classList.add('active');
            }

            // Tab default data anak
            const defaultAnakTab = document.getElementById('anak-ujungpadang-tab');
            if (defaultAnakTab) {
                defaultAnakTab.classList.remove('hidden');
                defaultAnakTab.classList.add('fade-in');
            }

            const firstAnakButton = document.querySelector('#data_anak .btn-group .btn:first-child');
            if (firstAnakButton) {
                firstAnakButton.classList.add('active');
            }

            // Konfirmasi logout
            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Apakah Anda yakin ingin logout?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#d63384',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, logout',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'logout.php';
                        }
                    });
                });
            }
        });
    </script>
    <?php if (isset($_SESSION['hapus_sukses'])): ?>
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: 'Akun telah dihapus.',
                icon: 'success',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    <?php unset($_SESSION['hapus_sukses']);
    endif; ?>

    <?php if (isset($_SESSION['hapus_gagal'])): ?>
        <script>
            Swal.fire({
                title: 'Gagal!',
                text: 'Akun gagal dihapus.',
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    <?php unset($_SESSION['hapus_gagal']);
    endif; ?>

    <?php if (isset($_SESSION['edit_sukses'])): ?>
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: 'Akun berhasil diperbarui.',
                icon: 'success',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    <?php unset($_SESSION['edit_sukses']);
    endif; ?>

    <?php if (isset($_SESSION['edit_gagal'])): ?>
        <script>
            Swal.fire({
                title: 'Gagal!',
                text: 'Akun gagal diperbarui.',
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    <?php unset($_SESSION['edit_gagal']);
    endif; ?>

    <?php if (isset($_SESSION['tambah_sukses'])): ?>
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: 'Data berhasil ditambahkan.',
                icon: 'success',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    <?php unset($_SESSION['tambah_sukses']);
    endif; ?>

    <?php if (isset($_SESSION['tambah_gagal'])): ?>
        <script>
            Swal.fire({
                title: 'Gagal!',
                text: 'Data gagal ditambahkan.',
                icon: 'error',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    <?php unset($_SESSION['tambah_gagal']);
    endif; ?>




</body>

</html>