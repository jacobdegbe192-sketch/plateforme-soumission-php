<?php
session_start();
require_once 'lib/db.php';

// Sécurité : Si l'utilisateur n'est pas un étudiant connecté, on le jette !
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    header("Location: login.php");
    exit;
}

$etudiant_id = $_SESSION['user_id'];
$fichiers = [];

try {
    // Récupération des fichiers appartenant uniquement à cet étudiant
    $stmt = $pdo->prepare("SELECT nom_fichier, uploaded_at FROM fichiers WHERE etudiant_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$etudiant_id]);
    $fichiers = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Erreur lors de la récupération de vos fichiers.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace Étudiant</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; }
        .navbar { background-color: #2e7d32; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { margin: 0; font-size: 20px; }
        .navbar a { color: white; text-decoration: none; font-weight: bold; background: #1b5e20; padding: 8px 15px; border-radius: 4px; }
        .container { max-width: 800px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        h3 { color: #2e7d32; border-bottom: 2px solid #2e7d32; padding-bottom: 8px; }
        .upload-zone { background: #e8f5e9; padding: 20px; border: 2px dashed #81c784; border-radius: 8px; text-align: center; margin-bottom: 30px; }
        .upload-zone input[type="file"] { margin: 15px 0; }
        .btn-submit { background-color: #2e7d32; color: white; border: none; padding: 10px 20px; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .btn-submit:hover { background-color: #1b5e20; }
        .alert { padding: 12px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f1f8e9; color: #2e7d32; }
    </style>
</head>
<body>

<div class="navbar">
    <h1>Espace Étudiant : <?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></h1>
    <a href="logout.php">Déconnexion</a> </div>

<div class="container">
    
    <?php if (isset($_SESSION['upload_success'])): ?>
        <div class="alert success"><?php echo $_SESSION['upload_success']; unset($_SESSION['upload_success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['upload_error'])): ?>
        <div class="alert error"><?php echo $_SESSION['upload_error']; unset($_SESSION['upload_error']); ?></div>
    <?php endif; ?>

    <div class="upload-zone">
        <h3>Téléverser un nouveau document</h3>
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <p>Formats acceptés : PDF, PNG, JPG (Max : 2 Mo)</p>
            <input type="file" name="etudiant_file" required><br>
            <button type="submit" class="btn-submit">Envoyer le fichier</button>
        </form>
    </div>

    <h3>Mes documents soumis</h3>
    <?php if (count($fichiers) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Nom du fichier</th>
                    <th>Date de soumission</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fichiers as $fichier): ?>
                    <tr>
                        <td><a href="uploads/<?php echo $fichier['nom_fichier']; ?>" target="_blank"><?php echo htmlspecialchars(substr($fichier['nom_fichier'], 11)); ?></a></td>
                        <td><?php echo date('d/m/Y à H:i', strtotime($fichier['uploaded_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Vous n'avez soumis aucun document pour le moment.</p>
    <?php endif; ?>

</div>

</body>
</html>