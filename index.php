<?php
// Pastikan ini di LINE PERTAMA, tidak boleh ada spasi/enter sebelum <?php
session_start();

include 'db.php';

// Debug session
// echo "<pre>"; print_r($_SESSION); echo "</pre>"; exit();

// Tampilkan pesan
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            ' . $_SESSION['success_message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_messages'])) {
    foreach ($_SESSION['error_messages'] as $error) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                ' . $error . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
    unset($_SESSION['error_messages']);
}

// Query data
$query = "SELECT * FROM penyuluhan ORDER BY tanggal DESC LIMIT 10";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posyandu - Informasi Penyuluhan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .penyuluhan-card {
            border-left: 4px solid #4e73df;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: transform 0.2s ease;
        }

        .penyuluhan-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 2rem 0 rgba(58, 59, 69, 0.2);
        }

        .penyuluhan-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }

        .pending-card {
            border-left-color: #f6c23e;
            opacity: 0.9;
        }

        .approved-card {
            border-left-color: #1cc88a;
        }

        .login-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .login-btn:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
            margin-bottom: 3rem;
        }

        .hero-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .hero-section p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .info-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-top: -2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 10px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .date-badge {
            background: #e9ecef;
            color: #495057;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1><i class="fas fa-calendar-alt me-3"></i>Jadwal Penyuluhannn</h1>
                    <p class="lead">Informasi lengkap kegiatan penyuluhan kesehatan di wilayah Posyandu</p>
                </div>
                <div class="col-lg-4 text-end">
                    <i class="fas fa-users fa-5x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Info Card -->
        <div class="row">
            <div class="col-12">
                <div class="info-card">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <i class="fas fa-bullhorn fa-3x text-primary mb-3"></i>
                            <h5>Penyuluhan Terbaruuu</h5>
                            <p class="text-muted">Informasi kegiatan penyuluhan kesehatan terkini</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-map-marker-alt fa-3x text-success mb-3"></i>
                            <h5>Lokasi Strategis</h5>
                            <p class="text-muted">Tersebar di berbagai wilayah untuk kemudahan akses</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-user-md fa-3x text-info mb-3"></i>
                            <h5>Petugas Ahli</h5>
                            <p class="text-muted">Dipandu oleh tenaga kesehatan berpengalaman</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Penyuluhan Cards -->
        <div class="row mt-5">
            <div class="col-12">
                <h2 class="section-title">
                    <i class="fas fa-list-alt me-2"></i>Daftar Penyuluhan
                </h2>
            </div>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="row">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-lg-6 col-md-12">
                        <div class="card penyuluhan-card <?= $row['status'] == 'pending' ? 'pending-card' : 'approved-card' ?>">
                            <div class="card-header penyuluhan-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-clipboard-list me-2"></i>
                                    <?= htmlspecialchars($row['judul']) ?>
                                </h5>
                                <?php if ($row['status'] == 'pending'): ?>

                                <?php else: ?>
                                    
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <span class="date-badge">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d M Y', strtotime($row['tanggal'])) ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <h6 class="text-primary mb-2">
                                        <i class="fas fa-book me-2"></i>Materi Penyuluhan
                                    </h6>
                                    <p class="card-text"><?= htmlspecialchars($row['materi']) ?></p>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="card-text mb-2">
                                            <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                            <strong>Lokasi:</strong> <?= htmlspecialchars($row['lokasi']) ?>
                                        </p>
                                    </div>
                      
                                </div>

                                

                                <hr class="my-3">
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="fas fa-user-md me-1"></i>
                                        Disampaikan oleh: <strong><?= htmlspecialchars($row['petugas']) ?></strong>
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        Desa: <strong><?= htmlspecialchars($row['desa']) ?></strong>
                                    </small>
                                </p>

                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Legend -->
         
        <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h4>Belum Ada Jadwal Penyuluhan</h4>
                        <p class="mb-0">Belum ada jadwal penyuluhan yang tersedia saat ini. Silakan cek kembali nanti.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Footer Info -->
        <div class="row mt-5 mb-5">
            <div class="col-12">
                <div class="card bg-light border-0">
                    <div class="card-body text-center py-4">
                        <h5 class="card-title">Butuh Informasi Lebih Lanjut?</h5>
                        <p class="card-text">Hubungi petugas Posyandu di wilayah Anda.</p>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Login Button -->
    <a href="login.php" class="login-btn">
        <i class="fas fa-sign-in-alt me-2"></i>Masuk ke Sistem
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>