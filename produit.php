<?php
require_once 'product-handler.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    header('Location: catalogue.php');
    exit;
}

$productData = getProductDetails($conn, $productId);

if (!$productData) {
    header('Location: catalogue.php');
    exit;
}

$bonbon = $productData['bonbon'];
$pageTitle = htmlspecialchars($bonbon['nom']) . ' - Confiz';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="autrecss.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <main>
        <?php echo renderProductPage($productData); ?>
    </main>

    <?php include 'footer.php'; ?>
    
    <script src="main.js"></script>
</body>
</html>