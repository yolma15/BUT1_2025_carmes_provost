<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour vérifier le rôle de l'utilisateur
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Fonction pour vérifier si l'utilisateur a l'un des rôles spécifiés
function hasAnyRole($roles) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    return in_array($_SESSION['role'], $roles);
}

// Fonction pour rediriger si non autorisé
function requireAuth($allowedRoles = []) {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
    
    if (!empty($allowedRoles) && !hasAnyRole($allowedRoles)) {
        header('Location: ../index.php?error=access_denied');
        exit;
    }
}

// Fonction pour obtenir les informations de l'utilisateur connecté
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'],
        'prenom' => $_SESSION['prenom'],
        'nom' => $_SESSION['nom']
    ];
}

// Fonction de déconnexion
function logout() {
    session_destroy();
    header('Location: ../login.php');
    exit;
}
?>