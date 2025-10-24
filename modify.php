<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Kaizhi</title>
    <meta property="og:image" content="./images/kanzhi.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome pour les icônes <-- Recommandé par mon LLM, très utile pour afficher des icons (SVG) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2a3f5f;
            --secondary-color: #4a76a8;
            --accent-color: #5bc0be;
            --light-color: #f8f9fa;
            --dark-color: #1a1a2e;
            --gradient-bg: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        }
        
        body {
            background: var(--gradient-bg);
            color: var(--light-color);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-custom {
            background-color: rgba(26, 26, 46, 0.9);
            backdrop-filter: blur(10px);
        }
        
        .card-custom {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .btn-primary-custom {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .btn-primary-custom:hover {
            background-color: #4ca8a6;
            border-color: #4ca8a6;
        }
        
        .btn-danger-custom {
            background-color: #e94560;
            border-color: #e94560;
        }
        
        .table-custom {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table-custom thead {
            background-color: rgba(42, 63, 95, 0.8);
        }
        
        .table-custom th, .table-custom td {
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--light-color);
        }
        
        .form-control-custom {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--light-color);
        }
        
        .form-control-custom:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: var(--accent-color);
            color: var(--light-color);
            box-shadow: 0 0 0 0.25rem rgba(91, 192, 190, 0.25);
        }
        
        .logo {
            max-width: 120px;
            filter: drop-shadow(0 0 10px rgba(91, 192, 190, 0.5));
        }
        
        .game-image {
            border-radius: 8px;
            transition: transform 0.3s ease;
        }
        
        .game-image:hover {
            transform: scale(1.05);
        }
        
        .search-container {
            position: relative;
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-color);
        }
        
        .search-input {
            padding-left: 45px;
        }
        
        .section-title {
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 10px;
            margin-bottom: 25px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <?php
        include("./modele/Game.php");

        // Connexion à la base de données avec VARIABLE DE CONNEXION (Pour éviter de les écrires en dur / Push sur le Github)
        // Utilisation de ".env.php" pour le stockage des variables des données sensibles
        // Utilisation de "parse_ini_file" pour lire les variables depuis le fichier .env.php
        $env_vars = parse_ini_file('.env.php');
        $host = $env_vars['HOST'];
        $user = $env_vars['USER'];
        $psw = $env_vars['PASSWORD'];
        $db = $env_vars['DB_NAME'];

        try {
            $bdd = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $psw);
        } catch (Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
        }

        // Gestion de la récupération des informations à modifier (GET)
        if (($_SERVER['REQUEST_METHOD'] == 'GET') && isset($_GET['modify']) && !empty($_GET['modify'])) {
            $query = $bdd->prepare("SELECT * FROM games WHERE gameid=:gameidtoedit");
            $gameidtoedit = $_GET['modify'];
            $query->bindParam(':gameidtoedit', $gameidtoedit, PDO::PARAM_STR);
            $query->execute();
            $infos = $query->fetchAll(PDO::FETCH_CLASS, 'Game');
        }

        // Gestion de la soumission du formulaire de modification (POST)
        if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['newgameid']) && !empty($_POST['newgameid'])) && (isset($_POST['newtitre']) && !empty($_POST['newtitre'])) && (isset($_POST['oldgameid']) && !empty($_POST['oldgameid']))) {   
            
            // Récupération des données du formulaire avec gestion pour les données optionnelles, même vides
            $oldgameid = $_POST['oldgameid'];
            $gameid = $_POST['newgameid'];
            $titre = $_POST['newtitre'];
            $description = $_POST['newdescription'] ?? '';
            $prix = $_POST['newprix'] ?? 0;
            $image = $_POST['newimage'] ?? '';
            $liensteam = $_POST['newliensteam'] ?? '';
            
            // Convertir le prix en centimes
            if ($prix > 0) {
                $prix = (int)($prix * 100);
            }
            
            // Vérifier si le nouveau gameid existe déjà (UNIQUEMENT si il est différent de l'ancien)
            if ($gameid != $oldgameid) {
                $checkQuery = $bdd->prepare("SELECT COUNT(*) FROM games WHERE gameid = :gameid");
                $checkQuery->bindParam(':gameid', $gameid, PDO::PARAM_INT);
                $checkQuery->execute();
                $exists = $checkQuery->fetchColumn();
                
                if ($exists > 0) {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Erreur :</strong> Un jeu avec ce GameID existe déjà !
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
                } else {
                    updateGame($bdd, $oldgameid, $gameid, $titre, $description, $prix, $image, $liensteam);
                }
            } else {
                updateGame($bdd, $oldgameid, $gameid, $titre, $description, $prix, $image, $liensteam);
            }
        }

        function updateGame($bdd, $oldgameid, $gameid, $titre, $description, $prix, $image, $liensteam) {
            $query = $bdd->prepare("UPDATE games SET gameid=:gameid, titre=:titre, description=:description, prix=:prix, image=:image, liensteam=:liensteam WHERE gameid=:oldgameid");
            
            $query->bindParam(':oldgameid', $oldgameid, PDO::PARAM_INT);
            $query->bindParam(':gameid', $gameid, PDO::PARAM_INT);
            $query->bindParam(':titre', $titre, PDO::PARAM_STR);
            $query->bindParam(':description', $description, PDO::PARAM_STR);
            $query->bindParam(':prix', $prix, PDO::PARAM_INT);
            $query->bindParam(':image', $image, PDO::PARAM_STR);
            $query->bindParam(':liensteam', $liensteam, PDO::PARAM_STR);
            
            if ($query->execute()) {
                header("Location: index.php");
                exit();
            } else {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Erreur lors de la modification du jeu
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
            }
        }
    ?>

    <!-- Navigation -->
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="./images/kanzhi.png" alt="Kaizhi Logo" class="logo me-2">
            <span class="fw-bold fs-4" style="color: var(--accent-color);">Kaizhi</span>
        </a>
    </div>

    <!-- En-tête -->
    <div class="container my-5">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold mb-3">Content que vous utilisiez <span style="color: var(--accent-color);">Kaizhi</span></h1>
            <p class="lead">Quelles modifications voulez-vous apporter à ce jeu ?</p>
        </div>

        <!-- Formulaire de modification de jeu -->
        <div class="card card-custom p-4 mb-5">
            <h2 class="section-title h4"><i class="fas fa-pencil me-2"></i>Modifier des informations</h2>
            <form method="POST" class="row g-3">
                <?php foreach ($infos as $info): ?>
                    <div class="col-md-6 col-lg-2" hidden>
                        <label class="form-label">OldGameID</label>
                        <input type="number" name="oldgameid" class="form-control form-control-custom" 
                            value="<?php echo $info->getGameid() ?? ''; ?>" readonly>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label">GameID</label>
                        <input type="number" name="newgameid" class="form-control form-control-custom" 
                            value="<?php echo $info->getGameid() ?? ''; ?>" required>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label">Titre</label>
                        <input type="text" name="newtitre" class="form-control form-control-custom" placeholder="Titre du jeu *" value="<?php echo $info->getTitre() ?? ''; ?>" required>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="newdescription" class="form-control form-control-custom" placeholder="Description du jeu" value="<?php echo $info->getDescription() ?? ''; ?>">
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label">Prix (€)</label>
                        <input type="text" name="newprix" class="form-control form-control-custom" placeholder="Prix"
                        value="<?php $prix = $info->getPrix() ?? 0;
                            // Convertion des centimes en euros --> Sur la base, le prix est stocké en centimes (Ex: 5.99€ = INT "599")
                            if ($prix > 0) {
                                echo number_format($prix / 100, 2);
                            } ?>">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label">Image URL</label>
                        <input type="text" name="newimage" class="form-control form-control-custom" placeholder="URL de l'image" value="<?php echo $info->getImage() ?? ''; ?>">
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label">Lien Steam</label>
                        <input type="text" name="newliensteam" class="form-control form-control-custom" placeholder="Lien Steam" value="<?php echo $info->getLiensteam() ?? ''; ?>">
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary-custom px-4">
                            <i class="fas fa-check me-2"></i>Confirmer les modifications
                        </button>
                    </div>
                <?php endforeach; ?>
            </form>
        </div>

    <!-- Footer -->
    <footer class="py-4 mt-5" style="background-color: rgba(26, 26, 46, 0.8);">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 Kaizhi. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>