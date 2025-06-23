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

// Récupérer les types existants pour suggestions
$stmt = $conn->query("SELECT DISTINCT type FROM confiseries ORDER BY type");
$types_existants = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $type = trim($_POST['type']);
    $prix = $_POST['prix'];
    $description = trim($_POST['description']);
    $illustration = trim($_POST['illustration']);
    
    // Validation
    $errors = [];
    
    if (empty($nom)) {
        $errors[] = "Le nom de la confiserie est obligatoire.";
    }
    
    if (empty($type)) {
        $errors[] = "Le type de confiserie est obligatoire.";
    }
    
    if (empty($prix) || !is_numeric($prix) || $prix <= 0) {
        $errors[] = "Le prix doit être un nombre positif.";
    }
    
    if (empty($description)) {
        $errors[] = "La description est obligatoire.";
    }
    
    // Vérifier si le nom n'existe pas déjà
    $stmt = $conn->prepare("SELECT id FROM confiseries WHERE nom = :nom");
    $stmt->bindParam(':nom', $nom);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $errors[] = "Une confiserie avec ce nom existe déjà.";
    }
    
    if (empty($errors)) {
        try {
            // Insérer la nouvelle confiserie
            $stmt = $conn->prepare("
                INSERT INTO confiseries (nom, type, prix, illustration, description) 
                VALUES (:nom, :type, :prix, :illustration, :description)
            ");
            
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':prix', $prix);
            $stmt->bindParam(':illustration', $illustration);
            $stmt->bindParam(':description', $description);
            
            if ($stmt->execute()) {
                $message = "Confiserie ajoutée avec succès au catalogue global !";
                // Réinitialiser le formulaire
                $_POST = [];
            } else {
                $error = "Erreur lors de l'ajout de la confiserie.";
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout de la confiserie : " . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une confiserie - Admin Confiz</title>
    <link rel="stylesheet" href="../autrecss.css">
    <link rel="stylesheet" href="admin.css">
</head>

<body>
    <?php include '../header.php'; ?>
    
    <main class="admin-main">
        <div class="admin-container">
            <div class="admin-header">
                <h1>Ajouter une nouvelle confiserie</h1>
                <a href="dashboard.php" class="back-btn">← Retour au dashboard</a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" class="admin-form" enctype="multipart/form-data">
                    <div class="form-section">
                        <h3>Informations de base</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nom">Nom de la confiserie *</label>
                                <input type="text" id="nom" name="nom" required 
                                       value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>"
                                       placeholder="Ex: Bonbon Haribo Fraise">
                            </div>
                            
                            <div class="form-group">
                                <label for="type">Type de confiserie *</label>
                                <input type="text" id="type" name="type" required list="types-list"
                                       value="<?php echo isset($_POST['type']) ? htmlspecialchars($_POST['type']) : ''; ?>"
                                       placeholder="Ex: Gélifié, Chocolat, Caramel...">
                                <datalist id="types-list">
                                    <?php foreach ($types_existants as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                                <small class="form-help">Tapez pour voir les suggestions ou créez un nouveau type</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="prix">Prix (€) *</label>
                                <input type="number" id="prix" name="prix" required step="0.01" min="0.01"
                                       value="<?php echo isset($_POST['prix']) ? htmlspecialchars($_POST['prix']) : ''; ?>"
                                       placeholder="Ex: 2.50">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>Description et image</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="description">Description *</label>
                                <textarea id="description" name="description" required rows="4"
                                          placeholder="Décrivez la confiserie, ses saveurs, sa texture..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="illustration">URL de l'image</label>
                                <input type="url" id="illustration" name="illustration"
                                       value="<?php echo isset($_POST['illustration']) ? htmlspecialchars($_POST['illustration']) : ''; ?>"
                                       placeholder="Ex: ./img/bonbon-fraise.jpg">
                                <small class="form-help">Laissez vide pour utiliser l'image par défaut</small>
                            </div>
                        </div>
                        
                        <div class="image-preview" id="image-preview" style="display: none;">
                            <h4>Aperçu de l'image :</h4>
                            <img id="preview-img" src="" alt="Aperçu" style="max-width: 200px; max-height: 200px; border-radius: 10px;">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" onclick="resetForm()" class="btn btn-secondary">Réinitialiser</button>
                        <button type="submit" class="btn btn-success">Ajouter au catalogue</button>
                    </div>
                </form>
            </div>
            
            <!-- Aperçu des dernières confiseries ajoutées -->
            <div class="recent-confiseries">
                <h3>Dernières confiseries ajoutées</h3>
                <?php
                $stmt = $conn->query("SELECT * FROM confiseries ORDER BY id DESC LIMIT 6");
                $dernieres_confiseries = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <div class="confiseries-preview">
                    <?php foreach ($dernieres_confiseries as $confiserie): ?>
                        <div class="confiserie-preview-card">
                            <div class="preview-image">
                                <img src="../<?php echo $confiserie['illustration'] ?: 'img/default.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($confiserie['nom']); ?>">
                            </div>
                            <div class="preview-info">
                                <h4><?php echo htmlspecialchars($confiserie['nom']); ?></h4>
                                <span class="preview-type"><?php echo htmlspecialchars($confiserie['type']); ?></span>
                                <p class="preview-price"><?php echo number_format($confiserie['prix'], 2, ',', ' '); ?> €</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        function resetForm() {
            if (confirm('Êtes-vous sûr de vouloir réinitialiser le formulaire ?')) {
                document.querySelector('.admin-form').reset();
                document.getElementById('image-preview').style.display = 'none';
            }
        }
        
        // Aperçu de l'image
        document.getElementById('illustration').addEventListener('input', function(e) {
            const url = e.target.value;
            const preview = document.getElementById('image-preview');
            const img = document.getElementById('preview-img');
            
            if (url) {
                img.src = url;
                img.onload = function() {
                    preview.style.display = 'block';
                };
                img.onerror = function() {
                    preview.style.display = 'none';
                };
            } else {
                preview.style.display = 'none';
            }
        });
        
        // Suggestions de types
        document.getElementById('type').addEventListener('input', function(e) {
            const value = e.target.value.toLowerCase();
            const suggestions = ['Gélifié', 'Chocolat', 'Caramel', 'Réglisse', 'Menthe', 'Fruité', 'Acide', 'Douceur', 'Classique', 'Fête', 'Frais', 'Gourmandise'];
            
            // Vous pouvez ajouter une logique de suggestion plus avancée ici
        });
    </script>
    
    <?php include '../footer.php'; ?>
</body>
</html>