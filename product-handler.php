<?php
// Configuration de la base de données
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

function getProductDetails($conn, $productId) {
    // Récupérer les détails du produit
    $stmt = $conn->prepare("SELECT * FROM confiseries WHERE id = :id");
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $bonbon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bonbon) {
        return null;
    }
    
    // Récupérer les stocks
    $stmt = $conn->prepare("
        SELECT s.quantite, b.nom as boutique_nom, b.ville 
        FROM stocks s 
        JOIN boutiques b ON s.boutique_id = b.id 
        WHERE s.confiserie_id = :id
    ");
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return ['bonbon' => $bonbon, 'stocks' => $stocks];
}

function renderProductPage($productData) {
    $bonbon = $productData['bonbon'];
    $stocks = $productData['stocks'];
    
    // Générer le HTML des stocks
    $stocksHtml = '';
    if (count($stocks) > 0) {
        $stocksHtml = '<div class="stock-list">';
        foreach ($stocks as $stock) {
            $stockClass = '';
            $stockText = '';
            
            if ($stock['quantite'] <= 0) {
                $stockClass = 'stock-epuise';
                $stockText = 'Épuisé';
            } elseif ($stock['quantite'] < 10) {
                $stockClass = 'stock-limite';
                $stockText = 'Stock limité: ' . $stock['quantite'] . ' restants';
            } else {
                $stockClass = 'stock-disponible';
                $stockText = 'En stock: ' . $stock['quantite'] . ' disponibles';
            }
            
            $stocksHtml .= '
                <div class="stock-item">
                    <p class="stock-boutique">' . htmlspecialchars($stock['boutique_nom']) . '</p>
                    <p class="stock-ville">' . htmlspecialchars($stock['ville']) . '</p>
                    <p class="stock-quantite ' . $stockClass . '">' . $stockText . '</p>
                </div>
            ';
        }
        $stocksHtml .= '</div>';
    } else {
        $stocksHtml = '<p>Ce produit n\'est actuellement disponible dans aucune boutique.</p>';
    }
    
    return '
        <div class="product-container">
            <a href="catalogue.php" class="back-button">← Retour au catalogue</a>
            
            <div class="product-details">
                <div class="product-image-container">
                    <img 
                        src="' . (!empty($bonbon['illustration']) ? htmlspecialchars($bonbon['illustration']) : 'images/candies/default.jpg') . '" 
                        alt="' . htmlspecialchars($bonbon['nom']) . '" 
                        class="product-image"
                    >
                </div>
                
                <div class="product-info">
                    <h1 class="product-name">' . htmlspecialchars($bonbon['nom']) . '</h1>
                    <span class="product-type">' . htmlspecialchars($bonbon['type']) . '</span>
                    <p class="product-price">' . number_format($bonbon['prix'], 2, ',', ' ') . ' €</p>
                    <div class="product-description">
                        ' . nl2br(htmlspecialchars($bonbon['description'])) . '
                    </div>
                    
                    <div class="stock-info">
                        <h2 class="stock-title">Disponibilité en boutique</h2>
                        ' . $stocksHtml . '
                    </div>
                </div>
            </div>
        </div>
    ';
}
?>