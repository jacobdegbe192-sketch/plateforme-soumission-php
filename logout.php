<?php
session_start(); // On récupère la session en cours
$_SESSION = array(); // On vide toutes les variables de session

// Si on veut détruire complètement le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy(); // On détruit la session sur le serveur

// Redirection immédiate vers la page de connexion
header("Location: login.php");
exit;