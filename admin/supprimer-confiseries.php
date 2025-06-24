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
$message = '';
$error = '';

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_confiserie'])) {
    $confiserie_id = $_POST['confiserie_id'];
    
    try {
        // Vérifier que la confiserie n'est vendue nulle part
        $stmt = $conn->prepare("
            SELECT COUNT(*) as stock_count 
            FROM stocks 
            WHERE confiserie_id = :confiserie_id AND quantite > 0
        ");
        $stmt->bindParam(':confiserie_id', $confiserie_id);
        $stmt->execute();
        $stock_count = $stmt->fetch(PDO::FETCH_ASSOC)['stock_count'];
        
        if ($stock_count > 0) {
            $error = "Cette confiserie est encore en vente dans au moins une boutique et ne peut pas être supprimée.";
        } else {
            // Supprimer d'abord tous les stocks (même à 0)
            $stmt = $conn->prepare("DELETE FROM stocks WHERE confiserie_id = :confiserie_id");
            $stmt->bindParam(':confiserie_id', $confiserie_id);
            $stmt->execute();
            
            // Puis supprimer la confiserie
            $stmt = $conn->prepare("DELETE FROM confiseries WHERE id = :confiserie_id");
            $stmt->bindParam(':confiserie_id', $confiserie_id);
            
            if ($stmt->execute()) {
                $message = "Confiserie supprimée avec succès du catalogue !";
            } else {
                $error = "Erreur lors de la suppression de la confiserie.";
            }
        }
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Récupérer les confiseries non vendues
$stmt = $conn->query("
    SELECT c.*
FROM confiseries c 
WHERE NOT EXISTS (
    SELECT 1 FROM stocks s 
    WHERE s.confiserie_id = c.id AND s.quantite > 0
)
ORDER BY c.nom
");
$confiseries_non_vendues = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer des confiseries - Admin Confiz</title>
    <link rel="stylesheet" href="../autrecss.css">
    <link rel="stylesheet" href="admin.css">
</head>

<body>
    <?php include '../header.php'; ?>
    
    <main class="admin-main">
        <div class="admin-container">
            <div class="admin-header">
                <h1>Supprimer des confiseries non vendues</h1>
                <a href="dashboard.php" class="back-btn">← Retour au dashboard</a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (empty($confiseries_non_vendues)): ?>
                <div class="no-products">
                    <h3>✅ Aucune confiserie non vendue</h3>
                    <p>Toutes vos confiseries sont en vente dans au moins une boutique. Aucune suppression n'est possible pour le moment.</p>
                    <a href="dashboard.php" class="btn btn-primary">Retour au dashboard</a>
                </div>
            <?php else: ?>
                <div class="warning-section">
                    <h3>⚠️ Attention</h3>
                    <p>Vous êtes sur le point de supprimer des confiseries <strong>définitivement</strong> du catalogue global. Cette action est irréversible.</p>
                    <p>Seules les confiseries qui ne sont en vente dans aucune boutique peuvent être supprimées.</p>
                </div>
                
                <div class="products-grid">
                    <?php foreach ($confiseries_non_vendues as $confiserie): ?>
                        <div class="product-card delete-candidate">
                            <div class="product-image">
                                <img src="../<?php echo $confiserie['illustration'] ?: 'img/default.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($confiserie['nom']); ?>">
                            </div>
                            
                            <div class="product-info">
                                <h4><?php echo htmlspecialchars($confiserie['nom']); ?></h4>
                                <span class="product-type"><?php echo htmlspecialchars($confiserie['type']); ?></span>
                                <p class="product-price"><?php echo number_format($confiserie['prix'], 2, ',', ' '); ?> €</p>
                                <p class="product-description"><?php echo htmlspecialchars(substr($confiserie['description'], 0, 100)); ?>...</p>
                            </div>
                            
                            <div class="product-status">
                                <span class="status-badge not-sold">❌ Non vendue</span>
                                <p class="status-info">Cette confiserie n'est en stock dans aucune boutique</p>
                            </div>
                            
                            <div class="product-actions">
                                <form method="POST" class="delete-form" onsubmit="return confirmDelete('<?php echo htmlspecialchars($confiserie['nom']); ?>')">
                                    <input type="hidden" name="confiserie_id" value="<?php echo $confiserie['id']; ?>">
                                    <button type="submit" name="supprimer_confiserie" class="btn btn-danger">
                                        🗑️ Supprimer du catalogue
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        function confirmDelete(nomConfiserie) {
            return confirm(
                `⚠️ ATTENTION ⚠️\n\n` +
                `Vous êtes sur le point de supprimer définitivement la confiserie :\n` +
                `"${nomConfiserie}"\n\n` +
                `Cette action supprimera la confiserie du catalogue global !\n` +
                `Cette action est IRRÉVERSIBLE !\n\n` +
                `Êtes-vous absolument certain de vouloir continuer ?`
            );
        }
    </script>
    
    <?php include '../footer.php'; ?>
</body>
</html>