<?php
require_once '../auth.php';
requireAuth(['gerant']);

// Connexion à la base de données
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "confiz";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

$user = getCurrentUser();

// Récupérer les boutiques du gérant
$stmt = $conn->prepare("SELECT * FROM boutiques WHERE utilisateur_id = :user_id");
$stmt->bindParam(':user_id', $user['id']);
$stmt->execute();
$boutiques = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques
$stats = [];
foreach ($boutiques as $boutique) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total_produits, SUM(quantite) as total_stock FROM stocks WHERE boutique_id = :boutique_id");
    $stmt->bindParam(':boutique_id', $boutique['id']);
    $stmt->execute();
    $stat = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats[$boutique['id']] = $stat;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Gérant - Confiz</title>
    <link rel="stylesheet" href="../autrecss.css">
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>
    <?php include '../header.php'; ?>
    
    <main class="dashboard-main">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1>Dashboard Gérant</h1>
                <p>Bienvenue, <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></p>
                <a href="../logout.php" class="logout-btn">Déconnexion</a>
            </div>
            
            <div class="dashboard-content">
                <div class="boutiques-section">
                    <h2>Mes Boutiques</h2>
                    
                    <?php if (empty($boutiques)): ?>
                        <div class="no-boutiques">
                            <p>Aucune boutique n'est assignée à votre compte.</p>
                        </div>
                    <?php else: ?>
                        <div class="boutiques-grid">
                            <?php foreach ($boutiques as $boutique): ?>
                                <div class="boutique-card">
                                    <div class="boutique-header">
                                        <h3><?php echo htmlspecialchars($boutique['nom']); ?></h3>
                                        <div class="boutique-stats">
                                            <span class="stat">
                                                <?php echo $stats[$boutique['id']]['total_produits']; ?> produits
                                            </span>
                                            <span class="stat">
                                                <?php echo $stats[$boutique['id']]['total_stock']; ?> en stock
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="boutique-info">
                                        <p><?php echo htmlspecialchars($boutique['numero_rue'] . ' ' . $boutique['nom_adresse']); ?></p>
                                        <p><?php echo htmlspecialchars($boutique['code_postal'] . ' ' . $boutique['ville']); ?></p>
                                    </div>
                                    
                                    <div class="boutique-actions">
                                        <a href="stocks.php?boutique_id=<?php echo $boutique['id']; ?>" class="btn btn-primary">
                                            Gérer les stocks
                                        </a>
                                        <a href="ajouter-produit.php?boutique_id=<?php echo $boutique['id']; ?>" class="btn btn-secondary">
                                            Ajouter un produit
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../footer.php'; ?>
</body>
</html>