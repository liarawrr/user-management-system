<?php
class EmailSender {
    public static function sendActivationEmail($email, $full_name, $activation_token) {
        $subject = "Aktivasi Akun Admin Gudang";
        $activation_link = "http://localhost/user_management/activate.php?token=" . $activation_token;
        
        $message = "
        <html>
        <head>
            <title>Aktivasi Akun</title>
        </head>
        <body>
            <h2>Halo " . $full_name . ",</h2>
            <p>Terima kasih telah mendaftar sebagai Admin Gudang.</p>
            <p>Silakan klik tautan berikut untuk mengaktifkan akun Anda:</p>
            <p><a href='" . $activation_link . "'>" . $activation_link . "</a></p>
            <p>Tautan ini akan kadaluarsa dalam 24 jam.</p>
            <br>
            <p>Salam,<br>Tim User Management</p>
        </body>
        </html>
        ";

        return self::sendEmail($email, $subject, $message);
    }

    public static function sendResetPasswordEmail($email, $full_name, $reset_token) {
        $subject = "Reset Password Admin Gudang";
        $reset_link = "http://localhost/user_management/reset_password.php?token=" . $reset_token;
        
        $message = "
        <html>
        <head>
            <title>Reset Password</title>
        </head>
        <body>
            <h2>Halo " . $full_name . ",</h2>
            <p>Kami menerima permintaan reset password untuk akun Anda.</p>
            <p>Silakan klik tautan berikut untuk membuat password baru:</p>
            <p><a href='" . $reset_link . "'>" . $reset_link . "</a></p>
            <p>Tautan ini akan kadaluarsa dalam 1 jam.</p>
            <br>
            <p>Salam,<br>Tim User Management</p>
        </body>
        </html>
        ";

        return self::sendEmail($email, $subject, $message);
    }

    private static function sendEmail($to, $subject, $message) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: no-reply@usermanagement.com" . "\r\n";

        // Dalam implementasi nyata, gunakan library email seperti PHPMailer
        // return mail($to, $subject, $message, $headers);
        
        // Untuk demo, kita simpan email ke file
        $email_content = "To: $to\nSubject: $subject\n\n$message\n\n";
        file_put_contents('emails.log', $email_content, FILE_APPEND);
        
        return true;
    }
}
?>
