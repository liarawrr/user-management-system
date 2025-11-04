<?php
require_once "config/database.php";
require_once "models/User.php";

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$message = "";

if(isset($_GET['token'])){
    $token = $_GET['token'];
    
    if($user->activate($token)){
        $message = "Akun berhasil diaktifkan! Silakan <a href='login.php'>login</a>.";
    } else {
        $message = "Token aktivasi tidak valid atau sudah kadaluarsa.";
    }
} else {
    $message = "Token aktivasi tidak ditemukan.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Akun</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
        .success { color: #155724; background: #d4edda; padding: 15px; border-radius: 4px; border: 1px solid #c3e6cb; }
        .error { color: #721c24; background: #f8d7da; padding: 15px; border-radius: 4px; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <?php if($message): ?>
            <div class="<?php echo strpos($message, 'berhasil') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>