<?php
// Fichier de connexion à la base de données (avec PDO + sécurité)
$host = 'localhost';
$dbname = 'Soumission'; // Le nom de la base de données exigé par le sujet
$username = 'root';        // Identifiant par défaut (XAMPP / Wamp)
$password = '2003';            // Mot de passe par défaut (vide sur XAMPP, 'root' sur MAMP)

try {
    // Connexion sécurisée avec PDO et configuration des options de sécurité
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Génère des exceptions en cas d'erreur SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retourne les résultats sous forme de tableau associatif
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Utilise de vraies requêtes préparées pour bloquer les injections SQL
    ]);
} catch (PDOException $e) {
    // En cas d'échec, on arrête le script avec un message générique (sécurité)
    die("Erreur critique : Impossible de se connecter à la base de données.");
}
?>