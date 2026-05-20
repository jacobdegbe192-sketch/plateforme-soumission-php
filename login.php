<?php
// On démarre la session en tout premier pour pouvoir stocker les informations de l'utilisateur connecté
session_start();
require_once 'lib/db.php';

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);

    if (!$email || empty($password)) {
        $message = "Veuillez remplir tous les champs correctement.";
        $messageClass = "error";
    } else {
        try {
            // 1. ÉTAPE A : On cherche d'abord si l'email correspond à un ADMINISTRATEUR
            $stmtAdmin = $pdo->prepare("SELECT id, email, password FROM administrateurs WHERE email = ?");
            $stmtAdmin->execute([$email]);
            $admin = $stmtAdmin->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Authentification Admin réussie : on initialise sa session
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['user_email'] = $admin['email'];
                $_SESSION['role'] = 'admin';

                // Redirection vers le tableau de bord admin
                header("Location: admin_dashboard.php");
                exit;
            }

            // 2. ÉTAPE B : Si ce n'est pas un admin, on cherche si c'est un ÉTUDIANT
            $stmtEtudiant = $pdo->prepare("SELECT id, nom, prenom, password, est_active FROM etudiants WHERE email = ?");
            $stmtEtudiant->execute([$email]);
            $etudiant = $stmtEtudiant->fetch();

            if ($etudiant) {
                // On vérifie si le compte est activé avant de tester le mot de passe
                if ($etudiant['est_active'] == 0) {
                    $message = "Votre compte n'est pas encore activé. Veuillez utiliser votre code d'activation.";
                    $messageClass = "error";
                } 
                // Vérification du mot de passe haché
                elseif ($etudiant['password'] && password_verify($password, $etudiant['password'])) {
                    // Authentification Étudiant réussie : on initialise sa session
                    $_SESSION['user_id'] = $etudiant['id'];
                    $_SESSION['user_nom'] = $etudiant['nom'];
                    $_SESSION['user_prenom'] = $etudiant['prenom'];
                    $_SESSION['role'] = 'etudiant';

                    // Redirection vers le tableau de bord étudiant
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $message = "Identifiants invalides ou mot de passe non configuré.";
                    $messageClass = "error";
                }
            } else {
                $message = "Identifiants incorrects.";
                $messageClass = "error";
            }

        } catch (PDOException $e) {
            $message = "Une erreur technique est survenue.";
            $messageClass = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Plateforme de Soumission</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #2e7d32; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 14px; }
        button { width: 100%; padding: 12px; background-color: #2e7d32; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; }
        button:hover { background-color: #1b5e20; }
        .alert { padding: 12px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; text-align: center; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .links { text-align: center; margin-top: 15px; font-size: 13px; }
        .links a { color: #2e7d32; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Connexion Espace Personnel</h2>

    <?php if (!empty($message)): ?>
        <div class="alert error">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label Pattern for="email">Adresse Email</label>
            <input type="email" id="email" name="email" required placeholder="étudiant@example.com ou admin@uca.ac.ma">
        </div>
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required placeholder="Votre mot de passe">
        </div>
        <button type="submit">Se connecter</button>
    </form>

    <div class="links">
        <a href="register.php">Pas encore inscrit ? Créer un compte</a><br><br>
        <a href="verify_code.php">Activer un compte existant</a>
    </div>
</div>

</body>
</html>