<?php
// 1. On inclut la connexion à la base de données
require_once 'lib/db.php';

$message = "";
$messageClass = "";

// 2. Traitement du formulaire lors de la soumission (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sécurité : Nettoyage et validation des entrées pour éviter les failles XSS
    $nom = strip_tags(trim($_POST['nom']));
    $prenom = strip_tags(trim($_POST['prenom']));
    $apogee = strip_tags(trim($_POST['apogee']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    // Vérification que tous les champs requis sont bien remplis et valides
    if (empty($nom) || empty($prenom) || empty($apogee) || !$email) {
        $message = "Veuillez remplir tous les champs correctement.";
        $messageClass = "error";
    } else {
        try {
            // Question 2 : Génération d'un code unique à 8 chiffres (sécurisé)
            $code_activation = random_int(10000000, 99999999);

            // Sécurité : Requête préparée pour bloquer les injections SQL
            $stmt = $pdo->prepare("INSERT INTO etudiants (nom, prenom, apogee, email, code_activation, est_active) VALUES (?, ?, ?, ?, ?, 0)");
            $stmt->execute([$nom, $prenom, $apogee, $email, $code_activation]);

            // =========================================================================
            // TODO (Question 2) : Intégration de PHPMailer pour envoyer l'email réel ici.
            // Pour l'instant, on simule l'envoi en affichant le code pour pouvoir tester !
            // =========================================================================
            
            $message = "Inscription réussie ! (Code simulé pour test : <strong>$code_activation</strong>). Un email vous a été envoyé.";
            $messageClass = "success";
            
        } catch (PDOException $e) {
            // Gestion de l'erreur si l'email ou l'Apogée existe déjà (Contrainte UNIQUE)
            if ($e->getCode() == 23000) {
                $message = "Erreur : Ce numéro d'Apogée ou cet email est déjà utilisé.";
            } else {
                $message = "Une erreur est survenue lors de l'enregistrement.";
            }
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
    <title>Inscription Étudiant</title>
    <style>
        /* Un design épuré avec une touche de vert, parfait pour l'application */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .register-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #2e7d32; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 14px; }
        .form-group input:focus { border-color: #2e7d32; outline: none; }
        button { width: 100%; padding: 12px; background-color: #2e7d32; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        button:hover { background-color: #1b5e20; }
        .alert { padding: 12px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="register-container">
    <h2>Inscription Étudiant</h2>

    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $messageClass; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" required placeholder="Ex: Benali">
        </div>
        <div class="form-group">
            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" name="prenom" required placeholder="Ex: Ahmed">
        </div>
        <div class="form-group">
            <label for="apogee">Numéro Apogée</label>
            <input type="text" id="apogee" name="apogee" required placeholder="Ex: 20251122">
        </div>
        <div class="form-group">
            <label for="email">Adresse Email</label>
            <input type="email" id="email" name="email" required placeholder="Ex: ahmed@example.com">
        </div>
        <button type="submit">S'inscrire</button>
    </form>
</div>

</body>
</html>