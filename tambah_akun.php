<?php
session_start();

// Redirect if not admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

// Initialize messages
$success = "";
$error = "";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $username = trim(mysqli_real_escape_string($conn, $_POST['username']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $desa = trim(mysqli_real_escape_string($conn, $_POST['desa']));
    $role = 'kader';

    // Validate inputs
    if ($password !== $confirmPassword) {
        $error = "Password dan konfirmasi tidak cocok!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } elseif (empty($desa)) {
        $error = "Desa harus dipilih!";
    } else {
        // Check if username exists
        $checkQuery = "SELECT id FROM users WHERE username = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Hash password (using better hashing than md5)
            $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

            // Insert new account
            $insertQuery = "INSERT INTO users (username, email, password, role, desa) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($stmt, "sssss", $username, $email, $passwordHashed, $role, $desa);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Akun kader berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan akun: " . mysqli_error($conn);
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Akun Kader - Admin Posyandu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-title {
            color: #4361ee;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .input-group-text {
            cursor: pointer;
            background-color: #e9ecef;
        }
        .btn-primary {
            background-color: #4361ee;
            border-color: #4361ee;
        }
        .btn-primary:hover {
            background-color: #3a56d4;
            border-color: #3a56d4;
        }
        .back-link {
            display: inline-block;
            margin-top: 1rem;
            color: #4361ee;
        }
        .back-link:hover {
            text-decoration: none;
            color: #3a56d4;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="form-title"><i class="fas fa-user-plus me-2"></i>Tambah Akun Kader</h2>
            
            <form method="post" id="kaderForm">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" id="username" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" id="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="password" id="password" required>
                        <span class="input-group-text" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                        <span class="input-group-text" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="desa" class="form-label">Desa</label>
                    <select class="form-select" name="desa" id="desa" required>
                        <option value="">-- Pilih Desa --</option>
                        <option value="Desa Ujung Padang">Desa Ujung Padang</option>
                        <option value="Desa Sialang">Desa Sialang</option>
                        <option value="Desa Barat Daya">Desa Barat Daya</option>
                        <option value="Desa kapeh">Desa Kapeh</option>
                        <option value="Desa Kedai Kandang">Desa Kedai Kandang</option>
                        <option value="Desa Kedai Runding">Desa Kedai Runding</option>
                        <option value="Desa Suaq Bakung">Desa Suaq Bakung</option>
                        <option value="Desa Rantau Binuang">Desa Rantau Binuang</option>
                        <option value="Desa Pulo Ie">Desa Pulai Ie</option>
                        <option value="Desa Luar">Desa Luar</option>
                        <option value="Desa Ujung ">Desa Ujung</option>
                          <option value="Desa Jua ">Desa Jua</option>
                            <option value="Desa Pasi Meurapat ">Desa Pasi Meurapat</option>
                              <option value="Desa Ujung Pasir ">Desa Ujung pasir</option>
                                <option value="Desa Geulumbuk ">Desa Geulumbuk</option>
                                  <option value="Desa Pasi Lembang">Desa Pasie Lembang</option>
                                    <option value="Desa Indra Damal">Desa Indra Damal</option>
                    </select>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Tambah Akun
                    </button>
                </div>
            </form>
            
            <a href="dashboard_admin.php" class="back-link">
                <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        document.getElementById('kaderForm').addEventListener('submit', function(e) {
            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            if (pass !== confirm) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: 'Password dan konfirmasi harus sama!'
                });
            }
        });

        <?php if (!empty($success)): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= addslashes($success); ?>',
            didClose: () => {
                window.location.href = 'dashboard_admin.php';
            }
        });
        <?php elseif (!empty($error)): ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '<?= addslashes($error); ?>'
        });
        <?php endif; ?>
    </script>
</body>
</html>