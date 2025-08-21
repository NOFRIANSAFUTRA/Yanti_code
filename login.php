<?php
session_start();
include 'db.php'; // Koneksi ke database

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $query = $stmt->get_result();

    if ($query && $query->num_rows === 1) {
        $user = $query->fetch_assoc();
        $stored_password = $user['password'];

        $password_match = false;

        // Cek apakah hash-nya bcrypt
        if (password_verify($password, $stored_password)) {
            $password_match = true;
        }

        // Cek apakah hash-nya MD5 (untuk admin lama)
        if (md5($password) === $stored_password) {
            $password_match = true;
        }

        if ($password_match) {
            // Set session variables
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id']; // Tambahan untuk tracking user
            $_SESSION['desa'] = $user['desa'];

            // Redirect berdasarkan role
            if ($user['role'] == 'admin') {
                header("Location: dashboard_admin.php");
            } elseif ($user['role'] == 'petugas') {
                header("Location: dashboard_petugas.php"); // Dashboard khusus petugas
            } elseif ($user['role'] == 'kader') {
                header("Location: dashboard_kader.php");
            } else {
                // Jika role tidak dikenali
                $error = "Role tidak valid!";
                session_destroy();
            }
            exit();
        } else {
            $error = "Username atau Password salah!";
        }
    } else {
        $error = "Username atau Password salah!";
    }
}

// Debug mode - untuk mengecek data di database (hapus setelah testing)
if (isset($_GET['debug']) && $_GET['debug'] == 'petugas') {
    echo "<h3>Debug Mode - Data Petugas:</h3>";
    $debug_query = mysqli_query($conn, "SELECT id, username, email, role, desa FROM users WHERE role = 'petugas'");
    if ($debug_query && mysqli_num_rows($debug_query) > 0) {
        echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Desa</th></tr>";
        while ($row = mysqli_fetch_assoc($debug_query)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['username'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . $row['role'] . "</td>";
            echo "<td>" . $row['desa'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Tidak ada data petugas ditemukan.";
    }
    echo "<br><a href='login.php'>Kembali ke Login</a>";
    exit;
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem Posyandu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 120px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            z-index: -1;
        }

        .login-header {
            padding: 30px;
            text-align: center;
            color: var(--white);
        }

        .login-header h2 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .login-header p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .login-form {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .input-field {
            position: relative;
        }

        .input-field i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background-color: var(--light);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        .login-footer {
            text-align: center;
            padding: 0 30px 30px;
            color: var(--gray);
            font-size: 0.85rem;
        }

        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h2>Selamat Datang</h2>
        <p>Sistem Informasi Posyandu</p>
    </div>

    <form method="post" action="" class="login-form">
        <div class="form-group">
            <label for="username">Username</label>
            <div class="input-field">
                <i class="fas fa-user"></i>
                <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required autocomplete="off">
            </div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-field">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required autocomplete="off">
                
            </div>
        </div>

        <button type="submit" name="login" class="btn btn-primary">
            <i class="fas fa-sign-in-alt"></i> Login
        </button>
    </form>

 <div class="login-footer">
    <p><a href="index.php">Kembali ke Halaman Utama</a></p>
</div>

</div>

<?php if (!empty($error)): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Login Gagal!',
        text: '<?= $error ?>',
        confirmButtonColor: '#d33',
        timer: 3000,
        backdrop: `
            rgba(0,0,0,0.4)
            url("https://sweetalert2.github.io/images/nyan-cat.gif")
            left top
            no-repeat
        `
    });
</script>
<?php endif; ?>

</body>
</html>