<?php
session_start();
require_once "config/database.php";
require_once "models/User.php";
require_once "models/Product.php";

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin_gudang'){
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$product = new Product($db);

$message = "";

// Handle product operations
if($_POST){
    if(isset($_POST['add_product'])){
        $product->name = $_POST['name'];
        $product->description = $_POST['description'];
        $product->price = $_POST['price'];
        $product->stock = $_POST['stock'];
        $product->category = $_POST['category'];
        $product->image_url = $_POST['image_url'];
        $product->created_by = $_SESSION['user_id'];
        
        if($product->create()){
            $message = "Produk berhasil ditambahkan!";
        } else {
            $message = "Gagal menambahkan produk.";
        }
    } elseif(isset($_POST['update_product'])){
        $product->id = $_POST['product_id'];
        $product->name = $_POST['name'];
        $product->description = $_POST['description'];
        $product->price = $_POST['price'];
        $product->stock = $_POST['stock'];
        $product->category = $_POST['category'];
        $product->image_url = $_POST['image_url'];
        
        if($product->update()){
            $message = "Produk berhasil diperbarui!";
        } else {
            $message = "Gagal memperbarui produk.";
        }
    } elseif(isset($_POST['delete_product'])){
        $product->id = $_POST['product_id'];
        
        if($product->delete()){
            $message = "Produk berhasil dihapus!";
        } else {
            $message = "Gagal menghapus produk.";
        }
    } elseif(isset($_POST['update_profile'])){
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        
        if($user->updateProfile($_SESSION['user_id'], $full_name, $email)){
            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_email'] = $email;
            $message = "Profil berhasil diperbarui!";
        } else {
            $message = "Gagal memperbarui profil.";
        }
    } elseif(isset($_POST['change_password'])){
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $user->email = $_SESSION['user_email'];
        if($user->emailExists()){
            if(password_verify($current_password, $user->password)){
                if($new_password === $confirm_password){
                    if($user->updatePassword($_SESSION['user_id'], $new_password)){
                        $message = "Password berhasil diubah!";
                    } else {
                        $message = "Gagal mengubah password.";
                    }
                } else {
                    $message = "Password baru dan konfirmasi tidak cocok.";
                }
            } else {
                $message = "Password saat ini salah.";
            }
        }
    }
}

// Get all products
$products = $product->readAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin Gudang</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f4; }
        .header { background: #343a40; color: white; padding: 15px 20px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .welcome { background: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .tabs { display: flex; margin-bottom: 20px; background: white; border-radius: 5px; overflow: hidden; }
        .tab { padding: 15px 20px; cursor: pointer; background: #e9ecef; border: none; flex: 1; text-align: center; }
        .tab.active { background: #007bff; color: white; }
        .tab-content { display: none; background: white; padding: 20px; border-radius: 0 0 5px 5px; }
        .tab-content.active { display: block; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"], input[type="number"], textarea, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .logout { float: right; background: #dc3545; }
        .logout:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard Admin Gudang</h1>
        <button class="logout" onclick="location.href='logout.php'">Logout</button>
    </div>

    <div class="container">
        <div class="welcome">
            <h2>Selamat datang, <?php echo $_SESSION['user_name']; ?>!</h2>
            <p>Email: <?php echo $_SESSION['user_email']; ?></p>
        </div>

        <?php if($message): ?>
            <div class="message <?php echo strpos($message, 'berhasil') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab active" onclick="openTab('products')">Kelola Produk</button>
            <button class="tab" onclick="openTab('addProduct')">Tambah Produk</button>
            <button class="tab" onclick="openTab('profile')">Profil Saya</button>
            <button class="tab" onclick="openTab('changePassword')">Ubah Password</button>
        </div>

        <!-- Products Management Tab -->
        <div id="products" class="tab-content active">
            <h3>Daftar Produk</h3>
            <?php if($products->rowCount() > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $products->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['category']; ?></td>
                                <td>Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                                <td><?php echo $row['stock']; ?></td>
                                <td><?php echo $row['created_by_name']; ?></td>
                                <td>
                                    <button onclick="editProduct(<?php echo $row['id']; ?>)">Edit</button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_product" class="btn-danger" onclick="return confirm('Yakin hapus produk?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Belum ada produk.</p>
            <?php endif; ?>
        </div>

        <!-- Add Product Tab -->
        <div id="addProduct" class="tab-content">
            <h3>Tambah Produk Baru</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Produk:</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi:</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Harga:</label>
                    <input type="number" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Stok:</label>
                    <input type="number" name="stock" required>
                </div>
                <div class="form-group">
                    <label>Kategori:</label>
                    <input type="text" name="category">
                </div>
                <div class="form-group">
                    <label>URL Gambar:</label>
                    <input type="text" name="image_url">
                </div>
                <button type="submit" name="add_product" class="btn-success">Tambah Produk</button>
            </form>
        </div>

        <!-- Edit Product Form (Hidden by default) -->
        <div id="editProduct" class="tab-content" style="display: none;">
            <h3>Edit Produk</h3>
            <form method="POST" id="editProductForm">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="form-group">
                    <label>Nama Produk:</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi:</label>
                    <textarea name="description" id="edit_description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Harga:</label>
                    <input type="number" name="price" id="edit_price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Stok:</label>
                    <input type="number" name="stock" id="edit_stock" required>
                </div>
                <div class="form-group">
                    <label>Kategori:</label>
                    <input type="text" name="category" id="edit_category">
                </div>
                <div class="form-group">
                    <label>URL Gambar:</label>
                    <input type="text" name="image_url" id="edit_image_url">
                </div>
                <button type="submit" name="update_product" class="btn-success">Update Produk</button>
                <button type="button" onclick="cancelEdit()">Batal</button>
            </form>
        </div>

        <!-- Profile Tab -->
        <div id="profile" class="tab-content">
            <h3>Profil Saya</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Lengkap:</label>
                    <input type="text" name="full_name" value="<?php echo $_SESSION['user_name']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?php echo $_SESSION['user_email']; ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn-success">Update Profil</button>
            </form>
        </div>

        <!-- Change Password Tab -->
        <div id="changePassword" class="tab-content">
            <h3>Ubah Password</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Password Saat Ini:</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>Password Baru:</label>
                    <input type="password" name="new_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password Baru:</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" name="change_password" class="btn-success">Ubah Password</button>
            </form>
        </div>
    </div>

    <script>
        function openTab(tabName) {
            // Hide all tab contents
            var tabContents = document.getElementsByClassName('tab-content');
            for (var i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }

            // Remove active class from all tabs
            var tabs = document.getElementsByClassName('tab');
            for (var i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove('active');
            }

            // Show the specific tab content and add active class to the button
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        function editProduct(productId) {
            // This would typically fetch product data via AJAX
            // For demo purposes, we'll just show the edit form
            openTab('editProduct');
            
            // In a real implementation, you would populate the form with existing data
            // document.getElementById('edit_product_id').value = productId;
            // ... populate other fields ...
        }

        function cancelEdit() {
            openTab('products');
        }
    </script>
</body>
</html>