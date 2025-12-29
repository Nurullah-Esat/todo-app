<?php
require_once 'db.php';

header('Content-Type: application/json'); // Yanıt JSON formatında

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Gerekli alanların kontrolü
    if (empty($username) || empty($password) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Tüm alanları doldurun.']);
        exit;
    }

    $hashedPassword = hash('sha256', $password); // Şifreyi şifrele

    try {
        // Veritabanına kullanıcıyı ekleme işlemi
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (:username, :password, :email)");
        $stmt->execute([
            'username' => $username,
            'password' => $hashedPassword,
            'email' => $email
        ]);

        // Başarı mesajı döndürülüyor
        echo json_encode(['success' => true, 'message' => 'Kayıt başarılı!']);
        exit;
    } catch (PDOException $e) {
        // Veritabanı hatası ya da kullanıcı adı/e-posta çakışması kontrolü
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'Bu kullanıcı adı veya e-posta zaten kayıtlı.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>
