<?php
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

// Récupération des types de confiseries pour le filtre
$stmt = $conn->prepare("SELECT DISTINCT type FROM confiseries ORDER BY type");
$stmt->execute();
$types = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Initialisation des variables de filtrage et pagination
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 12;
$offset = ($page - 1) * $items_per_page;

// Construction de la requête SQL avec filtres
$sql = "SELECT id, nom, type, prix, illustration, description FROM confiseries";
$params = [];

if (!empty($type_filter) || !empty($search)) {
    $sql .= " WHERE ";
    $conditions = [];
    
    if (!empty($type_filter)) {
        $conditions[] = "type = :type";
        $params[':type'] = $type_filter;
    }
    
    if (!empty($search)) {
        $conditions[] = "nom LIKE :search OR description LIKE :search";
        $params[':search'] = "%$search%";
    }
    
    $sql .= implode(" AND ", $conditions);
}

// Requête pour compter le nombre total d'éléments (pour la pagination)
$count_sql = str_replace("id, nom, type, prix, illustration, description", "COUNT(*) as total", $sql);
$stmt = $conn->prepare($count_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_items = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_items / $items_per_page);

// Ajout de l'ordre et de la pagination à la requête principale
$sql .= " ORDER BY nom ASC LIMIT :offset, :limit";
$params[':offset'] = $offset;
$params[':limit'] = $items_per_page;

// Exécution de la requête principale
$stmt = $conn->prepare($sql);
foreach ($params as $key => $value) {
    if ($key == ':offset' || $key == ':limit') {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $value);
    }
}
$stmt->execute();
$confiseries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue - Confiz</title>
    <link rel="stylesheet" href="autrecss.css">
    <link rel="stylesheet" href="catalogue.css">
</head>

<body>
    <?php 
    include 'header.php'; 
    ?>

    <main>
        <section class="catalogue-header">
            <h1 class="catalogue-title">Catalogue</h1>
            <p>Découvrez notre sélection de confiseries délicieuses</p>
        </section>
        
        <section class="filters">
            <form class="filter-form" method="GET" action="catalogue.php">
                <select name="type">
                    <option value="">Tous les types</option>
                    <?php foreach($types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $type_filter === $type ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" name="search" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>">
                
                <button type="submit">Filtrer</button>
                
                <?php if(!empty($type_filter) || !empty($search)): ?>
                    <a href="catalogue.php" class="reset-filters">Réinitialiser les filtres</a>
                <?php endif; ?>
            </form>
        </section>
        
        <section class="catalogue-container">
            <?php if(count($confiseries) > 0): ?>
                <div class="catalogue-grid">
                    <?php foreach($confiseries as $confiserie): ?>
                        <div class="product-card">
                            <img 
                                src="<?php echo !empty($confiserie['illustration']) ? htmlspecialchars($confiserie['illustration']) : 'images/candies/default.jpg'; ?>" 
                                alt="<?php echo htmlspecialchars($confiserie['nom']); ?>" 
                                class="product-image"
                            >
                            <div class="product-info">
                                <h3 class="product-name1"><?php echo htmlspecialchars($confiserie['nom']); ?></h3>
                                <span class="product-type1"><?php echo htmlspecialchars($confiserie['type']); ?></span>
                                <p class="product-price1"><?php echo number_format($confiserie['prix'], 2, ',', ' '); ?> €</p>
                                <p class="product-description1"><?php echo htmlspecialchars($confiserie['description']); ?></p>
                                <a href="produit.php?id=<?php echo $confiserie['id']; ?>" class="view-product">Voir le produit</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if($page > 1): ?>
                            <a href="?page=<?php echo $page-1; ?><?php echo !empty($type_filter) ? '&type='.urlencode($type_filter) : ''; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">Précédent</a>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?><?php echo !empty($type_filter) ? '&type='.urlencode($type_filter) : ''; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if($page < $total_pages): ?>
                            <a href="?page=<?php echo $page+1; ?><?php echo !empty($type_filter) ? '&type='.urlencode($type_filter) : ''; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">Suivant</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="no-results">
                    <h2>Aucun résultat trouvé</h2>
                    <p>Essayez de modifier vos critères de recherche ou consultez notre catalogue complet.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>
<?php
include_once 'footer.php';
?>
    <script src="main.js"></script>
</body>
</html>