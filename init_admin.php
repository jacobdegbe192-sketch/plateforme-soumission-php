<?php
require_once 'lib/db.php';

try {
    // 1. On vide proprement la table des administrateurs pour repartir à zéro
    $pdo->exec("TRUNCATE TABLE administrateurs");

    // 2. C'est PHP qui génère proprement le hachage BCRYPT sur ta machine
    $email = 'admin@uca.ac.ma';
    $password_clair = 'admin123';
    $hashed_password = password_hash($password_clair, PASSWORD_BCRYPT);

    // 3. Insertion du compte admin tout neuf
    $stmt = $pdo->prepare("INSERT INTO administrateurs (email, password) VALUES (?, ?)");
    $stmt->execute([$email, $hashed_password]);

    echo "<h2 style='color: green;'>Succès : Le compte administrateur a été recréé proprement !</h2>";
    echo "<p>Email : <strong>$email</strong></p>";
    echo "<p>Mot de passe : <strong>$password_clair</strong></p>";
    echo "<a href='login.php'>Aller à la page de connexion</a>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Erreur lors de l'initialisation :</h2> " . $e->getMessage();
}
?>