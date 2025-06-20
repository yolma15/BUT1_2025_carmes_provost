// Gestion des boutiques
function voirBoutique(boutiqueId) {
    console.log('Chargement de la boutique ID:', boutiqueId);
    
    // Masquer la grille des boutiques
    document.querySelector('.boutiques-container').style.display = 'none';
    document.querySelector('.boutiques-header').style.display = 'none';
    
    // Afficher la section des détails
    const detailsSection = document.getElementById('boutique-details');
    detailsSection.style.display = 'block';
    
    // Afficher un indicateur de chargement
    const boutiqueContent = document.getElementById('boutique-content');
    boutiqueContent.innerHTML = '<div class="loading-boutique">Chargement des détails de la boutique...</div>';
    
    // Faire défiler vers le haut
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
    
    // Charger les détails de la boutique via AJAX
    fetch(`boutique-details.php?id=${boutiqueId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                boutiqueContent.innerHTML = `<div class="error-boutique">${data.error}</div>`;
                return;
            }
            
            // Construire le HTML des détails
            let produitsHtml = '';
            if (data.produits && data.produits.length > 0) {
                produitsHtml = `
                    <div class="produits-section">
                        <h2 class="produits-title">Produits disponibles (${data.produits.length})</h2>
                        <div class="produits-grid">
                            ${data.produits.map(produit => `
                                <div class="produit-boutique-card">
                                    <div class="produit-boutique-image">
                                        <img src="${produit.illustration || './img/default.jpg'}" 
                                             alt="${produit.nom}"
                                             onerror="this.src='./img/default.jpg'">
                                    </div>
                                    <div class="produit-boutique-info">
                                        <h3 class="produit-boutique-name">${produit.nom}</h3>
                                        <span class="produit-boutique-type">${produit.type}</span>
                                        <p class="produit-boutique-price">${parseFloat(produit.prix).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} €</p>
                                        <div class="produit-boutique-stock ${
                                            produit.quantite <= 0 ? 'stock-epuise' : 
                                            produit.quantite < 10 ? 'stock-limite' : 
                                            'stock-disponible'
                                        }">
                                            ${
                                                produit.quantite <= 0 ? 'Épuisé' : 
                                                produit.quantite < 10 ? `Stock limité: ${produit.quantite}` : 
                                                `En stock: ${produit.quantite}`
                                            }
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            } else {
                produitsHtml = `
                    <div class="no-products">
                        <h2>Aucun produit disponible</h2>
                        <p>Cette boutique n'a actuellement aucun produit en stock.</p>
                    </div>
                `;
            }
            
            // Mettre à jour le contenu
            boutiqueContent.innerHTML = `
                <div class="boutique-detail-header">
                    <h1 class="boutique-detail-name">${data.boutique.nom}</h1>
                    <div class="boutique-detail-address">
                        ${data.boutique.numero_rue} ${data.boutique.nom_adresse}<br>
                        ${data.boutique.code_postal} ${data.boutique.ville}<br>
                        ${data.boutique.pays}
                    </div>
                </div>
                ${produitsHtml}
            `;
            
            // Mettre à jour l'URL sans recharger la page
            history.pushState({boutiqueId: boutiqueId}, data.boutique.nom, `boutiques.php?boutique=${boutiqueId}`);
            
            // Mettre à jour le titre de la page
            document.title = `${data.boutique.nom} - Confiz`;
        })
        .catch(error => {
            console.error('Erreur lors du chargement des détails:', error);
            boutiqueContent.innerHTML = '<div class="error-boutique">Une erreur est survenue lors du chargement des détails de la boutique.</div>';
        });
}

function retourBoutiques() {
    // Masquer la section des détails
    document.getElementById('boutique-details').style.display = 'none';
    
    // Afficher la grille des boutiques
    document.querySelector('.boutiques-container').style.display = 'block';
    document.querySelector('.boutiques-header').style.display = 'block';
    
    // Mettre à jour l'URL
    history.pushState(null, 'Nos Boutiques - Confiz', 'boutiques.php');
    
    // Mettre à jour le titre
    document.title = 'Nos Boutiques - Confiz';
    
    // Faire défiler vers le haut
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Gérer la navigation avec les boutons précédent/suivant
window.addEventListener('popstate', function(event) {
    if (event.state && event.state.boutiqueId) {
        voirBoutique(event.state.boutiqueId);
    } else {
        retourBoutiques();
    }
});

// Charger une boutique spécifique si l'URL le demande
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const boutiqueId = urlParams.get('boutique');
    
    if (boutiqueId) {
        voirBoutique(boutiqueId);
    }
});