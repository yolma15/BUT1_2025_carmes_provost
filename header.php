<header class="main-header">
    <nav class="navbar">
        <!-- Menu hamburger pour mobile -->
        <div class="hamburger-menu" id="hamburger-menu">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </div>

        <!-- Navigation principale -->
        <div class="nav-links" id="nav-links">
            <a href="index.php" class="nav-link">
                <i class="nav-icon">🏠</i>
                <span>Accueil</span>
            </a>
            <a href="boutiques.php" class="nav-link">
                <i class="nav-icon">🏪</i>
                <span>Boutiques</span>
            </a>
            <a href="catalogue.php" class="nav-link">
                <i class="nav-icon">🍭</i>
                <span>Catalogue</span>
            </a>
        </div>

        <!-- Logo central -->
        <div class="logo-container">
            <a href="index.php" class="logo-link">
                <img src="confiz.png" alt="Logo Confiz" class="logo-img">
                <span class="logo-text">Confiz</span>
            </a>
        </div>
        
        <!-- Actions utilisateur -->
        <div class="user-actions">
            <div class="search-container">
                <button class="search-toggle" id="search-toggle">
                    <i class="search-icon">🔍</i>
                </button>
                <div class="search-dropdown" id="search-dropdown">
                    <form class="search-form" action="catalogue.php" method="GET">
                        <input type="text" name="search" placeholder="Rechercher des bonbons..." class="search-input">
                        <button type="submit" class="search-submit">Rechercher</button>
                    </form>
                </div>
            </div>
            
            <a href="panier.php" class="cart-link">
                <i class="cart-icon">🛒</i>
                <span class="cart-count" id="cart-count">0</span>
                <span class="cart-text">Panier</span>
            </a>
            
            <div class="auth-container">
                <a href="login.php" class="login-btn">
                    <i class="user-icon">👤</i>
                    <span>Connexion</span>
                </a>
            </div>
        </div>
    </nav>
</header>