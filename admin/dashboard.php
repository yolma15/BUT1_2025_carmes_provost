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

// Boutiques vides (sans stock)
$stmt = $conn->query("
    SELECT COUNT(*) as total 
    FROM boutiques b 
    WHERE NOT EXISTS (
        SELECT 1 FROM stocks s WHERE s.boutique_id = b.id AND s.quantite > 0
    )
");
$stats['boutiques_vides'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Confiseries non vendues (pas en stock nulle part)
$stmt = $conn->query("
    SELECT COUNT(*) as total 
    FROM confiseries c 
    WHERE NOT EXISTS (
        SELECT 1 FROM stocks s WHERE s.confiserie_id = c.id AND s.quantite > 0
    )
");
$stats['confiseries_non_vendues'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
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
            
            <!-- Alertes de nettoyage -->
            <?php if ($stats['boutiques_vides'] > 0 || $stats['confiseries_non_vendues'] > 0): ?>
                <div class="cleanup-alerts">
                    <h2>🧹 Nettoyage recommandé</h2>
                    <div class="alert-grid">
                        <?php if ($stats['boutiques_vides'] > 0): ?>
                            <div class="cleanup-card">
                                <div class="cleanup-icon">🏪</div>
                                <div class="cleanup-info">
                                    <h4><?php echo $stats['boutiques_vides']; ?> boutique(s) vide(s)</h4>
                                    <p>Ces boutiques n'ont aucun stock et peuvent être supprimées</p>
                                    <a href="supprimer-boutiques.php" class="btn btn-danger btn-small">Gérer les suppressions</a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($stats['confiseries_non_vendues'] > 0): ?>
                            <div class="cleanup-card">
                                <div class="cleanup-icon">🍭</div>
                                <div class="cleanup-info">
                                    <h4><?php echo $stats['confiseries_non_vendues']; ?> confiserie(s) non vendue(s)</h4>
                                    <p>Ces confiseries ne sont en vente dans aucune boutique</p>
                                    <a href="supprimer-confiseries.php" class="btn btn-danger btn-small">Gérer les suppressions</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Actions principales -->
            <div class="admin-actions">
                <div class="action-section">
                    <h2>Gestion des boutiques</h2>
                    <div class="action-buttons">
                        <a href="ajouter-boutique.php" class="btn btn-success">Ajouter une boutique</a>
                        <a href="supprimer-boutiques.php" class="btn btn-danger">Supprimer des boutiques</a>
                    </div>
                </div>
                
                <div class="action-section">
                    <h2>Gestion du catalogue</h2>
                    <div class="action-buttons">
                        <a href="ajouter-confiserie.php" class="btn btn-success">Ajouter une confiserie</a>
                        <a href="supprimer-confiseries.php" class="btn btn-danger">Supprimer des confiseries</a>
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