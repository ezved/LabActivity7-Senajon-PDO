<?php
declare(strict_types=1);

// Update these values to match your local MySQL setup.
$host = '127.0.0.1';
$database = 'blog_site';
$username = 'root';
$password = '';

// Create one PDO connection for the page that includes this file.
try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    // Show a simple message instead of exposing database details to visitors.
    http_response_code(500);
    exit('Database connection failed. Check db.php settings and import database.sql.');
}
