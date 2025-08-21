<?php
// lihat_semua_penyuluhan.php

// Include koneksi database
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "posyandu_db";
$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Konfigurasi pagination
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Parameter pencarian dan filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$filter_desa = isset($_GET['desa']) ? mysqli_real_escape_string($conn, trim($_GET['desa'])) : '';
$filter_bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : '';
$filter_tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : '';

// Query dasar
$where_conditions = [];

// Kondisi pencarian
if (!empty($search)) {
    $where_conditions[] = "(judul LIKE '%$search%' OR materi LIKE '%$search%')";
}

// Filter desa
if (!empty($filter_desa)) {
    $where_conditions[] = "desa = '$filter_desa'";
}

// Filter bulan dan tahun
if (!empty($filter_bulan)) {
    $where_conditions[] = "MONTH(tanggal) = $filter_bulan";
}

if (!empty($filter_tahun)) {
    $where_conditions[] = "YEAR(tanggal) = $filter_tahun";
}

// Gabungkan kondisi WHERE
$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Query untuk menghitung total data
$count_query = "SELECT COUNT(*) as total FROM penyuluhan $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_data = mysqli_fetch_assoc($count_result)['total'];

// Hitung total halaman
$total_pages = ceil($total_data / $limit);

// Query untuk mengambil data dengan pagination
$data_query = "SELECT * FROM penyuluhan $where_clause ORDER BY tanggal DESC LIMIT $limit OFFSET $offset";
$data_result = mysqli_query($conn, $data_query);
$all_penyuluhan = [];
if ($data_result) {
    while ($row = mysqli_fetch_assoc($data_result)) {
        $all_penyuluhan[] = $row;
    }
}

// Query untuk mendapatkan daftar desa untuk filter
$desa_query = "SELECT DISTINCT desa FROM penyuluhan ORDER BY desa";
$desa_result = mysqli_query($conn, $desa_query);
$daftar_desa = [];
if ($desa_result) {
    while ($row = mysqli_fetch_assoc($desa_result)) {
        $daftar_desa[] = $row;
    }
}

