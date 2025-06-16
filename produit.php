<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root"; // à modifier selon votre configuration
$password = ""; // à modifier selon votre configuration
$dbname = "confiz";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur de connexion: ' . $e->getMessage()]);
        exit;
    }
    die("Erreur de connexion: " . $e->getMessage());
}

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID de produit non valide']);
        exit;
    }
    header('Location: catalogue.php');
    exit;
}

$id = $_GET['id'];

// Récupérer les détails du bonbon
$stmt = $conn->prepare("SELECT * FROM confiseries WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Produit non trouvé']);
        exit;
    }
    header('Location: catalogue.php');
    exit;
}

$bonbon = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les stocks disponibles dans les différentes boutiques
$stmt = $conn->prepare("
    SELECT s.quantite, b.nom as boutique_nom, b.ville, b.id as boutique_id
    FROM stocks s
    JOIN boutiques b ON s.boutique_id = b.id
    WHERE s.confiserie_id = :id
");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si c'est une requête AJAX, renvoyer les données au format JSON
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode([
        'bonbon' => $bonbon,
        'stocks' => $stocks
    ]);
    exit;
}

// Si ce n'est pas une requête AJAX, inclure le template HTML
include 'produit-template.php';
?>