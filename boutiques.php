<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "confiz";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erreur de connexion: " . $e->getMessage();
    exit;
}

// Vérifier si on doit afficher une boutique spécifique
$boutiqueId = isset($_GET['boutique']) ? (int)$_GET['boutique'] : 0;

if ($boutiqueId > 0) {
    // Rediriger vers la page de détails de la boutique
    header("Location: boutique-details.php?id=$boutiqueId");
    exit;
}

// Récupérer toutes les boutiques
$stmt = $conn->prepare("SELECT * FROM boutiques ORDER BY nom");
$stmt->execute();
$boutiques = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Boutiques - Confiz</title>
    <link rel="stylesheet" href="autrecss.css">
    <link rel="stylesheet" href="boutiques.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <main>
        <section class="boutiques-header">
            <h1 class="boutiques-title">Nos Boutiques</h1>
            <p>Découvrez nos points de vente et leurs spécialités</p>
        </section>
        
        <section class="boutiques-container">
            <div class="boutiques-grid">
                <?php foreach($boutiques as $boutique): ?>
                    <div class="boutique-card">
                        <div class="boutique-image">
                            <img src="boutique-<?php echo $boutique['id']; ?>.jpg" 
                                 alt="<?php echo htmlspecialchars($boutique['nom']); ?>"
                                 onerror="this.src='./img/boutique-default.jpg'">
                        </div>
                        <div class="boutique-info">
                            <h3 class="boutique-name"><?php echo htmlspecialchars($boutique['nom']); ?></h3>
                            <p class="boutique-address">
                                <?php echo htmlspecialchars($boutique['numero_rue'] . ' ' . $boutique['nom_adresse']); ?><br>
                                <?php echo htmlspecialchars($boutique['code_postal'] . ' ' . $boutique['ville']); ?><br>
                                <?php echo htmlspecialchars($boutique['pays']); ?>
                            </p>
                            <a href="boutique-details.php?id=<?php echo $boutique['id']; ?>" class="voir-boutique-btn">
                                Voir les produits
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <script src="main.js"></script>
</body>
</html>