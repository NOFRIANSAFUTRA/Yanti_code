<?php
include '../db.php';
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .access-denied-container {
                background: white;
                border-radius: 20px;
                box-shadow: 0 25px 50px rgba(238, 90, 36, 0.2);
                padding: 40px;
                text-align: center;
                max-width: 520px;
                width: 100%;
                animation: slideIn 0.6s ease-out;
                position: relative;
                overflow: hidden;
            }
            
            .access-denied-container::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(90deg, #ff6b6b, #ee5a24, #ff6b6b);
                animation: shimmer 2s infinite;
            }
            
            @keyframes shimmer {
                0% { transform: translateX(-100%); }
                100% { transform: translateX(100%); }
            }
            
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-40px) scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }
            
            .error-icon {
                font-size: 90px;
                color: #e74c3c;
                margin-bottom: 25px;
                animation: pulse 2s infinite;
                filter: drop-shadow(0 4px 8px rgba(231, 76, 60, 0.3));
            }
            
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }
            
            .error-title {
                color: #2c3e50;
                font-size: 32px;
                font-weight: 800;
                margin-bottom: 15px;
                text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .error-subtitle {
                color: #e74c3c;
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 20px;
            }
            
            .error-message {
                color: #7f8c8d;
                font-size: 16px;
                line-height: 1.7;
                margin-bottom: 30px;
                padding: 25px;
                background: linear-gradient(145deg, #fef5f5, #fdf2f2);
                border-radius: 12px;
                border-left: 5px solid #e74c3c;
                position: relative;
            }
            
            .error-message::before {
                content: '⚠️';
                position: absolute;
                top: 10px;
                right: 15px;
                font-size: 20px;
            }
            
            .danger-alert {
                background: linear-gradient(145deg, #ffebee, #ffcdd2);
                border: 2px solid #ef5350;
                border-radius: 10px;
                padding: 18px;
                margin-bottom: 25px;
                color: #c62828;
                font-weight: 600;
                position: relative;
            }
            
            .danger-alert::before {
                position: absolute;
                top: 15px;
                left: 15px;
                font-size: 24px;
            }
            
            .danger-alert-text {
                margin-left: 40px;
                font-size: 15px;
            }
            
            .user-info {
                background: linear-gradient(145deg, #f8f9fa, #e9ecef);
                padding: 20px;
                border-radius: 10px;
                margin-bottom: 30px;
                font-size: 14px;
                color: #495057;
                border: 1px solid #dee2e6;
            }
            
            .user-info strong {
                color: #2c3e50;
            }
            
            .user-info .user-badge {
                display: inline-block;
                background: #6c757d;
                color: white;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12px;
                margin-left: 8px;
            }
            
            .btn-group {
                display: flex;
                gap: 15px;
                justify-content: center;
                flex-wrap: wrap;
                margin-bottom: 20px;
            }
            
            .btn {
                padding: 14px 28px;
                border: none;
                border-radius: 30px;
                text-decoration: none;
                font-weight: 700;
                font-size: 15px;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                display: inline-flex;
                align-items: center;
                gap: 10px;
                cursor: pointer;
                position: relative;
                overflow: hidden;
            }
            
            .btn::before {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                border-radius: 50%;
                background: rgba(255,255,255,0.3);
                transition: all 0.3s;
                transform: translate(-50%, -50%);
            }
            
            .btn:hover::before {
                width: 300px;
                height: 300px;
            }
            
            .btn-primary {
                background: linear-gradient(45deg, #3498db, #2980b9);
                color: white;
                box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            }
            
            .btn-primary:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
            }
            
            .btn-warning {
                background: linear-gradient(45deg, #f39c12, #e67e22);
                color: white;
                box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
            }
            
            .btn-warning:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 25px rgba(243, 156, 18, 0.4);
            }
            
            .footer-info {
                margin-top: 30px;
                padding-top: 25px;
                border-top: 2px solid #ecf0f1;
                font-size: 13px;
                color: #95a5a6;
            }
            
            .security-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: linear-gradient(45deg, #e8f5e8, #d4edda);
                color: #155724;
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
                margin-top: 15px;
                border: 1px solid #c3e6cb;
            }
            
            .warning-text {
                margin-top: 15px;
                padding: 12px;
                background: #fff3cd;
                border: 1px solid #ffeaa7;
                border-radius: 8px;
                color: #856404;
                font-size: 12px;
            }
            
            @media (max-width: 480px) {
                .access-denied-container {
                    padding: 30px 20px;
                    margin: 10px;
                }
                
                .error-title {
                    font-size: 26px;
                }
                
                .error-icon {
                    font-size: 70px;
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
            <div class="error-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            
            <h1 class="error-title">Akses Terbatas</h1>
            <div class="error-subtitle">Operasi Penghapusan Data</div>
            
            <div class="danger-alert">
                <div class="danger-alert-text">
                    <strong>ZONA BERBAHAYA!</strong><br>
                    Halaman ini digunakan untuk menghapus data lansia secara permanen
                </div>
            </div>
            
            <div class="error-message">
                <strong>Fitur Penghapusan Data Lansia</strong> memiliki akses super terbatas dan hanya dapat digunakan oleh <strong>Kader bernama Farmala</strong> untuk mencegah penghapusan data yang tidak disengaja atau tidak sah.
                <br><br>
                <small><i class="fas fa-info-circle"></i> Data yang dihapus tidak dapat dikembalikan lagi.</small>
            </div>
            
            <?php if (isset($_SESSION['username']) && isset($_SESSION['role'])): ?>
            <div class="user-info">
                <i class="fas fa-user-circle" style="margin-right: 10px; font-size: 16px;"></i>
                <strong>Informasi Sesi Aktif:</strong><br><br>
                Username: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                <span class="user-badge"><?php echo htmlspecialchars($_SESSION['role']); ?></span><br>
                Status: <span style="color: #e74c3c; font-weight: 600;">Tidak Memiliki Izin Hapus</span>
            </div>
            <?php endif; ?>
            
            <div class="btn-group">
                <a href="../dashboard_kader.php" class="btn btn-primary">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
            
            <div class="footer-info">
                <div class="security-badge">
                    <i class="fas fa-shield-alt"></i>
                    <span>Sistem Keamanan Tingkat Tinggi</span>
                </div>
                
                <div class="warning-text">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Peringatan:</strong> Akses tidak sah ke fitur penghapusan data dapat dikenakan sanksi sesuai kebijakan sistem.
                </div>
                
                <br>
                <i class="fas fa-headset"></i>
                Butuh bantuan? Hubungi administrator sistem posyandu.
                <br>
                <small><strong>Error Code:</strong> 403-DELETE-FORBIDDEN</small>
            </div>
        </div>
        
       
    </body>
    </html>
    <?php
    exit();
}

// Cek akses dengan tampilan yang lebih baik
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kader' || $_SESSION['username'] !== 'Farmala') {
    showAccessDenied();
}

$id = $_GET['id'] ?? 0;

if ($id > 0) {
    // Cek apakah data ada
    $stmt = $conn->prepare("SELECT nama FROM lansia_Farmala WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // Log aktivitas penghapusan untuk audit trail
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user' => $_SESSION['username'],
            'action' => 'DELETE_LANSIA_DATA',
            'lansia_id' => $id,
            'lansia_name' => $data['nama'],
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        // Log ke file jika direktori logs ada
        if (is_dir('logs/') && is_writable('logs/')) {
            file_put_contents(
                'logs/delete_activity.log', 
                json_encode($log_data) . PHP_EOL, 
                FILE_APPEND | LOCK_EX
            );
        }
        
        // Hapus data
        $stmt = $conn->prepare("DELETE FROM lansia_Farmala WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Berhasil hapus - tampilkan SweetAlert sukses
            ?>
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Data Berhasil Dihapus</title>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data lansia "<?php echo htmlspecialchars($data['nama']); ?>" berhasil dihapus.',
                        showConfirmButton: true,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '../dashboard_kader.php';
                        }
                    });
                </script>
            </body>
            </html>
            <?php
            exit();
        } else {
            // Gagal hapus - tampilkan SweetAlert error
            ?>
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Gagal Menghapus Data</title>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat menghapus data. Silakan coba lagi.',
                        showConfirmButton: true,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '../dashboard_kader.php';
                        }
                    });
                </script>
            </body>
            </html>
            <?php
            exit();
        }
    } else {
        // Data tidak ditemukan - tampilkan SweetAlert error
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Data Tidak Ditemukan</title>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Data Tidak Ditemukan!',
                    text: 'Data lansia yang ingin dihapus tidak tersedia.',
                    showConfirmButton: true,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '../dashboard_kader.php';
                    }
                });
            </script>
        </body>
        </html>
        <?php
        exit();
    }
} else {
    // ID tidak valid - tampilkan SweetAlert error
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ID Tidak Valid</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'ID Tidak Valid!',
                text: 'Parameter ID yang diberikan tidak valid.',
                showConfirmButton: true,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../dashboard_kader.php';
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit();
}
?>