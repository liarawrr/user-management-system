<?php
require_once "config/database.php";
require_once "models/User.php";

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$message = "";

if(isset($_GET['token'])){
    $token = $_GET['token'];
    
    if($_POST){
        $new_password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if($new_password === $confirm_password){
            if(strlen($new_password) >= 6){
                if($user->resetPassword($token, $new_password)){
                    $message = "Password berhasil direset! Silakan <a href='login.php'>login</a> dengan password baru.";
                } else {
                    $message = "Token reset tidak valid atau sudah kadaluarsa.";
                }
            } else {
                $message = "Password minimal 6 karakter.";
            }
        } else {
            $message = "Password dan konfirmasi password tidak cocok.";
        }
    }
} else {
    $message = "Token reset tidak ditemukan.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 400px; margin: 50px auto; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input[type="password"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        
        <?php if($message): ?>
            <div class="message <?php echo strpos($message, 'berhasil') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['token']) && !strpos($message, 'berhasil')): ?>
            <form method="POST">
                <div class="form-group">
                    <label>Password Baru:</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password Baru:</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>