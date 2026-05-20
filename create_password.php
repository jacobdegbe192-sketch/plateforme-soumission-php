<?php
require_once 'lib/db.php';

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);
    $password_confirm = trim($_POST['password_confirm']);

    if (!$email || empty($password) || empty($password_confirm)) {
        $message = "Veuillez remplir tous les champs.";
        $messageClass = "error";
    } elseif ($password !== $password_confirm) {
        $message = "Les mots de passe ne correspondent pas.";
        $messageClass = "error";
    } elseif (strlen($password) < 6) {
        $message = "Le mot de passe doit contenir au moins 6 caractères.";
        $messageClass = "error";
    } else {
        try {
            // On vérifie d'abord si l'étudiant existe en base de données
            $stmt = $pdo->prepare("SELECT id, est_active FROM etudiants WHERE email = ?");
            $stmt->execute([$email]);
            $etudiant = $stmt->fetch();

            if (!$etudiant) {
                $message = "Aucun étudiant trouvé avec cette adresse email.";
                $messageClass = "error";
            } elseif ($etudiant['est_active'] == 0) {
                // Sécurité demandée par le sujet : le compte doit être activé via AJAX d'abord
                $message = "Veuillez d'abord activer votre compte avec le code reçu.";
                $messageClass = "error";
            } else {
                // SÉCURITÉ CRUCIALE : Hachage du mot de passe avec l'algorithme sécurisé BCRYPT
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Mise à jour du mot de passe de l'étudiant
                $update = $pdo->prepare("UPDATE etudiants SET password = ? WHERE id = ?");
                $update->execute([$hashed_password, $etudiant['id']]);

                $message = "Mot de passe créé avec succès ! Vous pouvez maintenant vous connecter.";
                $messageClass = "success";
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
    <title>Création du Mot de Passe</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .password-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #2e7d32; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 14px; }
        button { width: 100%; padding: 12px; background-color: #2e7d32; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; }
        button:hover { background-color: #1b5e20; }
        .alert { padding: 12px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .links { text-align: center; margin-top: 15px; font-size: 13px; }
        .links a { color: #2e7d32; text-decoration: none; }
    </style>
</head>
<body>

<div class="password-container">
    <h2>Créer votre mot de passe</h2>

    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $messageClass; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form action="create_password.php" method="POST">
        <div class="form-group">
            <label for="email">Adresse Email</label>
            <input type="email" id="email" name="email" required placeholder="Ex: votre_email@example.com">
        </div>
        <div class="form-group">
            <label for="password">Nouveau mot de passe</label>
            <input type="password" id="password" name="password" required placeholder="Minimum 6 caractères">
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirmer le mot de passe</label>
            <input type="password" id="password_confirm" name="password_confirm" required placeholder="Répétez le mot de passe">
        </div>
        <button type="submit">Enregistrer le mot de passe</button>
    </form>
    <div class="links">
        <a href="login.php">Retourner à la page de connexion</a>
    </div>
</div>

</body>
</html>