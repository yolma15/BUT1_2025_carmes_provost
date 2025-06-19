<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($bonbon['nom']); ?> - Confiz</title>
    <link rel="stylesheet" href="produit.css">
    <link rel="stylesheet" href="autrecss.css">
</head>
<style>
   
html, body {
    height: 100%;
    margin: 0;
}

body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

main {
    flex: 1 0 auto;
}

footer {
    flex-shrink: 0;
}


.product-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
    min-height: 60vh; 
}
</style>
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
            
            <div class="hamburger-menu">
                <div class="hamburger-line"></div>
                <div class="hamburger-line"></div>
                <div class="hamburger-line"></div>
            </div>
        </nav>
    </header>

    <main>
        <div class="product-container">
            <a href="catalogue.php" class="back-button">← Retour au catalogue</a>
            
            <div class="product-details">
                <div class="product-image-container">
                    <img 
                        src="<?php echo !empty($bonbon['illustration']) ? htmlspecialchars($bonbon['illustration']) : 'images/candies/default.jpg'; ?>" 
                        alt="<?php echo htmlspecialchars($bonbon['nom']); ?>" 
                        class="product-image"
                    >
                </div>
                
                <div class="product-info">
                    <h1 class="product-name"><?php echo htmlspecialchars($bonbon['nom']); ?></h1>
                    <span class="product-type"><?php echo htmlspecialchars($bonbon['type']); ?></span>
                    <p class="product-price"><?php echo number_format($bonbon['prix'], 2, ',', ' '); ?> €</p>
                    <div class="product-description">
                        <?php echo nl2br(htmlspecialchars($bonbon['description'])); ?>
                    </div>
                    
                    <div class="stock-info">
                        <h2 class="stock-title">Disponibilité en boutique</h2>
                        
                        <?php if (count($stocks) > 0): ?>
                            <div class="stock-list">
                                <?php foreach ($stocks as $stock): ?>
                                    <div class="stock-item">
                                        <p class="stock-boutique"><?php echo htmlspecialchars($stock['boutique_nom']); ?></p>
                                        <p class="stock-ville"><?php echo htmlspecialchars($stock['ville']); ?></p>
                                        <p class="stock-quantite <?php 
                                            if ($stock['quantite'] <= 0) echo 'stock-epuise';
                                            elseif ($stock['quantite'] < 10) echo 'stock-limite';
                                            else echo 'stock-disponible';
                                        ?>">
                                            <?php 
                                                if ($stock['quantite'] <= 0) echo 'Épuisé';
                                                elseif ($stock['quantite'] < 10) echo 'Stock limité: ' . $stock['quantite'] . ' restants';
                                                else echo 'En stock: ' . $stock['quantite'] . ' disponibles';
                                            ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>Ce produit n'est actuellement disponible dans aucune boutique.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
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
    <script src="product-loader.js"></script>
</body>
</html>