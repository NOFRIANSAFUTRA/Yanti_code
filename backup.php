<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kader') {
    header("Location: login.php");
    exit();
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$desa_kader = isset($_SESSION['desa']) ? $_SESSION['desa'] : '';

include 'db.php';

    $desa_kader = $_SESSION['desa']; // Desa kader yang login

    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);

        // Cek dulu apakah data anak dengan id ini memang dari desa kader yang login
        $stmt = $conn->prepare("SELECT desa FROM anak WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && $row['desa'] === $desa_kader) {
            // Kalau desa cocok, boleh hapus
            $stmt_del = $conn->prepare("DELETE FROM anak WHERE id = ?");
            $stmt_del->bind_param("i", $id);
            $stmt_del->execute();
            $stmt_del->close();
        } else {
            // Kalau tidak cocok, tolak hapus
            echo "Error: Anda tidak berhak menghapus data dari desa lain.";
            exit();
        }

        $stmt->close();
        header("Location: anak_list.php");
        exit();
    }

    ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kader Posyandu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .btn-group {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.anak-tab {
    transition: all 0.3s ease;
}

.anak-tab.hidden {
    display: none;
}
        :root {
               --primary:rgb(214, 51, 189);        /* Magenta / pink-ungu */
    --primary-dark:rgb(156, 35, 130);   /* Lebih gelap, tone pink-ungu */
    --secondary: #e599f7;      /* Ungu muda agak pink */
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
           --info: #f8c4ec;           /* Pink pastel, untuk elemen info */
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

        /* Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg, var(--primary), var(--info));
            color: var(--white);
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.2);
            position: relative;
            overflow: hidden;
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .welcome-card h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .welcome-card p {
            font-size: 1rem;
            opacity: 0.9;
            max-width: 600px;
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

        .table th, .table td {
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
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
        }
    </style>
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Posyandu Sehat</h3>
                <p>Dashboard Kader</p>
            </div>
            
      <nav class="sidebar-menu">
    <a href="javascript:void(0);" class="menu-item active" onclick="showSection('welcome')">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
    <a href="javascript:void(0);" class="menu-item" onclick="showSection('anak')">
        <i class="fas fa-baby"></i>
        <span>Data Anak</span>
    </a>
    <a href="javascript:void(0);" class="menu-item" onclick="showSection('remaja')">
        <i class="fas fa-baby"></i>
        <span>Data Remaja</span>
    </a>
    <a href="javascript:void(0);" class="menu-item" onclick="showSection('lansia')">
        <i class="fas fa-baby"></i>
        <span>Data Lansia</span>
    </a>
                <a href="javascript:void(0);" class="menu-item" onclick="showSection('penimbangan')">
                    <i class="fas fa-weight"></i>
                    <span>Penimbangan</span>
                </a>
                <a href="javascript:void(0);" class="menu-item" onclick="showSection('kehadiran')">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Kehadiran</span>
                </a>
                <a href="javascript:void(0);" class="menu-item" onclick="showSection('grafik')">
                    <i class="fas fa-chart-line"></i>
                    <span>Informasi Gizi</span>
                </a>
                <a href="javascript:void(0);" class="menu-item" onclick="showSection('penyuluhan')">
                    <i class="fas fa-book"></i>
                    <span>Penyuluhan</span>
                </a>
                <a href="javascript:void(0);" class="menu-item" onclick="showSection('jadwal')">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Jadwal</span>
                </a>
            </nav>
            
            <a href="logout.php" class="btn btn-primary logout-btn" onclick="return confirm('Yakin ingin logout?')">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Welcome Section -->
            <div class="welcome-card fade-in" id="welcome">
                <h2>Selamat datang, <?php echo htmlspecialchars($username); ?>!</h2>
                <p>Anda login sebagai Kader Posyandu. Gunakan menu di samping untuk mengelola data dan kegiatan posyandu.</p>
            </div>

            <!-- Data Anak Section -->
    <!-- Data Anak Section -->
<div class="section hidden fade-in" id="anak">
    <div class="section-header">
        <h3><i class="fas fa-baby"></i> Kelola Data Anak</h3>
        <div class="btn-group">
            <button class="btn btn-primary active" onclick="showAnakTab('sialang')">
                Desa Sialang
            </button>
            <button class="btn btn-primary" onclick="showAnakTab('ujungpadang')">
                Desa Ujung Padang
            </button>
            <button class="btn btn-primary" onclick="showAnakTab('baratdaya')">
                Desa Barat Daya
            </button>
              <button class="btn btn-primary" onclick="showAnakTab('kapeh')">
                Desa Kapeh
            </button>
            </button>
              <button class="btn btn-primary" onclick="showAnakTab('kedaikandang')">
                Desa Kedai Kandang
            </button>
            </button>
              <button class="btn btn-primary" onclick="showAnakTab('kedairunding')">
                Desa Kedai Runding
            </button>
            <a href="anak_add.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Tambah Data Anak
            </a>
        </div>
    </div>
    
    <!-- Tab Sialang -->
    <div id="anak-sialang-tab" class="anak-tab">
        <div class="table-responsive">
            <table class="table table-striped table-hover" style="font-size: 14px;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Anak</th>
                        <th>Jenis Kelamin</th>
                        <th>Tgl Lahir</th>
                        <th>Umur (bulan)</th>
                        <th>Nama Orang Tua</th>
                        <th>Alamat</th>
                        <th>BB Lalu</th>
                        <th>BB Ini</th>
                        <th>PB/TB Lalu</th>
                        <th>PB/TB Ini</th>
                        <th>LK Lalu</th>
                        <th>LK Ini</th>
                        <th>Status Gizi</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $query = mysqli_query($conn, "SELECT * FROM anak_yanti ORDER BY id DESC");
                    while ($row = mysqli_fetch_assoc($query)):
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['nama']); ?></td>
                        <td><?= htmlspecialchars($row['jk']); ?></td>
                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                        <td><?= (int)$row['umur']; ?></td>
                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                        <td><?= htmlspecialchars($row['ket']); ?></td>
                        <td class="actions">
                            <a href="anak_edit.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="anak_delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Tab Ujung Padang -->
    <div id="anak-ujungpadang-tab" class="anak-tab hidden">
        <div class="table-responsive">
            <table class="table table-striped table-hover" style="font-size: 14px;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Anak</th>
                        <th>Umur (bulan)</th>
                        <th>Nama Orang Tua</th>
                        <th>Alamat</th>
                        <th>BB Lalu</th>
                        <th>BB Ini</th>
                        <th>PB/TB Lalu</th>
                        <th>PB/TB Ini</th>
                        <th>LK Lalu</th>
                        <th>LK Ini</th>
                        <th>Status Gizi</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $result = $conn->query("SELECT * FROM anak ORDER BY id DESC");
                    while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['nama']); ?></td>
                        <td><?= (int)$row['umur']; ?></td>
                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                        <td><?= htmlspecialchars($row['ket']); ?></td>
                        <td class="actions">
                            <a href="anak_edit.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="anak_delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

 
    
    <!-- Tab barat daya-->
  <div id="anak-baratdaya-tab" class="anak-tab hidden">
        <div class="table-responsive">
            <table class="table table-striped table-hover" style="font-size: 14px;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Anak</th>
                        <th>Umur (bulan)</th>
                        <th>Nama Orang Tua</th>
                        <th>Alamat</th>
                        <th>BB Lalu</th>
                        <th>BB Ini</th>
                        <th>PB/TB Lalu</th>
                        <th>PB/TB Ini</th>
                        <th>LK Lalu</th>
                        <th>LK Ini</th>
                        <th>Status Gizi</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $result = $conn->query("SELECT * FROM anak_bariahh ORDER BY id DESC");
                    while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['nama']); ?></td>
                        <td><?= (int)$row['umur']; ?></td>
                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                        <td><?= htmlspecialchars($row['ket']); ?></td>
                        <td class="actions">
                            <a href="anak_edit.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="anak_delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- DESA Kapeh -->
    <div id="anak-kapeh-tab" class="anak-tab hidden">
        <div class="table-responsive">
            <table class="table table-striped table-hover" style="font-size: 14px;">
                <thead>
                    <tr>
                        <th>No</th> 
                        <th>Nama Anak</th>
                        <th>Umur (bulan)</th>
                        <th>Nama Orang Tua</th>
                        <th>Alamat</th>
                        <th>BB Lalu</th>
                        <th>BB Ini</th>
                        <th>PB/TB Lalu</th>
                        <th>PB/TB Ini</th>
                        <th>LK Lalu</th>
                        <th>LK Ini</th>
                        <th>Status Gizi</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $result = $conn->query("SELECT * FROM anak_nawawi ORDER BY id DESC");
                    while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['nama']); ?></td>
                        <td><?= (int)$row['umur']; ?></td>
                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                        <td><?= htmlspecialchars($row['ket']); ?></td>
                        <td class="actions">
                            <a href="anak_edit.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="anak_delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- kedai Kandang -->
 <div id="anak-kedaikandang-tab" class="anak-tab hidden">
        <div class="table-responsive">
            <table class="table table-striped table-hover" style="font-size: 14px;">
                <thead>
                    <tr>
                        <th>No</th> 
                        <th>Nama Anak</th>
                        <th>Umur (bulan)</th>
                        <th>Nama Orang Tua</th>
                        <th>Alamat</th>
                        <th>BB Lalu</th>
                        <th>BB Ini</th>
                        <th>PB/TB Lalu</th>
                        <th>PB/TB Ini</th>
                        <th>LK Lalu</th>
                        <th>LK Ini</th>
                        <th>Status Gizi</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $result = $conn->query("SELECT * FROM anak_al ORDER BY id DESC");
                    while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['nama']); ?></td>
                        <td><?= (int)$row['umur']; ?></td>
                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                        <td><?= htmlspecialchars($row['ket']); ?></td>
                        <td class="actions">
                            <a href="anak_edit.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="anak_delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div id="anak-kedairunding-tab" class="anak-tab hidden">
        <div class="table-responsive">
            <table class="table table-striped table-hover" style="font-size: 14px;">
                <thead>
                    <tr>
                        <th>No</th> 
                        <th>Nama Anak</th>
                        <th>Umur (bulan)</th>
                        <th>Nama Orang Tua</th>
                        <th>Alamat</th>
                        <th>BB Lalu</th>
                        <th>BB Ini</th>
                        <th>PB/TB Lalu</th>
                        <th>PB/TB Ini</th>
                        <th>LK Lalu</th>
                        <th>LK Ini</th>
                        <th>Status Gizi</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $result = $conn->query("SELECT * FROM anak_farmala ORDER BY id DESC");
                    while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['nama']); ?></td>
                        <td><?= (int)$row['umur']; ?></td>
                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                        <td><?= htmlspecialchars($row['ket']); ?></td>
                        <td class="actions">
                            <a href="anak_edit.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="anak_delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
     <div class="section hidden fade-in" id="remaja">
    <div class="section-header">
        <h3><i class="fas fa-baby"></i> Kelola Data Anak Remaja</h3>
        <div class="btn-group">
            <button class="btn btn-primary active" onclick="showremajaTab('sialang')">
                Desa Sialang
            </button>
            <button class="btn btn-primary" onclick="showremajaTab('ujungpadang')">
                Desa Ujung Padang
            </button>
            <button class="btn btn-primary" onclick="showremajaTab('baratdaya')">
                Desa Barat Daya
            </button>
              <button class="btn btn-primary" onclick="showremajaTab('kapeh')">
                Desa Kapeh
            </button>
            </button>
              <button class="btn btn-primary" onclick="showremajaTab('kedaikandang')">
                Desa Kedai Kandang
            </button>
            </button>
              <button class="btn btn-primary" onclick="showremajaTab('kedairunding')">
                Desa Kedai Runding
            </button>
            <a href="anak_remaja_add.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Tambah Data Anak Remaja
            </a>
        </div>
    </div>

                <div id="remaja-kedairunding-tab" class="anak-tab hidden">
        <div class="table-responsive">
            <table class="table table-striped table-hover" style="font-size: 14px;">
                <thead>
                    <tr>
                        <th>No</th> 
                        <th>Nama Anak</th>
                        <th>Umur (bulan)</th>
                        <th>Nama Orang Tua</th>
                        <th>Alamat</th>
                        <th>BB Lalu</th>
                        <th>BB Ini</th>
                        <th>PB/TB Lalu</th>
                        <th>PB/TB Ini</th>
                        <th>LK Lalu</th>
                        <th>LK Ini</th>
                        <th>Status Gizi</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $result = $conn->query("SELECT * FROM anak_farmala ORDER BY id DESC");
                    while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['nama']); ?></td>
                        <td><?= (int)$row['umur']; ?></td>
                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                        <td><?= htmlspecialchars($row['ket']); ?></td>
                        <td class="actions">
                            <a href="anak_edit.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="anak_delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
            </div>
             <div class="section hidden fade-in" id="lansia">
                <div class="section-header">
                    <h3><i class="fas fa-weight"></i> Input Data Penimbangan</h3>
                </div>
                <p>Masukkan tinggi, berat badan, dan usia anak, sistem otomatis menghitung status gizi (mengacu KMS).</p>
            </div>
              <div class="section hidden fade-in" id="penimbangan">
                <div class="section-header">
                    <h3><i class="fas fa-weight"></i> Input Data Penimbangan</h3>
                </div>
                <p>Masukkan tinggi, berat badan, dan usia anak, sistem otomatis menghitung status gizi (mengacu KMS).</p>
            </div>
             <!-- Penyuluhan Section -->
            <div class="section hidden fade-in" id="penyuluhan">
                <div class="section-header">
                    <h3><i class="fas fa-book"></i> Kegiatan Penyuluhan</h3>
                    <a href="#" class="btn btn-success">
                        <i class="fas fa-plus"></i> Tambah Kegiatan
                    </a>
                </div>
                <p>Input tema, peserta, dan tanggal kegiatan penyuluhan.</p>
            </div>
    </div>
 </div>
 </div>













            <!-- Penimbangan Section -->
          

            <!-- Kehadiran Section -->
            <div class="section hidden fade-in" id="kehadiran">
                <div class="section-header">
                    <h3><i class="fas fa-clipboard-check"></i> Input Kehadiran Anak</h3>
                </div>
                <p>Tandai anak-anak yang hadir dalam kegiatan posyandu.</p>
            </div>

            <!-- Grafik Section -->
            <div class="section hidden fade-in" id="grafik">
                <div class="section-header">
                    <h3><i class="fas fa-chart-line"></i> Grafik Gizi & Pertumbuhan</h3>
                </div>
                <p>Lihat grafik status gizi per anak sebagai referensi penyuluhan.</p>
            </div>

           

            <!-- Jadwal Section -->
            <div class="section hidden fade-in" id="jadwal">
                <div class="section-header">
                    <h3><i class="fas fa-calendar-alt"></i> Jadwal Kegiatan</h3>
                    <a href="#" class="btn btn-success">
                        <i class="fas fa-plus"></i> Tambah Jadwal
                    </a>
                </div>
                <p>Kelola jadwal kegiatan posyandu mendatang.</p>
            </div>
        </main>
    </div>
<script>
// Menampilkan section berdasarkan ID
function showSection(sectionId) {
    // Hapus class 'active' dari semua menu
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active');
    });

    // Tambahkan class 'active' ke menu yang diklik
    document.querySelector(`[onclick="showSection('${sectionId}')"]`).classList.add('active');

    // Sembunyikan semua section
    document.querySelectorAll('.section').forEach(section => {
        section.classList.add('hidden');
    });

    // Tampilkan section yang dipilih
    const selectedSection = document.getElementById(sectionId);
    if (selectedSection) {
        selectedSection.classList.remove('hidden');
        selectedSection.classList.add('fade-in');

        // Jika section-nya adalah anak, remaja, atau lansia, tampilkan tab default 'sialang'
        if (['anak', 'remaja', 'lansia'].includes(sectionId)) {
            showAnakTab('sialang', null); // tanpa event
        }
    }
}

// Menampilkan tab anak berdasarkan desa yang dipilih
function showAnakTab(desa, event) {
    // Sembunyikan semua tab anak
    document.querySelectorAll('.anak-tab').forEach(tab => {
        tab.classList.add('hidden');
    });

    // Tampilkan tab sesuai desa
    const targetTab = document.getElementById(`anak-${desa}-tab`);
    if (targetTab) {
        targetTab.classList.remove('hidden');
    }

    // Update tombol aktif
    document.querySelectorAll('#anak .btn-group button').forEach(btn => {
        btn.classList.remove('active');
    });

    // Jika ada event (misalnya diklik), tambahkan class active ke tombol yang diklik
    if (event) {
        event.target.classList.add('active');
    }
}
</script>

</body>
</html>