<?php
require_once 'lib/db.php';

// Si la requête vient d'AJAX (méthode POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'verify') {
    // Entêtes pour répondre en JSON (très propre pour AJAX)
    header('Content-Type: application/json');

    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $code = strip_tags(trim($_POST['code']));

    if (!$email || empty($code)) {
        echo json_encode(['status' => 'error', 'message' => 'Veuillez remplir tous les champs correctement.']);
        exit;
    }

    try {
        // On cherche l'étudiant avec cet email et ce code d'activation
        $stmt = $pdo->prepare("SELECT id, est_active FROM etudiants WHERE email = ? AND code_activation = ?");
        $stmt->execute([$email, $code]);
        $etudiant = $stmt->fetch();

        if ($etudiant) {
            if ($etudiant['est_active'] == 1) {
                echo json_encode(['status' => 'error', 'message' => 'Ce compte est déjà activé.']);
            } else {
                // Mise à jour du champ est_active dans la base de données
                $update = $pdo->prepare("UPDATE etudiants SET est_active = 1 WHERE id = ?");
                $update->execute([$etudiant['id']]);

                echo json_encode(['status' => 'success', 'message' => 'Compte activé avec succès !']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Email ou code d\'activation incorrect.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Une erreur technique est survenue.']);
    }
    exit; // On arrête le script ici pour ne pas charger le HTML lors d'un appel AJAX
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activation du Compte</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .activation-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #2e7d32; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 14px; }
        button { width: 100%; padding: 12px; background-color: #2e7d32; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; }
        button:hover { background-color: #1b5e20; }
        .alert { padding: 12px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; text-align: center; display: none; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="activation-container">
    <h2>Activation du compte</h2>

    <div id="customAlert" class="alert"></div>

    <form id="activationForm">
        <div class="form-group">
            <label for="email">Adresse Email</label>
            <input type="email" id="email" required placeholder="Ex: ahmed@example.com">
        </div>
        <div class="form-group">
            <label for="code">Code d'activation (8 chiffres)</label>
            <input type="text" id="code" required maxlength="8" placeholder="Ex: 12345678">
        </div>
        <button type="submit">Activer mon compte</button>
    </form>
</div>

<script src="js/main.js"></script>
</body>
</html>