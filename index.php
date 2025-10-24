<?php session_start(); ?>
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
        include("Game.php");

        // Connexion à la base de données avec VARIABLE DE CONNEXION (Pour éviter de les écrires en dur / Push sur le Github)
        // Utilisation de ".env.php" pour le stockage des variables des données sensibles
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

        // Gestion de la recherche (GET)
        if (($_SERVER['REQUEST_METHOD'] == 'GET') && isset($_GET['search']) && !empty($_GET['search'])) {
            $query = $bdd->prepare("SELECT * FROM games WHERE titre LIKE :search ORDER BY gameid");
            $searchTerm = '%' . $_GET['search'] . '%';
            $query->bindParam(':search', $searchTerm, PDO::PARAM_STR);
            $query->execute();
            $games = $query->fetchAll(PDO::FETCH_CLASS, 'Game');
        } else {
            $query = $bdd->query("SELECT * FROM games ORDER BY gameid LIMIT 500");
            $games = $query->fetchAll(PDO::FETCH_CLASS, 'Game');
        }

        // Gestion de l'ajout (POST)
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['gameid']) && !empty($_POST['gameid'])) {
            $query = $bdd->prepare("INSERT INTO games (gameid, titre, description, prix, image, liensteam) VALUES (:gameid, :titre, :description, :prix, :image, :liensteam)");
            
            $gameid = $_POST['gameid'];
            $titre = $_POST['titre'];
            $description = $_POST['description'] ?? '';
            $prix = $_POST['prix'] ?? 0;
            $image = $_POST['image'] ?? '';
            $liensteam = $_POST['liensteam'] ?? '';

            // Convertir le prix en centimes
            if ($prix > 0) {
                $prix = (int)($prix * 100);
            }
            
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
                            Erreur lors de l\'ajout du jeu
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
                }
            }
        }

        // Gestion de la suppression (GET)
        if (($_SERVER['REQUEST_METHOD'] == 'GET') && isset($_GET['delete']) && !empty($_GET['delete'])) {
            $gameid = $_GET['delete'];
            $query = $bdd->prepare("DELETE FROM games WHERE gameid = :gameid");
            $query->bindParam(':gameid', $gameid, PDO::PARAM_INT);
            if ($query->execute()) {
                header("Location: index.php");
                exit();
            } else {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Erreur lors de la suppression du jeu
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
            <h1 class="display-4 fw-bold mb-3">Bienvenue sur <span style="color: var(--accent-color);">Kaizhi</span></h1>
            <p class="lead">Découvrez notre catalogue de jeux vidéo et trouvez votre bonheur facilement !</p>
        </div>

        <!-- Formulaire de recherche -->
        <div class="card card-custom p-4 mb-5">
            <h2 class="section-title h4"><i class="fas fa-search me-2"></i>Rechercher un jeu</h2>
            <form method="GET" action="index.php" class="row g-3">
                <div class="col-md-8">
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" class="form-control form-control-custom search-input" placeholder="Rechercher un jeu par son titre...">
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary-custom w-100">
                        <i class="fas fa-search me-2"></i>Rechercher
                    </button>
                </div>
            </form>
        </div>

        <!-- Formulaire d'ajout de jeu -->
        <div class="card card-custom p-4 mb-5">
            <h2 class="section-title h4"><i class="fas fa-plus-circle me-2"></i>Ajouter un nouveau jeu</h2>
            <form method="POST" class="row g-3">
                <div class="col-md-6 col-lg-2">
                    <label class="form-label">GameID</label>
                    <input type="number" name="gameid" class="form-control form-control-custom" placeholder="GameID *" required>
                </div>
                <div class="col-md-6 col-lg-3">
                    <label class="form-label">Titre</label>
                    <input type="text" name="titre" class="form-control form-control-custom" placeholder="Titre du jeu *" required>
                </div>
                <div class="col-md-6 col-lg-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control form-control-custom" placeholder="Description du jeu">
                </div>
                <div class="col-md-6 col-lg-2">
                    <label class="form-label">Prix (€)</label>
                    <input type="text" name="prix" class="form-control form-control-custom" placeholder="Prix">
                </div>
                <div class="col-md-6 col-lg-3">
                    <label class="form-label">Image URL</label>
                    <input type="text" name="image" class="form-control form-control-custom" placeholder="URL de l'image">
                </div>
                <div class="col-md-6 col-lg-4">
                    <label class="form-label">Lien Steam</label>
                    <input type="text" name="liensteam" class="form-control form-control-custom" placeholder="Lien Steam">
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary-custom px-4">
                        <i class="fas fa-plus me-2"></i>Ajouter le jeu
                    </button>
                </div>
            </form>
        </div>

        <!-- Tableau des jeux (A peupler de données en provenance de la BDD) -->
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title h4 m-0"><i class="fas fa-gamepad me-2"></i>Liste des jeux</h2>
                <span class="badge bg-primary-custom fs-6"><?php echo count($games); ?> jeu(x) trouvé(s)</span>
            </div>

            <?php if (count($games) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead class="text-center">
                            <tr>
                                <th>GameID</th>
                                <th>Titre</th>
                                <th>Description</th>
                                <th>Prix</th>
                                <th>Image</th>
                                <th>Lien Steam</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($games as $game): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $game->getGameid() ?? ''; ?></td>
                                    <td class="fw-bold"><?php echo $game->getTitre() ?? ''; ?></td>
                                    <td><?php echo strlen($game->getDescription() ?? '') > 50 ? substr($game->getDescription(), 0, 50) . '...' : $game->getDescription(); ?></td>
                                    <td>
                                        <?php 
                                            $prix = $game->getPrix() ?? 0;
                                            // Convertion des centimes en euros --> Sur la base, le prix est stocké en centimes (Ex: 5.99€ = INT "599")
                                            if ($prix > 0) {
                                                echo number_format($prix / 100, 2) . ' €';
                                            } else {
                                                echo 'Gratuit'; // Pas forcément TOUJOURS vrai (Ex : Bundles apparaissent gratuit car pas comptés comme des jeux)
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($game->getImage() && $game->getImage() != ''): ?>
                                            <img src="<?php echo $game->getImage(); ?>" class="game-image" width="120" alt="<?php echo $game->getTitre(); ?>">
                                        <?php else: ?>
                                            <img src="./images/noimage.png" class="game-image" width="120" alt="Image non disponible">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($game->getLiensteam()): ?>
                                            <a href="<?php echo $game->getLiensteam(); ?>" target="_blank" class="btn btn-sm btn-outline-light">
                                                <i class="fab fa-steam me-1"></i> Steam
                                            </a>
                                        <?php else: ?>
                                            <span class="text-center">Aucun lien</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="index.php?delete=<?php echo $game->getGameid(); ?>" 
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce jeu ?')"
                                            class="btn btn-sm btn-danger-custom">
                                            <i class="fas fa-trash me-1"></i>
                                            Supprimer
                                        </a>

                                        <a href="modify.php?modify=<?php echo $game->getGameid(); ?>" 
                                            onclick="return confirm('Souhaitez-vous modifier ce jeu ?')"
                                            class="btn btn-sm btn-primary-custom">
                                            <i class="fas fa-pencil me-1"></i>
                                            Modifier
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-gamepad fa-4x mb-3" style="color: var(--accent-color);"></i>
                    <h3 class="h4">Aucun jeu trouvé</h3>
                    <p>La base de données ne contient aucun jeu, soit aucun résultat trouvé pour votre recherche.</p>
                    <a href="index.php">Retour à l'accueil</a>
                </div>
            <?php endif; ?>
        </div>
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