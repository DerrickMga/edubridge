<?php
$host = '127.0.0.1';
$user = 'root';
$pass = 'KMG@2024VItalLinks';
$port = 3306;

$conn = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$conn->exec("CREATE DATABASE IF NOT EXISTS `edubridge` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "edubridge database created/verified\n";
