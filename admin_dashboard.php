<?php
session_start();
require_once 'lib/db.php';

// SÉCURITÉ : On vérifie STRICTEMENT que l'utilisateur est connecté ET qu'il est administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

try {
    // Requête SQL avancée (Jointure) pour récupérer les étudiants et le décompte de leurs fichiers soumis
    $query = "SELECT e.id, e.nom, e.prenom, e.apogee, e.email, e.est_active, 
              COUNT(f.id) AS nb_fichiers 
              FROM etudiants e 
              LEFT JOIN fichiers f ON e.id = f.etudiant_id 
              GROUP BY e.id 
              ORDER BY e.nom ASC";
    
    $stmt = $pdo->query($query);
    $etudiants = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Erreur lors du chargement des données de gestion.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Administration</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; }
        .navbar { background-color: #1b5e20; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { margin: 0; font-size: 20px; }
        .navbar a { color: white; text-decoration: none; font-weight: bold; background: #b71c1c; padding: 8px 15px; border-radius: 4px; }
        .container { max-width: 1000px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        h2 { color: #1b5e20; margin-bottom: 20px; border-bottom: 2px solid #1b5e20; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #e8f5e9; color: #1b5e20; }
        .status { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .status.active { background-color: #c8e6c9; color: #256029; }
        .status.inactive { background-color: #ffcdd2; color: #c62828; }
        .badge { background: #1b5e20; color: white; padding: 2px 8px; border-radius: 10px; font-size: 13px; }
    </style>
</head>
<body>

<div class="navbar">
    <h1>Portail Administratif (UCA)</h1>
    <a href="logout.php">Déconnexion</a>
</div>

<div class="container">
    <h2>Liste des étudiants et suivis des soumissions</h2>

    <table>
        <thead>
            <tr>
                <th>Numéro Apogée</th>
                <th>Nom & Prénom</th>
                <th>Email</th>
                <th>Statut Compte</th>
                <th>Documents Soumis</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($etudiants) > 0): ?>
                <?php foreach ($etudiants as $etudiant): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($etudiant['apogee']); ?></strong></td>
                        <td><?php echo htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['email']); ?></td>
                        <td>
                            <?php if ($etudiant['est_active'] == 1): ?>
                                <span class="status active">Activé</span>
                            <?php else: ?>
                                <span class="status inactive">En attente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge"><?php echo $etudiant['nb_fichiers']; ?> fichier(s)</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #777;">Aucun étudiant inscrit pour le moment.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>