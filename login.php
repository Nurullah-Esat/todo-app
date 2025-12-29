<?php
session_start();
require_once 'db.php';
header("Content-Type:application/json");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kullanıcı adı ve şifre girişlerini alıyoruz.
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    try {
        // Girdi doğrulama
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Kullanıcı adı ve şifre gerekli.']);
            exit;
        }

        // Şifreyi güvenlik için hashliyoruz
        $hashedPassword = hash('sha256', $password);

        // Kullanıcı adı ve şifreyi veritabanında kontrol ediyoruz
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND password =:hashedPassword");
        $stmt->execute(['username' => $username,'hashedPassword' => $hashedPassword]); 
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount()) {
            // Kullanıcı bulundu, oturumu başlatıyoruz
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            // Başarı mesajı döndürülüyor
            echo json_encode(['success' => true, 'message' => 'Giriş başarılı!']);
            return;
        } else {
            // Kullanıcı adı veya şifre hatalı mesajı döndürülüyor
            echo json_encode(['success' => false, 'message' => 'Kullanıcı adı veya şifre hatalı.']);
            exit;
        }
    } catch (PDOException $e) {
        // Hata durumunda uygun mesaj döndürülüyor
        echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        exit;
    }
}
?>