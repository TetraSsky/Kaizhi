<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Kaizhi</title>
    <meta property="og:image" content="./images/kanzhi.png">
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
            $query = $bdd->prepare("SELECT * FROM games WHERE titre LIKE :search");
            $searchTerm = '%' . $_GET['search'] . '%';
            $query->bindParam(':search', $searchTerm, PDO::PARAM_STR);
            $query->execute();
            $games = $query->fetchAll(PDO::FETCH_CLASS, 'Game');
        } else {
            $query = $bdd->query("SELECT * FROM games");
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
            
            $checkQuery = $bdd->prepare("SELECT COUNT(*) FROM games WHERE gameid = :gameid");
            $checkQuery->bindParam(':gameid', $gameid, PDO::PARAM_INT);
            $checkQuery->execute();
            $exists = $checkQuery->fetchColumn();
        
            if ($exists > 0) {
                echo "<p style='color: red;'>Erreur : Un jeu avec ce GameID existe déjà !</p>";
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
                    echo "Erreur lors de l'ajout du jeu";
                }
            }
        }
    ?>

    <img src="./images/kanzhi.png"></img> <!-- Logo du site : Taille du fichier de base 500px x 500px-->
    <h1>Bienvenue sur le site de Kaizhi</h1>

    <!-- Formulaire de recherche parmi les jeux du tableau -->
    <form method="GET" action="index.php">
        <input type="text" name="search" placeholder="Rechercher un jeu...">
        <button type="submit" value="Rechercher">Rechercher</button>
    </form>

    <!-- Formulaire d'ajout de jeu -->
    <form method="POST" action="index.php">
        <input type="number" name="gameid" placeholder="GameID" required>
        <input type="text" name="titre" placeholder="Titre du jeu" required>
        <input type="text" name="description" placeholder="Description du jeu">
        <input type="text" name="prix" placeholder="Prix du jeu">
        <input type="file" name="image" placeholder="Image du jeu">
        <input type="text" name="liensteam" placeholder="Lien Steam du jeu">
        <button type="submit">Ajouter un nouveau jeu</button>
    </form>

    <!-- Tableau des jeux -->
    <table>
        <thead>
            <td>GameID</td>
            <td>Titre</td>
            <td>Description</td>
            <td>Prix</td>
            <td>Image</td>
            <td>Lien Steam</td>
        </thead>
        <tbody>
            <?php foreach ($games as $game): ?>
                <tr>
                    <td><?php echo $game->getGameid() ?? ''; ?></td>
                    <td><?php echo $game->getTitre() ?? ''; ?></td>
                    <td><?php echo $game->getDescription() ?? ''; ?></td>
                    <td><?php echo $game->getPrix() ?? ''; ?></td>
                    <td><img src="<?php echo $game->getImage() ?? ''; ?>" width="100"></td>
                    <td><a href="<?php echo $game->getLiensteam() ?? '#'; ?>" target="_blank">Voir sur Steam</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>