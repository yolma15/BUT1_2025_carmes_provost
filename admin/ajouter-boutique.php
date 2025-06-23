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

// Récupérer la liste des gérants disponibles (sans boutique assignée ou pouvant gérer plusieurs boutiques)
$stmt = $conn->query("SELECT id, username, prenom, nom, email FROM utilisateurs WHERE role = 'gerant' ORDER BY prenom, nom");
$gerants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $numero_rue = trim($_POST['numero_rue']);
    $nom_adresse = trim($_POST['nom_adresse']);
    $code_postal = trim($_POST['code_postal']);
    $ville = trim($_POST['ville']);
    $pays = trim($_POST['pays']);
    $utilisateur_id = $_POST['utilisateur_id'] ?: null;
    
    // Validation
    $errors = [];
    
    if (empty($nom)) {
        $errors[] = "Le nom de la boutique est obligatoire.";
    }
    
    if (empty($numero_rue)) {
        $errors[] = "Le numéro de rue est obligatoire.";
    }
    
    if (empty($nom_adresse)) {
        $errors[] = "Le nom de l'adresse est obligatoire.";
    }
    
    if (empty($code_postal)) {
        $errors[] = "Le code postal est obligatoire.";
    } elseif (!preg_match('/^\d{5}$/', $code_postal)) {
        $errors[] = "Le code postal doit contenir 5 chiffres.";
    }
    
    if (empty($ville)) {
        $errors[] = "La ville est obligatoire.";
    }
    
    if (empty($pays)) {
        $errors[] = "Le pays est obligatoire.";
    }
    
    // Vérifier si le gérant existe
    if ($utilisateur_id) {
        $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE id = :id AND role = 'gerant'");
        $stmt->bindParam(':id', $utilisateur_id);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            $errors[] = "Le gérant sélectionné n'existe pas.";
        }
    }
    
    if (empty($errors)) {
        try {
            // Insérer la nouvelle boutique
            $stmt = $conn->prepare("
                INSERT INTO boutiques (nom, utilisateur_id, numero_rue, nom_adresse, code_postal, ville, pays) 
                VALUES (:nom, :utilisateur_id, :numero_rue, :nom_adresse, :code_postal, :ville, :pays)
            ");
            
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':utilisateur_id', $utilisateur_id);
            $stmt->bindParam(':numero_rue', $numero_rue);
            $stmt->bindParam(':nom_adresse', $nom_adresse);
            $stmt->bindParam(':code_postal', $code_postal);
            $stmt->bindParam(':ville', $ville);
            $stmt->bindParam(':pays', $pays);
            
            if ($stmt->execute()) {
                $message = "Boutique créée avec succès !";
                // Réinitialiser le formulaire
                $_POST = [];
            } else {
                $error = "Erreur lors de la création de la boutique.";
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de la création de la boutique : " . $e->getMessage();
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
    <title>Ajouter une boutique - Admin Confiz</title>
    <link rel="stylesheet" href="../autrecss.css">
    <link rel="stylesheet" href="admin.css">
</head>

<body>
    <?php include '../header.php'; ?>
    
    <main class="admin-main">
        <div class="admin-container">
            <div class="admin-header">
                <h1>Ajouter une nouvelle boutique</h1>
                <a href="dashboard.php" class="back-btn">← Retour au dashboard</a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" class="admin-form">
                    <div class="form-section">
                        <h3>Informations de la boutique</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nom">Nom de la boutique *</label>
                                <input type="text" id="nom" name="nom" required 
                                       value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>"
                                       placeholder="Ex: La Sucrerie de Paris">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="utilisateur_id">Gérant assigné</label>
                                <select id="utilisateur_id" name="utilisateur_id">
                                    <option value="">Aucun gérant assigné</option>
                                    <?php foreach ($gerants as $gerant): ?>
                                        <option value="<?php echo $gerant['id']; ?>" 
                                                <?php echo (isset($_POST['utilisateur_id']) && $_POST['utilisateur_id'] == $gerant['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($gerant['prenom'] . ' ' . $gerant['nom'] . ' (' . $gerant['email'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-help">Vous pouvez assigner un gérant maintenant ou plus tard</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>Adresse de la boutique</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="numero_rue">Numéro de rue *</label>
                                <input type="text" id="numero_rue" name="numero_rue" required 
                                       value="<?php echo isset($_POST['numero_rue']) ? htmlspecialchars($_POST['numero_rue']) : ''; ?>"
                                       placeholder="Ex: 123">
                            </div>
                            
                            <div class="form-group">
                                <label for="nom_adresse">Nom de l'adresse *</label>
                                <input type="text" id="nom_adresse" name="nom_adresse" required 
                                       value="<?php echo isset($_POST['nom_adresse']) ? htmlspecialchars($_POST['nom_adresse']) : ''; ?>"
                                       placeholder="Ex: Rue de la Paix">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="code_postal">Code postal *</label>
                                <input type="text" id="code_postal" name="code_postal" required 
                                       pattern="[0-9]{5}" maxlength="5"
                                       value="<?php echo isset($_POST['code_postal']) ? htmlspecialchars($_POST['code_postal']) : ''; ?>"
                                       placeholder="Ex: 75001">
                            </div>
                            
                            <div class="form-group">
                                <label for="ville">Ville *</label>
                                <input type="text" id="ville" name="ville" required 
                                       value="<?php echo isset($_POST['ville']) ? htmlspecialchars($_POST['ville']) : ''; ?>"
                                       placeholder="Ex: Paris">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="pays">Pays *</label>
                                <select id="pays" name="pays" required>
                                    <option value="">Sélectionner un pays</option>
                                    <option value="France" <?php echo (isset($_POST['pays']) && $_POST['pays'] == 'France') ? 'selected' : 'selected'; ?>>France</option>
                                    <option value="Belgique" <?php echo (isset($_POST['pays']) && $_POST['pays'] == 'Belgique') ? 'selected' : ''; ?>>Belgique</option>
                                    <option value="Suisse" <?php echo (isset($_POST['pays']) && $_POST['pays'] == 'Suisse') ? 'selected' : ''; ?>>Suisse</option>
                                    <option value="Luxembourg" <?php echo (isset($_POST['pays']) && $_POST['pays'] == 'Luxembourg') ? 'selected' : ''; ?>>Luxembourg</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" onclick="resetForm()" class="btn btn-secondary">Réinitialiser</button>
                        <button type="submit" class="btn btn-success">Créer la boutique</button>
                    </div>
                </form>
            </div>
            
            <!-- Aperçu des boutiques existantes -->
            <div class="existing-boutiques">
                <h3>Boutiques existantes</h3>
                <?php
                $stmt = $conn->query("
                    SELECT b.*, u.prenom, u.nom as nom_gerant 
                    FROM boutiques b 
                    LEFT JOIN utilisateurs u ON b.utilisateur_id = u.id 
                    ORDER BY b.nom
                ");
                $boutiques_existantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <div class="boutiques-preview">
                    <?php foreach ($boutiques_existantes as $boutique): ?>
                        <div class="boutique-preview-card">
                            <h4><?php echo htmlspecialchars($boutique['nom']); ?></h4>
                            <p><?php echo htmlspecialchars($boutique['numero_rue'] . ' ' . $boutique['nom_adresse']); ?></p>
                            <p><?php echo htmlspecialchars($boutique['code_postal'] . ' ' . $boutique['ville']); ?></p>
                            <p class="gerant-info">
                                Gérant: <?php echo $boutique['prenom'] ? htmlspecialchars($boutique['prenom'] . ' ' . $boutique['nom_gerant']) : 'Non assigné'; ?>
                            </p>
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
            }
        }
        
        // Validation en temps réel du code postal
        document.getElementById('code_postal').addEventListener('input', function(e) {
            const value = e.target.value;
            if (value && !/^\d{0,5}$/.test(value)) {
                e.target.value = value.slice(0, -1);
            }
        });
    </script>
    
    <?php include '../footer.php'; ?>
</body>
</html>