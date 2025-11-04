<?php
session_start();
require_once "config/database.php";
require_once "models/User.php";
require_once "utils/EmailSender.php";

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$message = "";

if($_POST){
    if(isset($_POST['login'])){
        // Login process
        $user->email = $_POST['email'];
        $password = $_POST['password'];

        if($user->emailExists()){
            if($user->status == 'active'){
                if(password_verify($password, $user->password)){
                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['user_email'] = $user->email;
                    $_SESSION['user_name'] = $user->full_name;
                    $_SESSION['user_role'] = $user->role;
                    
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $message = "Password salah!";
                }
            } else {
                $message = "Akun belum diaktifkan. Silakan cek email Anda untuk tautan aktivasi.";
            }
        } else {
            $message = "Email tidak terdaftar!";
        }
    } elseif(isset($_POST['forgot_password'])){
        // Forgot password process
        $user->email = $_POST['email'];
        
        if($user->emailExists() && $user->status == 'active'){
            if($user->generateResetToken()){
                if(EmailSender::sendResetPasswordEmail($user->email, $user->full_name, $user->reset_token)){
                    $message = "Tautan reset password telah dikirim ke email Anda.";
                } else {
                    $message = "Gagal mengirim email reset password. Silakan coba lagi.";
                }
            } else {
                $message = "Gagal generate token reset. Silakan coba lagi.";
            }
        } else {
            $message = "Email tidak terdaftar atau akun belum aktif!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin Gudang</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 400px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input[type="email"], input[type="password"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; border: none; border-radius: 4px; cursor: pointer; margin-bottom: 10px; }
        .btn-login { background: #007bff; color: white; }
        .btn-login:hover { background: #0056b3; }
        .btn-forgot { background: #6c757d; color: white; }
        .btn-forgot:hover { background: #545b62; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .register-link { text-align: center; margin-top: 15px; }
        .forgot-password-form { display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login Admin Gudang</h2>
        
        <?php if($message): ?>
            <div class="message <?php echo strpos($message, 'dikirim') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn-login">Login</button>
        </form>

        <button type="button" class="btn-forgot" onclick="toggleForgotPassword()">Lupa Password?</button>

        <div id="forgotPasswordForm" class="forgot-password-form">
            <form method="POST">
                <div class="form-group">
                    <label>Masukkan email untuk reset password:</label>
                    <input type="email" name="email" required>
                </div>
                <button type="submit" name="forgot_password" class="btn-forgot">Kirim Tautan Reset</button>
            </form>
        </div>
        
        <div class="register-link">
            <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </div>
    </div>

    <script>
        function toggleForgotPassword() {
            var form = document.getElementById('forgotPasswordForm');
            form.style.display = form.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</body>
</html>