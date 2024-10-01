<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

$host = 'localhost';
$db   = 'slim_api_db';
$user = 'root'; // varsayılan XAMPP kullanıcısı
$pass = '';     // varsayılan şifre
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$client = new Client();

$usersResponse = $client->get('https://jsonplaceholder.typicode.com/users');
$users = json_decode($usersResponse->getBody(), true);

// ... (önceki kodlar)

// Kullanıcı verilerini eklerken mevcut olup olmadığını kontrol et
foreach ($users as $user) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    if ($stmt->fetchColumn() == 0) { // Eğer kullanıcı yoksa ekle
        $stmt = $pdo->prepare("INSERT INTO users (id, name, username, email, address, phone, website, company) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user['id'],
            $user['name'],
            $user['username'],
            $user['email'],
            json_encode($user['address']),
            $user['phone'],
            $user['website'],
            $user['company']['name'] ?? null // Eğer company boşsa NULL kullan
        ]);
    }
}


$postsResponse = $client->get('https://jsonplaceholder.typicode.com/posts');
$posts = json_decode($postsResponse->getBody(), true);

foreach ($posts as $post) {
    $stmt = $pdo->prepare("INSERT INTO posts (id, userId, title, body) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $post['id'],
        $post['userId'],
        $post['title'],
        $post['body']
    ]);
}

echo "Database populated successfully!";
?>
