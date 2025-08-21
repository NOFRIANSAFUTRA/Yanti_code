<?php
session_start();
include 'db.php';

// PROSES LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// PROSES INPUT DATA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'] ?? '';
    $materi = $_POST['materi'] ?? '';
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $lokasi = $_POST['lokasi'] ?? '';
    $desa = $_POST['desa'] ?? 'Desa Default';
    $petugas = $_POST['petugas'] ?? 'Petugas Default';

    if (!empty($judul) && !empty($materi)) {
        $stmt = $conn->prepare("INSERT INTO penyuluhan 
  (judul, materi, tanggal, lokasi, desa, petugas, status) 
  VALUES (?, ?, ?, ?, ?, ?, 'approved')");
        $stmt->bind_param("ssssss", $judul, $materi, $tanggal, $lokasi, $desa, $petugas);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Data penyuluhan berhasil disimpan untuk Desa $desa";
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Error: " . $conn->error;
            $_SESSION['message_type'] = 'error';
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// PROSES HAPUS DATA
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM penyuluhan WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Data berhasil dihapus!";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        $_SESSION['message_type'] = 'error';
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// AMBIL DATA UNTUK DITAMPILKAN
$result = $conn->query("SELECT * FROM penyuluhan ORDER BY tanggal DESC LIMIT 10");
$data_penyuluhan = $result->fetch_all(MYSQLI_ASSOC);

// Hitung statistik
$stats = [
    'total' => $conn->query("SELECT COUNT(*) as total FROM penyuluhan")->fetch_assoc()['total'],
    'peserta' => $conn->query("SELECT SUM(peserta) as total FROM penyuluhan")->fetch_assoc()['total'] ?? 0,
    'desa' => $conn->query("SELECT COUNT(DISTINCT desa) as total FROM penyuluhan")->fetch_assoc()['total'],
    'bulan_ini' => $conn->query("SELECT COUNT(*) as total FROM penyuluhan WHERE MONTH(tanggal) = MONTH(NOW()) AND YEAR(tanggal) = YEAR(NOW())")->fetch_assoc()['total']
];

// Data petugas (simulasikan data dari session/database)
$petugas = [
    'nama' => $_SESSION['username'] ?? 'Petugas',
    'foto' => 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['username'] ?? 'P') . '&background=4f46e5&color=fff'
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penyuluhan</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.95);
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.2));
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.3));
        }

        .gradient-text {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(79, 70, 229, 0.4);
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
        }

        .form-input {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .form-input:focus {
            background: rgba(255, 255, 255, 1);
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .table-row {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.7);
        }

        .table-row:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateX(4px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .animate-slide-up {
            animation: slideUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .animate-slide-in-right {
            animation: slideInRight 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .animate-fade-in-scale {
            animation: fadeInScale 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .floating {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        .profile-pic {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .profile-pic:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        }

        .notification-slide {
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .icon-bounce:hover {
            animation: bounce 0.6s ease-in-out;
        }

        @keyframes bounce {
            0%, 20%, 60%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            80% { transform: translateY(-5px); }
        }

        .data-empty {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #4338ca, #6d28d9);
        }

        .badge-desa {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(124, 58, 237, 0.1));
            border: 1px solid rgba(79, 70, 229, 0.2);
            transition: all 0.3s ease;
        }

        .badge-desa:hover {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.2), rgba(124, 58, 237, 0.2));
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <!-- Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-white opacity-10 rounded-full floating"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-white opacity-5 rounded-full floating" style="animation-delay: -3s;"></div>
        <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-white opacity-5 rounded-full floating" style="animation-delay: -1.5s;"></div>
    </div>

    <div class="min-h-screen relative z-10">
        <!-- Header -->
        <header class="glass-effect shadow-2xl border-b border-white/20">
            <div class="container mx-auto px-6 py-6">
                <div class="flex justify-between items-center">
                    <div class="animate-slide-up">
                        <h1 class="text-3xl font-bold text-white">
                            <i class="fas fa-chalkboard-teacher mr-3 text-yellow-300 icon-bounce"></i>
                            <span class="gradient-text">Dashboard Penyuluhan</span>
                        </h1>
                        <p class="text-white/80 mt-2 text-lg">Sistem Informasi Penyuluhan Masyarakat</p>
                    </div>
                    <div class="flex items-center space-x-6 animate-slide-in-right">
                        <div class="relative" id="profile-dropdown">
                            <button onclick="toggleDropdown()" class="flex items-center space-x-3 focus:outline-none bg-white/10 rounded-full p-2 hover:bg-white/20 transition-all duration-300">
                                <img src="<?php echo $petugas['foto']; ?>" alt="Profile" class="profile-pic">
                                <div class="text-left hidden md:block">
                                    <div class="text-blue font-semibold"><?php echo $petugas['nama']; ?></div>
                                    <div class="text-white/70 text-sm">Petugas Penyuluhan</div>
                                </div>
                                <i class="fas fa-chevron-down text-white/70 text-sm transition-transform duration-300" id="dropdown-arrow"></i>
                            </button>
                            <div id="dropdown-menu" class="absolute right-0 mt-3 w-64 glass-effect rounded-2xl shadow-2xl py-2 z-50 hidden border border-white/20">
                                <div class="px-4 py-3 border-b border-white/10">
                                    <div class="text-gray-800 font-semibold"><?php echo $petugas['nama']; ?></div>
                                    <div class="text-gray-600 text-sm">petugas@penyuluhan.id</div>
                                </div>
                                <a href="profile.php" class="block px-4 py-3 text-gray-700 hover:bg-white/50 transition-colors">
                                    <i class="fas fa-user mr-3 text-indigo-600"></i>Profil Saya
                                </a>
                                <a href="settings.php" class="block px-4 py-3 text-gray-700 hover:bg-white/50 transition-colors">
                                    <i class="fas fa-cog mr-3 text-indigo-600"></i>Pengaturan
                                </a>
                                <hr class="border-white/10 my-2">
                                <a href="login.php?logout" class="block px-4 py-3 text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-sign-out-alt mr-3"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-6 py-8">
            <!-- Notification -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="notification-slide mb-8 p-6 rounded-2xl shadow-xl border <?php echo $_SESSION['message_type'] === 'success' ? 'bg-green-50 text-green-800 border-green-200' : 'bg-red-50 text-red-800 border-red-200'; ?>">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-2 rounded-full <?php echo $_SESSION['message_type'] === 'success' ? 'bg-green-100' : 'bg-red-100'; ?> mr-4">
                                <i class="fas <?php echo $_SESSION['message_type'] === 'success' ? 'fa-check-circle text-green-600' : 'fa-exclamation-circle text-red-600'; ?> text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-lg"><?php echo $_SESSION['message_type'] === 'success' ? 'Berhasil!' : 'Error!'; ?></h4>
                                <p><?php echo $_SESSION['message']; ?></p>
                            </div>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="text-xl hover:scale-110 transition-transform">&times;</button>
                    </div>
                </div>
                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <div class="stat-card rounded-2xl shadow-2xl p-6 text-white animate-fade-in-scale">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium">Total Penyuluhan</p>
                            <h3 class="text-3xl font-bold mt-2"><?php echo number_format($stats['total']); ?></h3>
                            <p class="text-white/60 text-xs mt-2">
                                <i class="fas fa-chart-line mr-1"></i>
                                Semua waktu
                            </p>
                        </div>
                        <div class="p-4 bg-white/10 rounded-2xl">
                            <i class="fas fa-calendar-alt text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card rounded-2xl shadow-2xl p-6 text-white animate-fade-in-scale" style="animation-delay: 0.1s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium">Bulan Ini</p>
                            <h3 class="text-3xl font-bold mt-2"><?php echo number_format($stats['bulan_ini']); ?></h3>
                            <p class="text-white/60 text-xs mt-2">
                                <i class="fas fa-calendar-day mr-1"></i>
                                <?php echo date('M Y'); ?>
                            </p>
                        </div>
                        <div class="p-4 bg-white/10 rounded-2xl">
                            <i class="fas fa-calendar-check text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card rounded-2xl shadow-2xl p-6 text-white animate-fade-in-scale" style="animation-delay: 0.2s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium">Desa Terjangkau</p>
                            <h3 class="text-3xl font-bold mt-2"><?php echo number_format($stats['desa']); ?></h3>
                            <p class="text-white/60 text-xs mt-2">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                Lokasi aktif
                            </p>
                        </div>
                        <div class="p-4 bg-white/10 rounded-2xl">
                            <i class="fas fa-map-marked-alt text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card rounded-2xl shadow-2xl p-6 text-white animate-fade-in-scale" style="animation-delay: 0.3s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium">Status</p>
                            <h3 class="text-xl font-bold mt-2 text-green-300">Aktif</h3>
                            <p class="text-white/60 text-xs mt-2">
                                <i class="fas fa-check-circle mr-1"></i>
                                Sistem online
                            </p>
                        </div>
                        <div class="p-4 bg-white/10 rounded-2xl">
                            <i class="fas fa-server text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                <!-- Form Input -->
                <div class="lg:col-span-2">
                    <div class="glass-card rounded-3xl shadow-2xl overflow-hidden animate-slide-up">
                        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-800 px-8 py-6">
                            <h2 class="text-2xl font-bold text-white">
                                <i class="fas fa-plus-circle mr-3 text-yellow-300"></i>
                                Input Data Penyuluhan
                            </h2>
                            <p class="text-white/80 mt-2">Tambahkan data penyuluhan baru</p>
                        </div>
                        <div class="p-8">
                            <form method="POST" class="space-y-6">
                                <div class="space-y-2">
                                    <label class="block text-gray-800 text-sm font-semibold" for="judul">
                                        <i class="fas fa-heading mr-2 text-indigo-600"></i>
                                        Judul Penyuluhan
                                    </label>
                                    <input class="form-input w-full px-4 py-3 rounded-xl focus:ring-4 focus:ring-indigo-500/20 outline-none transition-all"
                                        type="text" id="judul" name="judul" placeholder="Masukkan judul penyuluhan..." required>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-gray-800 text-sm font-semibold" for="materi">
                                        <i class="fas fa-file-alt mr-2 text-indigo-600"></i>
                                        Materi Penyuluhan
                                    </label>
                                    <textarea class="form-input w-full px-4 py-3 rounded-xl focus:ring-4 focus:ring-indigo-500/20 outline-none resize-none transition-all"
                                        id="materi" name="materi" rows="4" placeholder="Deskripsi materi yang akan disampaikan..." required></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="block text-gray-800 text-sm font-semibold" for="tanggal">
                                            <i class="fas fa-calendar mr-2 text-indigo-600"></i>
                                            Tanggal
                                        </label>
                                        <input class="form-input w-full px-4 py-3 rounded-xl focus:ring-4 focus:ring-indigo-500/20 outline-none transition-all"
                                            type="date" id="tanggal" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-gray-800 text-sm font-semibold" for="lokasi">
                                            <i class="fas fa-map-marker-alt mr-2 text-indigo-600"></i>
                                            Lokasi
                                        </label>
                                        <input class="form-input w-full px-4 py-3 rounded-xl focus:ring-4 focus:ring-indigo-500/20 outline-none transition-all"
                                            type="text" id="lokasi" name="lokasi" placeholder="Balai desa, posyandu..." required>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="block text-gray-800 text-sm font-semibold" for="desa">
                                            <i class="fas fa-home mr-2 text-indigo-600"></i>
                                            Desa
                                        </label>
                                        <input class="form-input w-full px-4 py-3 rounded-xl focus:ring-4 focus:ring-indigo-500/20 outline-none transition-all"
                                            type="text" id="desa" name="desa" placeholder="Nama desa..." required>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-gray-800 text-sm font-semibold" for="petugas">
                                            <i class="fas fa-user-tie mr-2 text-indigo-600"></i>
                                            Nama Petugas
                                        </label>
                                        <input class="form-input w-full px-4 py-3 rounded-xl focus:ring-4 focus:ring-indigo-500/20 outline-none transition-all"
                                            type="text" id="petugas" name="petugas" placeholder="Petugas yang bertugas..." required>
                                    </div>
                                </div>

                                <button type="submit" class="btn-primary w-full py-4 px-6 rounded-xl text-white font-semibold text-lg hover:shadow-2xl transition-all duration-300 flex items-center justify-center">
                                    <i class="fas fa-save mr-3"></i>
                                    Simpan Data Penyuluhan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="lg:col-span-3">
                    <div class="glass-card rounded-3xl shadow-2xl overflow-hidden animate-slide-in-right">
                        <div class="bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-800 px-8 py-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-2xl font-bold text-white">
                                        <i class="fas fa-history mr-3 text-yellow-300"></i>
                                        Data Penyuluhan Terbaru
                                    </h2>
                                    <p class="text-white/80 mt-2">10 data penyuluhan terakhir</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-white/80 text-sm">Total</div>
                                    <div class="text-2xl font-bold text-white"><?php echo number_format($stats['total']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="p-8">
                            <?php if (empty($data_penyuluhan)): ?>
                                <div class="data-empty text-center py-16 rounded-2xl">
                                    <div class="floating">
                                        <i class="fas fa-inbox text-6xl mb-6 text-gray-400"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Data</h3>
                                    <p class="text-gray-500 mb-6">Mulai tambahkan data penyuluhan pertama Anda</p>
                                    <button onclick="document.getElementById('judul').focus()" class="btn-primary px-6 py-3 rounded-xl text-white font-medium">
                                        <i class="fas fa-plus mr-2"></i>
                                        Tambah Data Pertama
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($data_penyuluhan as $index => $data): ?>
                                        <div class="table-row p-6 rounded-2xl border border-white/20 hover:border-indigo-300 animate-fade-in-scale" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center mb-3">
                                                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold mr-4">
                                                            <?php echo $index + 1; ?>
                                                        </div>
                                                        <div class="flex-1">
                                                            <h4 class="font-bold text-gray-900 text-lg mb-1">
                                                                <?php echo htmlspecialchars($data['judul']); ?>
                                                            </h4>
                                                            <div class="flex items-center text-sm text-gray-500 space-x-4">
                                                                <span>
                                                                    <i class="fas fa-calendar-alt mr-1 text-indigo-500"></i>
                                                                    <?php echo date('d M Y', strtotime($data['tanggal'])); ?>
                                                                </span>
                                                                <span class="badge-desa px-3 py-1 rounded-full text-indigo-700 font-medium">
                                                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                                                    <?php echo htmlspecialchars($data['desa']); ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="ml-12">
                                                        <p class="text-gray-600 leading-relaxed">
                                                            <?php echo substr(htmlspecialchars($data['materi']), 0, 120); ?>
                                                            <?php if (strlen($data['materi']) > 120): ?>
                                                                <span class="text-indigo-600 cursor-pointer font-medium hover:text-indigo-800" onclick="toggleDetail(<?php echo $data['id']; ?>)">
                                                                    ... Lihat selengkapnya
                                                                </span>
                                                            <?php endif; ?>
                                                        </p>
                                                        <?php if (strlen($data['materi']) > 120): ?>
                                                            <div id="detail-<?php echo $data['id']; ?>" class="hidden mt-3 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl border-l-4 border-indigo-500">
                                                                <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($data['materi'])); ?></p>
                                                                <button onclick="toggleDetail(<?php echo $data['id']; ?>)" class="mt-2 text-indigo-600 text-sm font-medium hover:text-indigo-800">
                                                                    <i class="fas fa-chevron-up mr-1"></i>Sembunyikan
                                                                </button>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($data['lokasi'])): ?>
                                                            <div class="mt-2 text-sm text-gray-500">
                                                                <i class="fas fa-map-pin mr-1 text-indigo-400"></i>
                                                                <?php echo htmlspecialchars($data['lokasi']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-3 ml-4">
                                                    <button onclick="viewDetailModal(<?php echo htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8'); ?>)" 
                                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors icon-bounce" 
                                                            title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="edit_penyuluhan.php?id=<?php echo $data['id']; ?>" 
                                                       class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors icon-bounce" 
                                                       title="Edit Data">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?hapus=<?php echo $data['id']; ?>" 
                                                       class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors icon-bounce" 
                                                       onclick="return confirm('⚠️ Yakin ingin menghapus penyuluhan \"<?php echo htmlspecialchars($data['judul']); ?>\"?\n\nData yang dihapus tidak dapat dikembalikan!')"
                                                       title="Hapus Data">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="mt-8 flex justify-between items-center p-6 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl border border-indigo-100">
                                    <div class="text-gray-700 flex items-center">
                                        <i class="fas fa-info-circle mr-2 text-indigo-500"></i>
                                        Menampilkan <span class="font-bold mx-1"><?php echo count($data_penyuluhan); ?></span> dari <span class="font-bold mx-1"><?php echo number_format($stats['total']); ?></span> data
                                    </div>
                                    <a href="lihat_semua_penyuluhan.php" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold px-6 py-3 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg flex items-center">
                                        Lihat Semua Data 
                                        <i class="fas fa-arrow-right ml-2"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="glass-card rounded-2xl p-6 hover:scale-105 transition-all duration-300 cursor-pointer" onclick="window.location.href='laporan.php'">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-chart-bar text-white text-2xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">Laporan</h3>
                        <p class="text-gray-600 text-sm">Lihat laporan dan statistik penyuluhan</p>
                    </div>
                </div>

                <div class="glass-card rounded-2xl p-6 hover:scale-105 transition-all duration-300 cursor-pointer" onclick="window.location.href='kalender.php'">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calendar-week text-white text-2xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">Kalender</h3>
                        <p class="text-gray-600 text-sm">Jadwal penyuluhan mendatang</p>
                    </div>
                </div>

                <div class="glass-card rounded-2xl p-6 hover:scale-105 transition-all duration-300 cursor-pointer" onclick="window.location.href='peserta.php'">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-red-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-users text-white text-2xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">Peserta</h3>
                        <p class="text-gray-600 text-sm">Kelola data peserta penyuluhan</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="glass-effect border-t border-white/20 mt-16">
            <div class="container mx-auto px-6 py-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-gray-700 text-sm mb-4 md:mb-0 flex items-center">
                        <i class="fas fa-heart text-red-500 mr-2"></i>
                        &copy; <?php echo date('Y'); ?> Sistem Penyuluhan Masyarakat - Dibuat dengan penuh dedikasi
                    </div>
                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                        <span class="flex items-center">
                            <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                            Status: Online
                        </span>
                        <span>|</span>
                        <span>Login sebagai: <span class="font-semibold text-indigo-600"><?php echo $petugas['nama']; ?></span></span>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
        <div class="glass-effect rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden border border-white/20">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-700 px-8 py-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-white">
                        <i class="fas fa-info-circle mr-3 text-yellow-300"></i>
                        Detail Penyuluhan
                    </h3>
                    <button onclick="closeModal()" class="text-white/80 hover:text-white hover:bg-white/20 w-10 h-10 rounded-full flex items-center justify-center transition-all">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-8 overflow-y-auto" id="modalContent">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        // Toggle dropdown
        function toggleDropdown() {
            const menu = document.getElementById('dropdown-menu');
            const arrow = document.getElementById('dropdown-arrow');

            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                arrow.classList.add('rotate-180');
            } else {
                menu.classList.add('hidden');
                arrow.classList.remove('rotate-180');
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profile-dropdown');
            if (!dropdown.contains(event.target)) {
                document.getElementById('dropdown-menu').classList.add('hidden');
                document.getElementById('dropdown-arrow').classList.remove('rotate-180');
            }
        });

        // Toggle detail content
        function toggleDetail(id) {
            const detail = document.getElementById('detail-' + id);
            if (detail.classList.contains('hidden')) {
                detail.classList.remove('hidden');
                detail.classList.add('animate-fade-in-scale');
            } else {
                detail.classList.add('hidden');
            }
        }

        // View detail modal
        function viewDetailModal(data) {
            const modal = document.getElementById('detailModal');
            const content = document.getElementById('modalContent');
            
            const date = new Date(data.tanggal);
            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const dayName = dayNames[date.getDay()];
            const formattedDate = date.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            content.innerHTML = `
                <div class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="glass-effect p-6 rounded-2xl">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-calendar-alt mr-2 text-indigo-600"></i>
                                Tanggal & Waktu
                            </label>
                            <div class="text-gray-900 text-lg font-medium">
                                ${dayName}, ${formattedDate}
                            </div>
                        </div>
                        <div class="glass-effect p-6 rounded-2xl">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-map-marker-alt mr-2 text-indigo-600"></i>
                                Lokasi
                            </label>
                            <div class="text-gray-900 text-lg font-medium">
                                ${data.desa}${data.lokasi ? ', ' + data.lokasi : ''}
                            </div>
                        </div>
                    </div>
                    
                    <div class="glass-effect p-6 rounded-2xl">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-heading mr-2 text-indigo-600"></i>
                            Judul Penyuluhan
                        </label>
                        <h4 class="text-2xl font-bold text-gray-900">${data.judul}</h4>
                    </div>
                    
                    <div class="glass-effect p-6 rounded-2xl">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-file-alt mr-2 text-indigo-600"></i>
                            Materi Penyuluhan
                        </label>
                        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-6 rounded-xl border-l-4 border-indigo-500">
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line">${data.materi}</p>
                        </div>
                    </div>
                    
                    ${data.petugas ? `
                    <div class="glass-effect p-6 rounded-2xl">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-user-tie mr-2 text-indigo-600"></i>
                            Petugas
                        </label>
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <span class="text-lg font-medium text-gray-900">${data.petugas}</span>
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            
            modal.classList.remove('hidden');
        }

        // Close modal
        function closeModal() {
            document.getElementById('detailModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('detailModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Auto-hide notification after 8 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const notification = document.querySelector('.notification-slide');
            if (notification) {
                setTimeout(() => {
                    notification.style.transform = 'translateY(-100px)';
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 500);
                }, 8000);
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add loading state to form submission
        document.querySelector('form').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
            submitBtn.disabled = true;
            
            // Re-enable after 3 seconds (fallback)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });
    </script>
</body>
</html>

<?php
// Tutup koneksi database
mysqli_close($conn);
?>