<?php
session_start();
include 'db.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil ID user yang akan diedit dari URL
if (!isset($_GET['id'])) {
    header("Location: dashboard_admin.php");
    exit;
}

$id = intval($_GET['id']);

// Ambil data user dari database
$result = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
if (mysqli_num_rows($result) == 0) {
    $_SESSION['edit_gagal'] = "Akun tidak ditemukan.";
    header("Location: dashboard_admin.php");
    exit;
}

$user = mysqli_fetch_assoc($result);
$error = "";

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim(mysqli_real_escape_string($conn, $_POST['username']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));

    // Validasi
    if (empty($username) || empty($email)) {
        $error = "Username dan email harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        // Update data
        $sql = "UPDATE users SET username='$username', email='$email' WHERE id=$id";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['edit_sukses'] = true;
            header("Location: dashboard_admin.php");
            exit;
        } else {
            $error = "Gagal memperbarui akun: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Akun Kader | Admin Posyandu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --white: #ffffff;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .edit-container {
            width: 100%;
            max-width: 500px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        .edit-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            padding: 25px;
            text-align: center;
        }

        .edit-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .edit-header p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .edit-form {
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
            transition: var(--transition);
            background-color: var(--light);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .btn {
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
            width: 100%;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-back {
            margin-top: 15px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: var(--transition);
        }

        .btn-back:hover {
            color: var(--secondary);
            text-decoration: none;
        }

        .error-message {
            background-color: #fee;
            color: var(--danger);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid var(--danger);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message i {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>

<div class="edit-container">
    <div class="edit-header">
        <h2><i class="fas fa-user-edit"></i> Edit Akun Kader</h2>
        <p>Perbarui informasi akun kader posyandu</p>
    </div>

    <div class="edit-form">
        <?php if ($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-field">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?= htmlspecialchars($user['username']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-field">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($user['email']); ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>

            <a href="dashboard_admin.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </form>
    </div>
</div>

</body>
</html>