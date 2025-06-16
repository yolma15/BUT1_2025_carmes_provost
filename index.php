<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root"; // à modifier selon votre configuration
$password = ""; // à modifier selon votre configuration
$dbname = "confiz";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erreur de connexion: " . $e->getMessage();
}

// Récupérer les confiseries pour l'affichage sur la page d'accueil
$stmt = $conn->prepare("SELECT id, nom, description, illustration FROM confiseries LIMIT 3");
$stmt->execute();
$confiseries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confiz - Revendeur officiel de bonbons Haribo</title>
    <link rel="stylesheet" href="autrecss.css">
    <script src="product-loader.js"></script>
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="nav-links">
                <a href="index.php">Accueil</a>
                <a href="boutiques.php">Boutiques</a>
                <a href="catalogue.php">Catalogue</a>
            </div>

            <div class="logo-container">
                <img src="confiz.png" alt="Logo confiz">
            </div>
            
            <div class="cart-login">
                <a href="panier.php" class="cart-icon">🛒</a>
                <a href="login.php" class="login-btn">Connexion</a>
            </div>
        </nav>
    </header>

    <main>
        <section id="moi" class="hero">
            <div class="hero-content">
                <h1>Nous sommes Confiz</h1>
                <h2>Revendeur officiel de bonbons Haribo</h2>
                <p class="hero-desc">
                    Créé en 2012 par Jeff Beyett, la société Confiz est une entreprise qui revend principalement des bonbons de la marque Haribo
                </p>
                <div class="hero-cta">
                    <a href="catalogue.php" class="primary-btn">Catalogue</a>
                    <a href="boutiques.php" class="secondary-btn">Nos boutiques</a>
                </div>
            </div>
            <div class="hero-image-container">
                <img class="hero-image" src="confiz.png" alt="Logo Confiz">
            </div>
        </section>

        <section class="daily-candies">
            <h2>Confiseries du jour !</h2>
            <div class="candy-grid">
                <?php foreach($confiseries as $confiserie): ?>
                <div class="candy-card">
                    <img src="<?php echo !empty($confiserie['illustration']) ? $confiserie['illustration'] : 'images/candies/default.jpg'; ?>" alt="<?php echo $confiserie['nom']; ?>">
                    <h3><?php echo $confiserie['nom']; ?></h3>
                    <p><?php echo $confiserie['description']; ?></p>
                    <a href="produit.php?id=<?php echo $confiserie['id']; ?>" class="voir-plus">Voir plus →</a>
                </div>
                <?php endforeach; ?>
            </div>
            <a href="catalogue.php" class="catalogue-button">Catalogue</a>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <p>&copy; Confiz <?php echo date('Y'); ?>. Tous droits réservés</p>
            <div class="footer-links">
                <a href="contact.php">Contact</a>
                <a href="mentions-legales.php">Mentions légales</a>
            </div>
        </div>
    </footer>

    <script src="main.js"></script>
</body>
</html>