// Hapus data jika ada parameter hapus
if (isset($_GET['hapus']) && !empty($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    $hapus_query = "DELETE FROM penyuluhan WHERE id = $id_hapus";
    if (mysqli_query($conn, $hapus_query)) {
        echo "<script>alert('Data berhasil dihapus!'); window.location.href='?';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menghapus data: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Data Penyuluhan</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <i class="fas fa-list-ul text-indigo-600 mr-3"></i>
                        Semua Data Penyuluhan
                    </h1>
                    <p class="text-gray-600 mt-2">Kelola dan lihat semua data penyuluhan</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Total Data</div>
                    <div class="text-2xl font-bold text-indigo-600"><?php echo number_format($total_data); ?></div>
                </div>
            </div>
        </div>

        <!-- Filter dan Pencarian -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <!-- Pencarian -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Cari judul atau materi..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <!-- Filter Desa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Desa</label>
                    <select name="desa" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Desa</option>
                        <?php foreach ($daftar_desa as $desa): ?>
                            <option value="<?php echo htmlspecialchars($desa['desa']); ?>" 
                                    <?php echo ($filter_desa === $desa['desa']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($desa['desa']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Bulan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                    <select name="bulan" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Bulan</option>
                        <?php
                        $bulan_names = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                        foreach ($bulan_names as $num => $name):
                        ?>
                            <option value="<?php echo $num; ?>" <?php echo ($filter_bulan == $num) ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Tahun -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <select name="tahun" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Tahun</option>
                        <?php
                        $current_year = date('Y');
                        for ($year = $current_year; $year >= ($current_year - 5); $year--):
                        ?>
                            <option value="<?php echo $year; ?>" <?php echo ($filter_tahun == $year) ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Tombol Filter -->
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                </div>
            </form>

            <!-- Reset Filter -->
            <?php if (!empty($search) || !empty($filter_desa) || !empty($filter_bulan) || !empty($filter_tahun)): ?>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="?" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-800">
                        <i class="fas fa-times mr-2"></i>Reset Filter
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pengaturan Tampilan -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">Tampilkan per halaman:</span>
                <select onchange="changeLimit(this.value)" class="border border-gray-300 rounded-lg px-3 py-1 text-sm">
                    <option value="10" <?php echo ($limit == 10) ? 'selected' : ''; ?>>10</option>
                    <option value="25" <?php echo ($limit == 25) ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?php echo ($limit == 50) ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo ($limit == 100) ? 'selected' : ''; ?>>100</option>
                </select>
            </div>
            <div class="text-sm text-gray-600">
                Menampilkan <?php echo (($page - 1) * $limit) + 1; ?> - 
                <?php echo min($page * $limit, $total_data); ?> dari 
                <?php echo number_format($total_data); ?> data
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <?php if (empty($all_penyuluhan)): ?>
                <div class="text-center py-16">
                    <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data ditemukan</h3>
                    <p class="text-gray-500">Coba ubah kriteria pencarian atau filter Anda</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-indigo-600 to-purple-700 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider">No</th>
                                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider">Judul & Materi</th>
                                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider">Desa</th>
                                <th class="px-6 py-4 text-center text-xs font-medium uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($all_penyuluhan as $index => $data): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">
                                        <?php echo (($page - 1) * $limit) + $index + 1; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo date('d M Y', strtotime($data['tanggal'])); ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo strftime('%A', strtotime($data['tanggal'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 mb-1">
                                            <?php echo htmlspecialchars($data['judul']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500 max-w-md">
                                            <?php echo substr(htmlspecialchars($data['materi']), 0, 100); ?>
                                            <?php if (strlen($data['materi']) > 100): ?>
                                                <span class="text-indigo-600 cursor-pointer" onclick="toggleMateri(<?php echo $data['id']; ?>)">
                                                    ... <i class="fas fa-eye text-xs"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <!-- Full materi (hidden by default) -->
                                        <div id="full-materi-<?php echo $data['id']; ?>" class="hidden text-sm text-gray-600 mt-2 p-3 bg-gray-50 rounded-lg">
                                            <?php echo nl2br(htmlspecialchars($data['materi'])); ?>
                                            <div class="mt-2">
                                                <span class="text-indigo-600 cursor-pointer text-xs" onclick="toggleMateri(<?php echo $data['id']; ?>)">
                                                    <i class="fas fa-eye-slash"></i> Sembunyikan
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            <?php echo htmlspecialchars($data['desa']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <button onclick="viewDetail(<?php echo htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8'); ?>)" 
                                                    class="text-blue-600 hover:text-blue-800 transition-colors" 
                                                    title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="edit_penyuluhan.php?id=<?php echo $data['id']; ?>" 
                                               class="text-green-600 hover:text-green-800 transition-colors" 
                                               title="Edit Data">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?hapus=<?php echo $data['id']; ?>&<?php echo http_build_query($_GET); ?>" 
                                               class="text-red-600 hover:text-red-800 transition-colors" 
                                               onclick="return confirm('Yakin ingin menghapus data penyuluhan \"<?php echo htmlspecialchars($data['judul']); ?>\"?')"
                                               title="Hapus Data">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>
                            </div>
                            <div class="flex space-x-2">
                                <!-- Previous Button -->
                                <?php if ($page > 1): ?>
                                    <?php 
                                    $prev_params = $_GET;
                                    $prev_params['page'] = $page - 1;
                                    ?>
                                    <a href="?<?php echo http_build_query($prev_params); ?>" 
                                       class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                        <i class="fas fa-chevron-left mr-1"></i>Sebelumnya
                                    </a>
                                <?php endif; ?>

                                <!-- Page Numbers -->
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                for ($i = $start_page; $i <= $end_page; $i++):
                                    $page_params = $_GET;
                                    $page_params['page'] = $i;
                                ?>
                                    <a href="?<?php echo http_build_query($page_params); ?>" 
                                       class="px-3 py-2 text-sm font-medium <?php echo ($i == $page) ? 'text-white bg-indigo-600 border-indigo-600' : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50'; ?> border rounded-lg">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <!-- Next Button -->
                                <?php if ($page < $total_pages): ?>
                                    <?php 
                                    $next_params = $_GET;
                                    $next_params['page'] = $page + 1;
                                    ?>
                                    <a href="?<?php echo http_build_query($next_params); ?>" 
                                       class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                        Selanjutnya<i class="fas fa-chevron-right ml-1"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Tombol Kembali -->
        <div class="mt-8">
            <a href="dashboard_petugas.php" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Modal Detail -->
    <div id="detailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">Detail Penyuluhan</h3>
                        <button onclick="closeModal()" class="text-white hover:text-gray-200">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6 overflow-y-auto" id="modalContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set locale for Indonesian day names
        const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        // Toggle full materi display
        function toggleMateri(id) {
            const fullMateri = document.getElementById('full-materi-' + id);
            if (fullMateri.classList.contains('hidden')) {
                fullMateri.classList.remove('hidden');
            } else {
                fullMateri.classList.add('hidden');
            }
        }

        // Change limit per page
        function changeLimit(newLimit) {
            const url = new URL(window.location);
            url.searchParams.set('limit', newLimit);
            url.searchParams.set('page', '1'); // Reset to first page
            window.location.href = url.toString();
        }

        // View detail modal
        function viewDetail(data) {
            const modal = document.getElementById('detailModal');
            const content = document.getElementById('modalContent');
            
            const date = new Date(data.tanggal);
            const dayName = dayNames[date.getDay()];
            const formattedDate = date.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            content.innerHTML = `
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                        <div class="flex items-center text-gray-900">
                            <i class="fas fa-calendar-alt text-indigo-600 mr-2"></i>
                            ${dayName}, ${formattedDate}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Judul</label>
                        <h4 class="text-lg font-semibold text-gray-900">${data.judul}</h4>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Desa</label>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-indigo-600 mr-2"></i>
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-medium">${data.desa}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Materi Penyuluhan</label>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-700 whitespace-pre-line">${data.materi}</p>
                        </div>
                    </div>
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
    </script>
</body>
</html>

<?php
// Tutup koneksi database
mysqli_close($conn);
?>