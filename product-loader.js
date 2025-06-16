// Encapsulons tout dans une IIFE pour éviter les conflits
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Product loader script initialized'); // Pour déboguer
        
        // Fonction pour charger les détails d'un produit
        function loadProductDetails(productId) {
            console.log('Loading product details for ID:', productId); // Pour déboguer
            
            // Afficher un indicateur de chargement
            const mainElement = document.querySelector('main');
            if (mainElement) {
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'loading';
                loadingDiv.textContent = 'Chargement des détails du produit...';
                mainElement.innerHTML = '';
                mainElement.appendChild(loadingDiv);
            }
            
            // Faire une requête AJAX pour récupérer les détails du produit
            fetch(`produit.php?id=${productId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Response received:', response.status); // Pour déboguer
                return response.json();
            })
            .then(data => {
                console.log('Data received:', data); // Pour déboguer
                
                if (data.error) {
                    mainElement.innerHTML = `<div class="error-message">${data.error}</div>`;
                    return;
                }
                
                // Mettre à jour le titre de la page
                document.title = `${data.bonbon.nom} - Confiz`;
                
                // Construire le HTML pour les détails du produit
                let stocksHtml = '';
                if (data.stocks && data.stocks.length > 0) {
                    stocksHtml = `
                        <div class="stock-list">
                            ${data.stocks.map(stock => `
                                <div class="stock-item">
                                    <p class="stock-boutique">${stock.boutique_nom}</p>
                                    <p class="stock-ville">${stock.ville}</p>
                                    <p class="stock-quantite ${
                                        stock.quantite <= 0 ? 'stock-epuise' : 
                                        stock.quantite < 10 ? 'stock-limite' : 
                                        'stock-disponible'
                                    }">
                                        ${
                                            stock.quantite <= 0 ? 'Épuisé' : 
                                            stock.quantite < 10 ? `Stock limité: ${stock.quantite} restants` : 
                                            `En stock: ${stock.quantite} disponibles`
                                        }
                                    </p>
                                </div>
                            `).join('')}
                        </div>
                    `;
                } else {
                    stocksHtml = '<p>Ce produit n\'est actuellement disponible dans aucune boutique.</p>';
                }
                
                // Créer le contenu HTML
                const productHTML = `
                    <div class="product-container">
                        <a href="catalogue.php" class="back-button">← Retour au catalogue</a>
                        
                        <div class="product-details">
                            <div class="product-image-container">
                                <img 
                                    src="${data.bonbon.illustration || 'images/candies/default.jpg'}" 
                                    alt="${data.bonbon.nom}" 
                                    class="product-image"
                                >
                            </div>
                            
                            <div class="product-info">
                                <h1 class="product-name">${data.bonbon.nom}</h1>
                                <span class="product-type">${data.bonbon.type}</span>
                                <p class="product-price">${parseFloat(data.bonbon.prix).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} €</p>
                                <div class="product-description">
                                    ${data.bonbon.description.replace(/\n/g, '<br>')}
                                </div>
                                
                                <div class="stock-info">
                                    <h2 class="stock-title">Disponibilité en boutique</h2>
                                    ${stocksHtml}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Mettre à jour le contenu de la page
                mainElement.innerHTML = productHTML;
                
                // Mettre à jour l'URL sans recharger la page
                history.pushState({productId: productId}, data.bonbon.nom, `produit.php?id=${productId}`);
            })
            .catch(error => {
                console.error('Erreur lors du chargement des détails du produit:', error);
                if (mainElement) {
                    mainElement.innerHTML = '<div class="error-message">Une erreur est survenue lors du chargement des détails du produit.</div>';
                }
            });
        }
        
        // Intercepter les clics sur les liens vers les pages de produits
        document.addEventListener('click', function(event) {
            // Vérifier si le clic est sur un lien vers une page de produit
            const productLink = event.target.closest('a[href^="produit.php?id="]');
            
            if (productLink) {
                console.log('Product link clicked:', productLink.href); // Pour déboguer
                event.preventDefault();
                
                // Extraire l'ID du produit de l'URL
                const url = new URL(productLink.href, window.location.origin);
                const productId = url.searchParams.get('id');
                
                if (productId) {
                    loadProductDetails(productId);
                    
                    // Faire défiler vers le haut de la page
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            }
        });
        
        // Gérer la navigation avec les boutons précédent/suivant du navigateur
        window.addEventListener('popstate', function(event) {
            console.log('Popstate event:', event.state); // Pour déboguer
            if (event.state && event.state.productId) {
                loadProductDetails(event.state.productId);
            } else {
                // Si on revient à une page sans état, recharger la page
                window.location.reload();
            }
        });
        
        // Charger les détails du produit si on est sur une page de produit
        if (window.location.pathname.includes('produit.php')) {
            const urlParams = new URLSearchParams(window.location.search);
            const productId = urlParams.get('id');
            
            if (productId) {
                console.log('Initial product ID detected:', productId); // Pour déboguer
                // Sauvegarder l'état initial dans l'historique
                history.replaceState({productId: productId}, document.title, window.location.href);
            }
        }
    });
})();