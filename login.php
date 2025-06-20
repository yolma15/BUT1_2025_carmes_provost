<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confiz - Revendeur officiel de bonbons Haribo</title>
    <link rel="stylesheet" href="autrecss.css">
    <script src="product-loader.js" defer></script>
</head>

<?php 
include 'header.php';
?>
<body>
    <main>
        <section class="login-section">
            <div class="login-container">
                <h1>Connexion</h1>
                <form action="login.php" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required placeholder="Entrez votre email">
                    </div>
                    <div class="form-group  ">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required placeholder="Entrez votre mot de passe">
                    </div>
                    <button type="submit" class="login-btn">Se connecter</button>
        </section>
    </main>
</body>
<?php 
include 'footer.php';
?>