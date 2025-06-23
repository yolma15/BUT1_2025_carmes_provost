<?php
require_once '../auth.php';
requireAuth(['admin', 'super-gerant']);

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

// Statistiques générales
$stats = [];

// Nombre total de boutiques
$stmt = $conn->query("SELECT COUNT(*) as total FROM boutiques");
$stats['boutiques'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Nombre total de confiseries
$stmt = $conn->query("SELECT COUNT(*) as total FROM confiseries");
$stats['confiseries'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Nombre total de gérants
$stmt = $conn->query("SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'gerant'");
$stats['gerants'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Stock total
$stmt = $conn->query("SELECT SUM(quantite) as total FROM stocks");
$stats['stock_total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Confiz</title>
    <link rel="stylesheet" href="../autrecss.css">
    <link rel="stylesheet" href="admin.css">
</head>

<body>
    <?php include '../header.php'; ?>
    
    <main class="admin-main">
        <div class="admin-container">
            <div class="admin-header">
                <h1>Dashboard Super-Gérant</h1>
                <p>Bienvenue, <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></p>
                <a href="../logout.php" class="logout-btn">Déconnexion</a>
            </div>
            
            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🏪</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['boutiques']; ?></h3>
                        <p>Boutiques</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🍭</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['confiseries']; ?></h3>
                        <p>Confiseries</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['gerants']; ?></h3>
                        <p>Gérants</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['stock_total']); ?></h3>
                        <p>Stock total</p>
                    </div>
                </div>
            </div>
            
            <!-- Actions principales -->
            <div class="admin-actions">
                <div class="action-section">
                    <h2>Gestion des boutiques</h2>
                    <div class="action-buttons">
                        <a href="ajouter-boutique.php" class="btn btn-success">Ajouter une boutique</a>
                    </div>
                </div>
                
                <div class="action-section">
                    <h2>Gestion du catalogue</h2>
                    <div class="action-buttons">
                        <a href="ajouter-confiserie.php" class="btn btn-success">Ajouter une confiserie</a>
                    </div>
                </div>
                
                <div class="action-section">
                    <h2>Gestion des utilisateurs</h2>
                    <div class="action-buttons">
                        <a href="ajouter-utilisateur.php" class="btn btn-success">Ajouter un utilisateur</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../footer.php'; ?>
</body>
</html>