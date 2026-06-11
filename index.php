<?php
// Veritabanı Bağlantısı
$host = 'localhost';
$db   = 'minecraft_authme_database'; // Veritabanı adın
$user = 'veritabani_kullanicisi';     // Veritabanı kullanıcı adın
$pass = 'veritabani_sifresi';         // Veritabanı şifren
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
     $pdo = new PDO($dsn, $user, $pass);
} catch (\PDOException $e) {
     $mesaj = "<div class='hata'>Veritabanı bağlantı hatası!</div>";
}

// Kayıt Ol Butonuna Basıldığında
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kayit_ol'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $mesaj = "<div class='hata'>Lütfen tüm alanları doldurun!</div>";
    } else {
        // Kullanıcı daha önce kayıt olmuş mu kontrol et
        $stmt = $pdo->prepare('SELECT id FROM authme WHERE LOWER(username) = LOWER(?)');
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $mesaj = "<div class='hata'>Bu kullanıcı adı zaten kayıtlı!</div>";
        } else {
            // AuthMe SHA256 Şifreleme Formatı: $SHA$salt$hash
            // Rastgele 16 karakterlik bir tuz (salt) üretiyoruz
            $salt = substr(md5(uniqid(rand(), true)), 0, 16);
            
            // Algoritma: SHA256(SHA256(şifre) + salt)
            $hashed_password = hash('sha256', hash('sha256', $password) . $salt);
            
            // AuthMe formatında birleştiriyoruz
            $final_password = "\$SHA\$" . $salt . "\$" . $hashed_password;

            // Veritabanına kaydet (AuthMe'nin ihtiyaç duyduğu temel alanlar)
            $insert = $pdo->prepare('INSERT INTO authme (username, realname, password, regip, regdate) VALUES (?, ?, ?, ?, ?)');
            $ip = $_SERVER['REMOTE_ADDR'];
            $time = time() * 1000; // Milisaniye cinsinden zaman
            
            if ($insert->execute([strtolower($username), $username, $final_password, $ip, $time])) {
                $mesaj = "<div class='basari'>Başarıyla kayıt oldunuz! LestaLauncher üzerinden giriş yapabilirsiniz.</div>";
            } else {
                $mesaj = "<div class='hata'>Kayıt sırasında bir hata oluştu!</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>LestaNetwork - Kayıt Ol</title>
    <style>
        body { background-color: #121212; color: #fff; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .kayit-kutu { background-color: #1e1e1e; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); width: 320px; text-align: center; }
        h2 { color: #bb86fc; margin-bottom: 20px; }
        input[type="text"], input[type="password"] { width: 90%; padding: 10px; margin: 10px 0; border: 1px solid #333; background-color: #2d2d2d; color: #fff; border-radius: 5px; }
        input[type="submit"] { width: 97%; padding: 10px; background-color: #bb86fc; border: none; color: #121212; font-weight: bold; border-radius: 5px; cursor: pointer; transition: 0.3s; }
        input[type="submit"]:hover { background-color: #9965db; }
        .hata { color: #cf6679; margin-bottom: 10px; }
        .basari { color: #03dac6; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="kayit-kutu">
    <h2>LestaNetwork</h2>
    <?php if(isset($mesaj)) echo $mesaj; ?>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Minecraft Kullanıcı Adı" required><br>
        <input type="password" name="password" placeholder="Şifre" required><br><br>
        <input type="submit" name="kayit_ol" value="Kayıt Ol">
    </form>
</div>

</body>
</html>
