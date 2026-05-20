<?php
session_start();
require_once 'lib/db.php';

// Sécurité : On vérifie que l'utilisateur est connecté et qu'il est bien un étudiant
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['etudiant_file'])) {
    $file = $_FILES['etudiant_file'];
    $etudiant_id = $_SESSION['user_id'];

    // 1. Configurations de sécurité (Contrôle exigé par le sujet)
    $max_size = 2 * 1024 * 1024; // Limite à 2 Mo
    $allowed_extensions = ['pdf', 'png', 'jpg', 'jpeg'];
    $allowed_mime_types = ['application/pdf', 'image/png', 'image/jpeg'];

    // Récupération des infos du fichier
    $file_name = basename($file['name']);
    $file_size = $file['size'];
    $file_tmp  = $file['tmp_name'];
    $file_err  = $file['error'];
    
    // Extraction de l'extension et vérification du vrai type MIME (Sécurité avancée)
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $real_mime_type = finfo_file($finfo, $file_tmp);
    finfo_close($finfo);

    // 2. Vérifications des restrictions
    if ($file_err !== 0) {
        $_SESSION['upload_error'] = "Une erreur est survenue lors du transfert.";
    } elseif (!in_array($file_ext, $allowed_extensions) || !in_array($real_mime_type, $allowed_mime_types)) {
        $_SESSION['upload_error'] = "Format non autorisé ! Seuls les fichiers PDF, PNG et JPG sont acceptés.";
    } elseif ($file_size > $max_size) {
        $_SESSION['upload_error'] = "Le fichier est trop lourd (Maximum 2 Mo).";
    } else {
        // 3. Sécurisation du nom de fichier pour éviter d'écraser un fichier existant ou les caractères spéciaux
        $clean_name = time() . '_' . preg_replace("/[^a-zA-Z0-8.]/", "_", $file_name);
        $target_dir = "uploads/";
        $target_path = $target_dir . $clean_name;

        // Déplacement du fichier vers le dossier uploads/
        if (move_uploaded_file($file_tmp, $target_path)) {
            try {
                // Enregistrement des métadonnées dans la base de données (Nom, date automatique, ID étudiant)
                $stmt = $pdo->prepare("INSERT INTO fichiers (etudiant_id, nom_fichier) VALUES (?, ?)");
                $stmt->execute([$etudiant_id, $clean_name]);

                $_SESSION['upload_success'] = "Fichier téléversé avec succès !";
            } catch (PDOException $e) {
                $_SESSION['upload_error'] = "Erreur lors de l'enregistrement en base de données.";
            }
        } else {
            $_SESSION['upload_error'] = "Impossible de sauvegarder le fichier sur le serveur.";
        }
    }
}

// Redirection immédiate vers le tableau de bord pour afficher le retour visuel
header("Location: dashboard.php");
exit;