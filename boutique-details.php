<?php
header('Content-Type: application/json');

// Connexion à la base de données
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "confiz";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Erreur de connexion: ' . $e->getMessage()]);
    exit;
}

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'ID de boutique non valide']);
    exit;
}

$id = $_GET['id'];

// Récupérer les détails de la boutique
$stmt = $conn->prepare("SELECT * FROM boutiques WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    echo json_encode(['error' => 'Boutique non trouvée']);
    exit;
}

$boutique = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les produits disponibles dans cette boutique
$stmt = $conn->prepare("
    SELECT c.*, s.quantite 
    FROM confiseries c
    JOIN stocks s ON c.id = s.confiserie_id
    WHERE s.boutique_id = :boutique_id
    ORDER BY c.nom ASC
");
$stmt->bindParam(':boutique_id', $id, PDO::PARAM_INT);
$stmt->execute();
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retourner les données au format JSON
echo json_encode([
    'boutique' => $boutique,
    'produits' => $produits
]);
?>