<?php
$host = '127.0.0.1';
$db = 'pn_system';
$user = 'root';
$password = 'hazel';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("DROP TABLE IF EXISTS locations");
    echo "Dropped locations table\n";
    
    $pdo->exec("DROP TABLE IF EXISTS students");
    echo "Dropped students table\n";
    
    echo "Tables dropped successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>