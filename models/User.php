<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $email;
    public $password;
    public $full_name;
    public $role;
    public $status;
    public $activation_token;
    public $reset_token;
    public $reset_token_expiry;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Check if email exists
    public function emailExists() {
        $query = "SELECT id, password, status, full_name, role 
                  FROM " . $this->table_name . " 
                  WHERE email = ? 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->password = $row['password'];
            $this->status = $row['status'];
            $this->full_name = $row['full_name'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }

    // Create new user
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET email=:email, password=:password, full_name=:full_name, 
                  activation_token=:activation_token, status='pending'";

        $stmt = $this->conn->prepare($query);

        // Hash password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Generate activation token
        $this->activation_token = bin2hex(random_bytes(32));

        // Bind values
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":activation_token", $this->activation_token);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Activate user account
    public function activate($token) {
        $query = "UPDATE " . $this->table_name . "
                  SET status = 'active', activation_token = NULL
                  WHERE activation_token = :token AND status = 'pending'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);

        if($stmt->execute() && $stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    // Generate reset token
    public function generateResetToken() {
        $this->reset_token = bin2hex(random_bytes(32));
        $this->reset_token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $query = "UPDATE " . $this->table_name . "
                  SET reset_token = :reset_token, 
                      reset_token_expiry = :reset_token_expiry
                  WHERE email = :email";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":reset_token", $this->reset_token);
        $stmt->bindParam(":reset_token_expiry", $this->reset_token_expiry);
        $stmt->bindParam(":email", $this->email);

        return $stmt->execute();
    }

    // Reset password
    public function resetPassword($token, $new_password) {
        $query = "UPDATE " . $this->table_name . "
                  SET password = :password, 
                      reset_token = NULL,
                      reset_token_expiry = NULL
                  WHERE reset_token = :token 
                  AND reset_token_expiry > NOW()";

        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":token", $token);

        if($stmt->execute() && $stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    // Update password
    public function updatePassword($user_id, $new_password) {
        $query = "UPDATE " . $this->table_name . "
                  SET password = :password
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":id", $user_id);

        return $stmt->execute();
    }

    // Update profile
    public function updateProfile($user_id, $full_name, $email) {
        $query = "UPDATE " . $this->table_name . "
                  SET full_name = :full_name, email = :email
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":full_name", $full_name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":id", $user_id);

        return $stmt->execute();
    }
}
?>