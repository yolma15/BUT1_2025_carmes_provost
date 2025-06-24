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

function getBoutiqueDetails($conn, $boutiqueId) {
    // Récupérer les détails de la boutique
    $stmt = $conn->prepare("SELECT * FROM boutiques WHERE id = :id");
    $stmt->bindParam(':id', $boutiqueId, PDO::PARAM_INT);
    $stmt->execute();
    $boutique = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$boutique) {
        return null;
    }
    
    // Récupérer les produits disponibles dans cette boutique
    $stmt = $conn->prepare("
        SELECT c.*, s.quantite 
        FROM confiseries c 
        JOIN stocks s ON c.id = s.confiserie_id 
        WHERE s.boutique_id = :boutique_id 
        ORDER BY c.nom
    ");
    $stmt->bindParam(':boutique_id', $boutiqueId, PDO::PARAM_INT);
    $stmt->execute();
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return ['boutique' => $boutique, 'produits' => $produits];
}

function renderBoutiqueDetails($boutiqueData) {
    $boutique = $boutiqueData['boutique'];
    $produits = $boutiqueData['produits'];
    
    // Générer le HTML des produits
    $produitsHtml = '';
    if (count($produits) > 0) {
        $produitsHtml = '
            <div class="produits-section">
                <h2 class="produits-title">Produits disponibles (' . count($produits) . ')</h2>
                <div class="produits-grid">';
        
        foreach ($produits as $produit) {
            $stockClass = '';
            $stockText = '';
            
            if ($produit['quantite'] <= 0) {
                $stockClass = 'stock-epuise';
                $stockText = 'Épuisé';
            } elseif ($produit['quantite'] < 10) {
                $stockClass = 'stock-limite';
                $stockText = 'Stock limité: ' . $produit['quantite'];
            } else {
                $stockClass = 'stock-disponible';
                $stockText = 'En stock: ' . $produit['quantite'];
            }
            
            $illustration = !empty($produit['illustration']) ? htmlspecialchars($produit['illustration']) : './img/default.jpg';
            
            $produitsHtml .= '
                <div class="produit-boutique-card">
                    <div class="produit-boutique-image">
                        <img src="' . $illustration . '" 
                             alt="' . htmlspecialchars($produit['nom']) . '"
                             onerror="this.src=\'./img/default.jpg\'">
                    </div>
                    <div class="produit-boutique-info">
                        <h3 class="produit-boutique-name">' . htmlspecialchars($produit['nom']) . '</h3>
                        <span class="produit-boutique-type">' . htmlspecialchars($produit['type']) . '</span>
                        <p class="produit-boutique-price">' . number_format($produit['prix'], 2, ',', ' ') . ' €</p>
                        <div class="produit-boutique-stock ' . $stockClass . '">
                            ' . $stockText . '
                        </div>
                    </div>
                </div>
            ';
        }
        
        $produitsHtml .= '
                </div>
            </div>';
    } else {
        $produitsHtml = '
            <div class="no-products">
                <h2>Aucun produit disponible</h2>
                <p>Cette boutique n\'a actuellement aucun produit en stock.</p>
            </div>
        ';
    }
    
    return '
        <div class="boutique-detail-header">
            <h1 class="boutique-detail-name">' . htmlspecialchars($boutique['nom']) . '</h1>
            <div class="boutique-detail-address">
                ' . htmlspecialchars($boutique['numero_rue'] . ' ' . $boutique['nom_adresse']) . '<br>
                ' . htmlspecialchars($boutique['code_postal'] . ' ' . $boutique['ville']) . '<br>
                ' . htmlspecialchars($boutique['pays']) . '
            </div>
        </div>
        ' . $produitsHtml;
}
?>