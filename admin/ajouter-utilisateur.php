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

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password_input = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom']);
    $ddn = $_POST['ddn'];
    $role = $_POST['role'];
    
    // Validation
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Le nom d'utilisateur est obligatoire.";
    } elseif (strlen($username) < 3) {
        $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères.";
    }
    
    if (empty($email)) {
        $errors[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide.";
    }
    
    if (empty($password_input)) {
        $errors[] = "Le mot de passe est obligatoire.";
    } elseif (strlen($password_input) < 4) {
        $errors[] = "Le mot de passe doit contenir au moins 4 caractères.";
    }
    
    if ($password_input !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire.";
    }
    
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }
    
    if (empty($ddn)) {
        $errors[] = "La date de naissance est obligatoire.";
    }
    
    if (empty($role)) {
        $errors[] = "Le rôle est obligatoire.";
    } elseif (!in_array($role, ['client', 'gerant', 'admin', 'super-gerant'])) {
        $errors[] = "Le rôle sélectionné n'est pas valide.";
    }
    
    // Vérifier si l'username existe déjà
    $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $errors[] = "Ce nom d'utilisateur existe déjà.";
    }
    
    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $errors[] = "Cet email est déjà utilisé.";
    }
    
    if (empty($errors)) {
        try {
            // Insérer le nouvel utilisateur
            $stmt = $conn->prepare("
                INSERT INTO utilisateurs (username, password, email, role, prenom, nom, ddn) 
                VALUES (:username, :password, :email, :role, :prenom, :nom, :ddn)
            ");
            
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', md5($password_input)); 
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':ddn', $ddn);
            
            if ($stmt->execute()) {
                $message = "Utilisateur créé avec succès ! Identifiants : $username / $password_input";
                // Réinitialiser le formulaire
                $_POST = [];
            } else {
                $error = "Erreur lors de la création de l'utilisateur.";
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de la création de l'utilisateur : " . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

// Statistiques des utilisateurs
$stats_users = [];
$roles = ['client', 'gerant', 'admin', 'super-gerant'];
foreach ($roles as $role) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM utilisateurs WHERE role = :role");
    $stmt->bindParam(':role', $role);
    $stmt->execute();
    $stats_users[$role] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un utilisateur - Admin Confiz</title>
    <link rel="stylesheet" href="../autrecss.css">
    <link rel="stylesheet" href="admin.css">
</head>

<body>
    <?php include '../header.php'; ?>
    
    <main class="admin-main">
        <div class="admin-container">
            <div class="admin-header">
                <h1>Ajouter un nouvel utilisateur</h1>
                <a href="dashboard.php" class="back-btn">← Retour au dashboard</a>
            </div>
            
            <!-- Statistiques des utilisateurs -->
            <div class="user-stats">
                <h3>Statistiques des utilisateurs</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-info">
                            <h4><?php echo $stats_users['client']; ?></h4>
                            <p>Clients</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🏪</div>
                        <div class="stat-info">
                            <h4><?php echo $stats_users['gerant']; ?></h4>
                            <p>Gérants</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⚡</div>
                        <div class="stat-info">
                            <h4><?php echo $stats_users['admin'] + $stats_users['super-gerant']; ?></h4>
                            <p>Administrateurs</p>
                        </div>
                    </div>
                </div>
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
                        <h3>Informations de connexion</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Nom d'utilisateur *</label>
                                <input type="text" id="username" name="username" required minlength="3"
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                       placeholder="Ex: jean.dupont">
                                <small class="form-help">Au moins 3 caractères, sans espaces</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       placeholder="Ex: jean.dupont@email.com">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Mot de passe *</label>
                                <input type="password" id="password" name="password" required minlength="4"
                                       placeholder="Au moins 4 caractères">
                                <div class="password-strength" id="password-strength"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirmer le mot de passe *</label>
                                <input type="password" id="confirm_password" name="confirm_password" required
                                       placeholder="Répétez le mot de passe">
                                <div class="password-match" id="password-match"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>Informations personnelles</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="prenom">Prénom *</label>
                                <input type="text" id="prenom" name="prenom" required
                                       value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>"
                                       placeholder="Ex: Jean">
                            </div>
                            
                            <div class="form-group">
                                <label for="nom">Nom *</label>
                                <input type="text" id="nom" name="nom" required
                                       value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>"
                                       placeholder="Ex: Dupont">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="ddn">Date de naissance *</label>
                                <input type="date" id="ddn" name="ddn" required
                                       value="<?php echo isset($_POST['ddn']) ? htmlspecialchars($_POST['ddn']) : ''; ?>"
                                       max="<?php echo date('Y-m-d', strtotime('-13 years')); ?>">
                                <small class="form-help">L'utilisateur doit avoir au moins 13 ans</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Rôle *</label>
                                <select id="role" name="role" required>
                                    <option value="">Sélectionner un rôle</option>
                                    <option value="client" <?php echo (isset($_POST['role']) && $_POST['role'] == 'client') ? 'selected' : ''; ?>>
                                        Client - Accès basique au site
                                    </option>
                                    <option value="gerant" <?php echo (isset($_POST['role']) && $_POST['role'] == 'gerant') ? 'selected' : ''; ?>>
                                        Gérant - Gestion des boutiques assignées
                                    </option>
                                    <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>
                                        Administrateur - Accès complet
                                    </option>
                                    <option value="super-gerant" <?php echo (isset($_POST['role']) && $_POST['role'] == 'super-gerant') ? 'selected' : ''; ?>>
                                        Super-gérant - Gestion complète du système
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="role-description" id="role-description">
                        <!-- Description du rôle sélectionné -->
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" onclick="resetForm()" class="btn btn-secondary">Réinitialiser</button>
                        <button type="button" onclick="generatePassword()" class="btn btn-info">Générer mot de passe</button>
                        <button type="submit" class="btn btn-success">Créer l'utilisateur</button>
                    </div>
                </form>
            </div>
            
            <!-- Derniers utilisateurs créés -->
            <div class="recent-users">
                <h3>Derniers utilisateurs créés</h3>
                <?php
                $stmt = $conn->query("SELECT username, email, role, prenom, nom, ddn FROM utilisateurs ORDER BY id DESC LIMIT 5");
                $derniers_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <div class="users-preview">
                    <?php foreach ($derniers_users as $utilisateur): ?>
                        <div class="user-preview-card">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($utilisateur['prenom'], 0, 1) . substr($utilisateur['nom'], 0, 1)); ?>
                            </div>
                            <div class="user-info">
                                <h4><?php echo htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']); ?></h4>
                                <p><?php echo htmlspecialchars($utilisateur['email']); ?></p>
                                <span class="user-role role-<?php echo $utilisateur['role']; ?>">
                                    <?php echo ucfirst($utilisateur['role']); ?>
                                </span>
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
                document.getElementById('password-strength').innerHTML = '';
                document.getElementById('password-match').innerHTML = '';
                document.getElementById('role-description').innerHTML = '';
            }
        }
        
        function generatePassword() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let password = '';
            for (let i = 0; i < 8; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            
            document.getElementById('password').value = password;
            document.getElementById('confirm_password').value = password;
            
            // Mettre à jour les indicateurs
            checkPasswordStrength();
            checkPasswordMatch();
            
            alert('Mot de passe généré : ' + password + '\nN\'oubliez pas de le communiquer à l\'utilisateur !');
        }
        
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthDiv = document.getElementById('password-strength');
            
            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }
            
            let strength = 0;
            if (password.length >= 4) strength++;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            const levels = ['Très faible', 'Faible', 'Moyen', 'Fort', 'Très fort'];
            const colors = ['#ff4444', '#ff8800', '#ffaa00', '#88aa00', '#44aa44'];
            
            strengthDiv.innerHTML = `<span style="color: ${colors[strength-1]}">${levels[strength-1] || 'Très faible'}</span>`;
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('password-match');
            
            if (confirmPassword.length === 0) {
                matchDiv.innerHTML = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchDiv.innerHTML = '<span style="color: #44aa44">✓ Les mots de passe correspondent</span>';
            } else {
                matchDiv.innerHTML = '<span style="color: #ff4444">✗ Les mots de passe ne correspondent pas</span>';
            }
        }
        
        function updateRoleDescription() {
            const role = document.getElementById('role').value;
            const descDiv = document.getElementById('role-description');
            
            const descriptions = {
                'client': 'Le client peut naviguer sur le site, voir les produits et les boutiques. Accès limité aux fonctionnalités publiques.',
                'gerant': 'Le gérant peut gérer les stocks des boutiques qui lui sont assignées. Il peut ajouter/supprimer des produits et modifier les quantités.',
                'admin': 'L\'administrateur a un accès complet à toutes les fonctionnalités du système.',
                'super-gerant': 'Le super-gérant peut créer/supprimer des boutiques et des confiseries, gérer tous les utilisateurs et avoir une vue d\'ensemble du système.'
            };
            
            if (descriptions[role]) {
                descDiv.innerHTML = `<div class="role-info"><strong>Permissions :</strong> ${descriptions[role]}</div>`;
            } else {
                descDiv.innerHTML = '';
            }
        }
        
        // Event listeners
        document.getElementById('password').addEventListener('input', checkPasswordStrength);
        document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);
        document.getElementById('role').addEventListener('change', updateRoleDescription);
        
        // Validation du nom d'utilisateur
        document.getElementById('username').addEventListener('input', function(e) {
            const value = e.target.value;
            // Supprimer les espaces et caractères spéciaux
            e.target.value = value.replace(/[^a-zA-Z0-9._-]/g, '');
        });
    </script>
    
    <?php include '../footer.php'; ?>
</body>
</html>