<?php
session_start();

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

$error = '';
$success = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password_input = $_POST['password'];
    
    if (!empty($email) && !empty($password_input)) {
        // Rechercher l'utilisateur
        $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Vérifier le mot de passe (MD5 comme dans votre base)
            if (md5($password_input) === $user['password']) {
                // Connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['prenom'] = $user['prenom'];
                $_SESSION['nom'] = $user['nom'];
                
                // Redirection selon le rôle
                switch($user['role']) {
                    case 'admin':
                    case 'super-gerant':
                        header('Location: admin/dashboard.php');
                        break;
                    case 'gerant':
                        header('Location: gerant/dashboard.php');
                        break;
                    default:
                        header('Location: index.php');
                        break;
                }
                exit;
            } else {
                $error = 'Mot de passe incorrect.';
            }
        } else {
            $error = 'Aucun compte trouvé avec cet email.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Confiz</title>
    <link rel="stylesheet" href="autrecss.css">
    <link rel="stylesheet" href="login.css">
</head>

<body>
    <?php include 'header.php'; ?>
    
    <main>
        <section class="login-section">
            <div class="login-container">
                <div class="login-header">
                    <h1>Connexion</h1>
                    <p>Accédez à votre espace personnel</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form action="login.php" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required 
                               placeholder="Entrez votre email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Entrez votre mot de passe">
                    </div>
                    
                    <button type="submit" class="login-btn">Se connecter</button>
                </form>
                
                <div class="login-footer">
                    <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
                    <p><a href="forgot-password.php">Mot de passe oublié ?</a></p>
                </div>
                
                <!-- Comptes de test -->
                <div class="test-accounts">
                    <h3>Comptes de test :</h3>
                    <div class="test-account">
                        <strong>Super-gérant :</strong> alice22@example.com / 1234
                    </div>
                    <div class="test-account">
                        <strong>Gérant :</strong> chalie@example.com / 1234
                    </div>
                    <div class="test-account">
                        <strong>Client :</strong> bobdu35@example.com / 1234
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>