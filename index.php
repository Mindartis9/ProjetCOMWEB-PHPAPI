<?php
header("Content-Type: text/plain");

// Test simple pour confirmer que PHP fonctionne
echo "PHP fonctionne correctement sur le serveur zzz !\n";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mmarchais002;charset=utf8mb4", "mmarchais002", "IWILLSEEKMYTRUTH12*");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion MySQL réussie 🎉";
} catch (PDOException $e) {
    echo "Erreur MySQL : " . $e->getMessage();
}
?>
