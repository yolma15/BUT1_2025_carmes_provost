<?php
require_once 'boutique-handler.php';

$boutiqueId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($boutiqueId <= 0) {
    header('Location: boutiques.php');
    exit;
}

$boutiqueData = getBoutiqueDetails($conn, $boutiqueId);

if (!$boutiqueData) {
    header('Location: boutiques.php');
    exit;
}

$boutique = $boutiqueData['boutique'];
$pageTitle = htmlspecialchars($boutique['nom']) . ' - Confiz';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="autrecss.css">
    <link rel="stylesheet" href="boutiques.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <main>
        <section class="boutique-details">
            <div class="details-container">
                <a href="boutiques.php" class="back-to-boutiques">← Retour aux boutiques</a>
                <div id="boutique-content">
                    <?php echo renderBoutiqueDetails($boutiqueData); ?>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
    
    <script src="main.js"></script>
</body>
</html>