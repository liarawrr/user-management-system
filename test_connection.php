<?php
require_once "config/database.php";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if($db) {
        echo "✅ Koneksi database BERHASIL!";
        
        // Test query
        $stmt = $db->query("SELECT COUNT(*) as total_users FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<br>Total users: " . $result['total_users'];
        
    } else {
        echo "❌ Koneksi database GAGAL!";
    }
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>