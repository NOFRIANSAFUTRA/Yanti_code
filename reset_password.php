<?php
session_start();
include 'db.php';

$error = "";
$success = "";

if (isset($_GET['token'])) {
    $reset_token = mysqli_real_escape_string($conn, $_GET['token']);

    // Verifikasi token
    $sql = "SELECT * FROM users WHERE reset_token='$reset_token'";
    $query = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($query);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $new_password = md5($_POST['new_password']);
        $confirm_password = md5($_POST['confirm_password']);

        if ($new_password == $confirm_password) {
            // Update password di database
            $update_sql = "UPDATE users SET password='$new_password', reset_token=NULL WHERE reset_token='$reset_token'";
            mysqli_query($conn, $update_sql);
            $success = "Password berhasil diperbarui. Silakan login dengan password baru.";
        } else {
            $error = "Password dan konfirmasi password tidak cocok!";
        }
    }
} else {
    $error = "Token reset password tidak valid!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
</head>
<body>
  <h2>Reset Password</h2>
  
  <?php if ($error != "") echo "<p style='color:red;'>$error</p>"; ?>
  <?php if ($success != "") echo "<p style='color:green;'>$success</p>"; ?>

  <form method="post">
    <label>Password Baru:</label><br>
    <input type="password" name="new_password" required><br><br>
    <label>Konfirmasi Password:</label><br>
    <input type="password" name="confirm_password" required><br><br>
    <button type="submit">Reset Password</button>
  </form>
</body>
</html>
