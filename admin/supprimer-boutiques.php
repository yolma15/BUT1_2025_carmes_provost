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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_boutique'])) {
    $boutique_id = $_POST['boutique_id'];
    
    try {
        // Vérifier que la boutique est bien vide
        $stmt = $conn->prepare("
            SELECT COUNT(*) as stock_count 
            FROM stocks 
            WHERE boutique_id = :boutique_id AND quantite > 0
        ");
        $stmt->bindParam(':boutique_id', $boutique_id);
        $stmt->execute();
        $stock_count = $stmt->fetch(PDO::FETCH_ASSOC)['stock_count'];
        
        if ($stock_count > 0) {
            $error = "Cette boutique contient encore du stock et ne peut pas être supprimée.";
        } else {
            // Supprimer d'abord tous les stocks (même à 0)
            $stmt = $conn->prepare("DELETE FROM stocks WHERE boutique_id = :boutique_id");
            $stmt->bindParam(':boutique_id', $boutique_id);
            $stmt->execute();
            
            // Puis supprimer la boutique
            $stmt = $conn->prepare("DELETE FROM boutiques WHERE id = :boutique_id");
            $stmt->bindParam(':boutique_id', $boutique_id);
            
            if ($stmt->execute()) {
                $message = "Boutique supprimée avec succès !";
            } else {
                $error = "Erreur lors de la suppression de la boutique.";
            }
        }
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Récupérer les boutiques vides
$stmt = $conn->query("
    SELECT b.*, u.prenom, u.nom as nom_gerant,
           COALESCE(SUM(s.quantite), 0) as stock_total
    FROM boutiques b 
    LEFT JOIN utilisateurs u ON b.utilisateur_id = u.id 
    LEFT JOIN stocks s ON b.id = s.boutique_id
    GROUP BY b.id
    HAVING stock_total = 0
    ORDER BY b.nom
");
$boutiques_vides = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer des boutiques - Admin Confiz</title>
    <link rel="stylesheet" href="../autrecss.css">
    <link rel="stylesheet" href="admin.css">
</head>

<body>
    <?php include '../header.php'; ?>
    
    <main class="admin-main">
        <div class="admin-container">
            <div class="admin-header">
                <h1>Supprimer des boutiques vides</h1>
                <a href="dashboard.php" class="back-btn">← Retour au dashboard</a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (empty($boutiques_vides)): ?>
                <div class="no-boutiques">
                    <h3>✅ Aucune boutique vide</h3>
                    <p>Toutes vos boutiques contiennent du stock. Aucune suppression n'est possible pour le moment.</p>
                    <a href="dashboard.php" class="btn btn-primary">Retour au dashboard</a>
                </div>
            <?php else: ?>
                <div class="warning-section">
                    <h3>⚠️ Attention</h3>
                    <p>Vous êtes sur le point de supprimer des boutiques <strong>définitivement</strong>. Cette action est irréversible.</p>
                    <p>Seules les boutiques sans aucun stock peuvent être supprimées.</p>
                </div>
                
                <div class="boutiques-grid">
                    <?php foreach ($boutiques_vides as $boutique): ?>
                        <div class="boutique-card delete-candidate">
                            <div class="boutique-header">
                                <h3><?php echo htmlspecialchars($boutique['nom']); ?></h3>
                                <div class="boutique-stats">
                                    <span class="stat empty">Stock: 0</span>
                                </div>
                            </div>
                            
                            <div class="boutique-info">
                                <p><strong>Adresse:</strong> <?php echo htmlspecialchars($boutique['numero_rue'] . ' ' . $boutique['nom_adresse']); ?></p>
                                <p><?php echo htmlspecialchars($boutique['code_postal'] . ' ' . $boutique['ville'] . ', ' . $boutique['pays']); ?></p>
                                <p><strong>Gérant:</strong> 
                                    <?php echo $boutique['prenom'] ? htmlspecialchars($boutique['prenom'] . ' ' . $boutique['nom_gerant']) : 'Non assigné'; ?>
                                </p>
                            </div>
                            
                            <div class="boutique-actions">
                                <form method="POST" class="delete-form" onsubmit="return confirmDelete('<?php echo htmlspecialchars($boutique['nom']); ?>')">
                                    <input type="hidden" name="boutique_id" value="<?php echo $boutique['id']; ?>">
                                    <button type="submit" name="supprimer_boutique" class="btn btn-danger">
                                        🗑️ Supprimer définitivement
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
        function confirmDelete(nomBoutique) {
            return confirm(
                `⚠️ ATTENTION ⚠️\n\n` +
                `Vous êtes sur le point de supprimer définitivement la boutique :\n` +
                `"${nomBoutique}"\n\n` +
                `Cette action est IRRÉVERSIBLE !\n\n` +
                `Êtes-vous absolument certain de vouloir continuer ?`
            );
        }
    </script>
    
    <?php include '../footer.php'; ?>
</body>
</html>