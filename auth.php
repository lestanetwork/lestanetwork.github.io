<?php
// Hata göstermeyi kapat (Güvenlik için)
error_reporting(0);

// Veritabanı Bilgilerin
$host = 'localhost';
$db   = 'minecraft_authme_database';
$user = 'veritabani_kullanicisi';
$pass = 'veritabani_sifresi';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("CONN_ERROR");
}

// Launcher'dan gelen veriler
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if(empty($username) || empty($password)) {
    die("MISSING_FIELDS");
}

// AuthMe tablosundan kullanıcıyı çek
$stmt = $pdo->prepare('SELECT password FROM authme WHERE LOWER(username) = LOWER(?)');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    die("USER_NOT_FOUND");
}

// AuthMe SHA256 Şifre Doğrulama Algoritması
$db_password = $user['password'];
$parts = explode('$', $db_password);

if(count($parts) < 4) {
    die("INVALID_HASH_FORMAT");
}

$salt = $parts[2];
$real_hash = $parts[3];

// C#'taki gibi: SHA256(SHA256(girilen_sifre) + salt)
$hashed_input = hash('sha256', hash('sha256', $password) . $salt);

if (strcasecmp($hashed_input, $real_hash) === 0) {
    echo "SUCCESS";
} else {
    echo "WRONG_PASSWORD";
}
?>
