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
$boutique_id = $_GET['boutique_id'] ?? 0;

// Vérifier que la boutique appartient au gérant
$stmt = $conn->prepare("SELECT * FROM boutiques WHERE id = :id AND utilisateur_id = :user_id");
$stmt->bindParam(':id', $boutique_id);
$stmt->bindParam(':user_id', $user['id']);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    header('Location: dashboard.php?error=boutique_not_found');
    exit;
}

$boutique = $stmt->fetch(PDO::FETCH_ASSOC);

$message = '';
$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confiserie_id = $_POST['confiserie_id'];
    $quantite = $_POST['quantite'];
    
    // Vérifier si le produit n'est pas déjà dans la boutique
    $stmt = $conn->prepare("SELECT * FROM stocks WHERE boutique_id = :boutique_id AND confiserie_id = :confiserie_id");
    $stmt->bindParam(':boutique_id', $boutique_id);
    $stmt->bindParam(':confiserie_id', $confiserie_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $error = 'Ce produit est déjà présent dans cette boutique.';
    } else {
        // Ajouter le produit
        $stmt = $conn->prepare("INSERT INTO stocks (boutique_id, confiserie_id, quantite, date_de_modification) VALUES (:boutique_id, :confiserie_id, :quantite, NOW())");
        $stmt->bindParam(':boutique_id', $boutique_id);
        $stmt->bindParam(':confiserie_id', $confiserie_id);
        $stmt->bindParam(':quantite', $quantite);
        
        if ($stmt->execute()) {
            $message = 'Produit ajouté avec succès à la boutique.';
        } else {
            $error = 'Erreur lors de l\'ajout du produit.';
        }
    }
}

// Récupérer les confiseries disponibles (pas encore dans cette boutique)
$stmt = $conn->prepare("
    SELECT c.* FROM confiseries c 
    WHERE c.id NOT IN (
        SELECT s.confiserie_id FROM stocks s WHERE s.boutique_id = :boutique_id
    )
    ORDER BY c.nom
");
$stmt->bindParam(':boutique_id', $boutique_id);
$stmt->execute();
$confiseries_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un produit - <?php echo htmlspecialchars($boutique['nom']); ?></title>
    <link rel="stylesheet" href="../autrecss.css">
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>
    <?php include '../header.php'; ?>
    
    <main class="dashboard-main">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1>Ajouter un produit</h1>
                <h2><?php echo htmlspecialchars($boutique['nom']); ?></h2>
                <a href="stocks.php?boutique_id=<?php echo $boutique_id; ?>" class="back-btn">← Retour aux stocks</a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (empty($confiseries_disponibles)): ?>
                <div class="no-products">
                    <p>Tous les produits du catalogue sont déjà présents dans cette boutique.</p>
                    <a href="stocks.php?boutique_id=<?php echo $boutique_id; ?>" class="btn btn-primary">
                        Retour à la gestion des stocks
                    </a>
                </div>
            <?php else: ?>
                <div class="add-product-section">
                    <h3>Sélectionner un produit à ajouter</h3>
                    
                    <div class="products-grid">
                        <?php foreach ($confiseries_disponibles as $confiserie): ?>
                            <div class="product-card selectable" onclick="selectProduct(<?php echo $confiserie['id']; ?>)">
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
                                
                                <div class="select-indicator">
                                    <span class="checkmark">✓</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <form method="POST" class="add-form" id="add-form" style="display: none;">
                        <div class="form-group">
                            <label for="quantite">Quantité initiale :</label>
                            <input type="number" id="quantite" name="quantite" min="1" value="10" required>
                        </div>
                        
                        <input type="hidden" id="confiserie_id" name="confiserie_id">
                        
                        <div class="form-actions">
                            <button type="button" onclick="cancelSelection()" class="btn btn-secondary">Annuler</button>
                            <button type="submit" class="btn btn-primary">Ajouter le produit</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        function selectProduct(productId) {
            // Désélectionner tous les produits
            document.querySelectorAll('.product-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Sélectionner le produit cliqué
            event.currentTarget.classList.add('selected');
            
            // Mettre à jour le formulaire
            document.getElementById('confiserie_id').value = productId;
            document.getElementById('add-form').style.display = 'block';
            
            // Faire défiler vers le formulaire
            document.getElementById('add-form').scrollIntoView({ behavior: 'smooth' });
        }
        
        function cancelSelection() {
            document.querySelectorAll('.product-card').forEach(card => {
                card.classList.remove('selected');
            });
            document.getElementById('add-form').style.display = 'none';
        }
    </script>
    
    <?php include '../footer.php'; ?>
</body>
</html>     