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

// Traitement des actions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_stock':
            $confiserie_id = $_POST['confiserie_id'];
            $nouvelle_quantite = $_POST['quantite'];
            
            $stmt = $conn->prepare("UPDATE stocks SET quantite = :quantite, date_de_modification = NOW() WHERE boutique_id = :boutique_id AND confiserie_id = :confiserie_id");
            $stmt->bindParam(':quantite', $nouvelle_quantite);
            $stmt->bindParam(':boutique_id', $boutique_id);
            $stmt->bindParam(':confiserie_id', $confiserie_id);
            
            if ($stmt->execute()) {
                $message = 'Stock mis à jour avec succès.';
            } else {
                $error = 'Erreur lors de la mise à jour du stock.';
            }
            break;
            
        case 'remove_product':
            $confiserie_id = $_POST['confiserie_id'];
            
            $stmt = $conn->prepare("DELETE FROM stocks WHERE boutique_id = :boutique_id AND confiserie_id = :confiserie_id");
            $stmt->bindParam(':boutique_id', $boutique_id);
            $stmt->bindParam(':confiserie_id', $confiserie_id);
            
            if ($stmt->execute()) {
                $message = 'Produit retiré de la boutique avec succès.';
            } else {
                $error = 'Erreur lors de la suppression du produit.';
            }
            break;
    }
}

// Récupérer les stocks de la boutique
$stmt = $conn->prepare("
    SELECT s.*, c.nom, c.type, c.prix, c.illustration 
    FROM stocks s 
    JOIN confiseries c ON s.confiserie_id = c.id 
    WHERE s.boutique_id = :boutique_id 
    ORDER BY c.nom
");
$stmt->bindParam(':boutique_id', $boutique_id);
$stmt->execute();
$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des stocks - <?php echo htmlspecialchars($boutique['nom']); ?></title>
    <link rel="stylesheet" href="../autrecss.css">
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>
    <?php include '../header.php'; ?>
    
    <main class="dashboard-main">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1>Gestion des stocks</h1>
                <h2><?php echo htmlspecialchars($boutique['nom']); ?></h2>
                <a href="dashboard.php" class="back-btn">← Retour au dashboard</a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="stocks-section">
                <div class="section-header">
                    <h3>Produits en stock (<?php echo count($stocks); ?>)</h3>
                    <a href="ajouter-produit.php?boutique_id=<?php echo $boutique_id; ?>" class="btn btn-primary">
                        Ajouter un produit
                    </a>
                </div>
                
                <?php if (empty($stocks)): ?>
                    <div class="no-stocks">
                        <p>Aucun produit en stock dans cette boutique.</p>
                        <a href="ajouter-produit.php?boutique_id=<?php echo $boutique_id; ?>" class="btn btn-primary">
                            Ajouter le premier produit
                        </a>
                    </div>
                <?php else: ?>
                    <div class="stocks-grid">
                        <?php foreach ($stocks as $stock): ?>
                            <div class="stock-card">
                                <div class="product-image">
                                    <img src="../<?php echo $stock['illustration'] ?: 'img/default.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($stock['nom']); ?>">
                                </div>
                                
                                <div class="product-info">
                                    <h4><?php echo htmlspecialchars($stock['nom']); ?></h4>
                                    <span class="product-type"><?php echo htmlspecialchars($stock['type']); ?></span>
                                    <p class="product-price"><?php echo number_format($stock['prix'], 2, ',', ' '); ?> €</p>
                                </div>
                                
                                <div class="stock-management">
                                    <form method="POST" class="stock-form">
                                        <input type="hidden" name="action" value="update_stock">
                                        <input type="hidden" name="confiserie_id" value="<?php echo $stock['confiserie_id']; ?>">
                                        
                                        <div class="quantity-control">
                                            <label>Quantité en stock :</label>
                                            <div class="quantity-input">
                                                <button type="button" class="qty-btn" onclick="changeQuantity(this, -1)">-</button>
                                                <input type="number" name="quantite" value="<?php echo $stock['quantite']; ?>" 
                                                       min="0" class="qty-input">
                                                <button type="button" class="qty-btn" onclick="changeQuantity(this, 1)">+</button>
                                            </div>
                                        </div>
                                        
                                        <div class="stock-actions">
                                            <button type="submit" class="btn btn-small btn-primary">Mettre à jour</button>
                                        </div>
                                    </form>
                                    
                                    <form method="POST" class="remove-form" onsubmit="return confirm('Êtes-vous sûr de vouloir retirer ce produit de la boutique ?')">
                                        <input type="hidden" name="action" value="remove_product">
                                        <input type="hidden" name="confiserie_id" value="<?php echo $stock['confiserie_id']; ?>">
                                        <button type="submit" class="btn btn-small btn-danger">Retirer</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <script>
        function changeQuantity(button, change) {
            const input = button.parentNode.querySelector('.qty-input');
            const currentValue = parseInt(input.value) || 0;
            const newValue = Math.max(0, currentValue + change);
            input.value = newValue;
        }
    </script>
    
    <?php include '../footer.php'; ?>
</body>
</html>