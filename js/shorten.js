document.addEventListener('DOMContentLoaded', function() {
    const shortenForm = document.getElementById('shorten-form');
    const linkResult = document.getElementById('link-result');
    const copyBtn = document.getElementById('copy-btn');
    const qrBtn = document.getElementById('qr-btn');
    const longUrlInput = document.getElementById('long-url');
    const shortUrlDisplay = document.getElementById('short-url');
    
    if (shortenForm) {
        shortenForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Récupérer l'URL longue
            const longUrl = longUrlInput.value.trim();
            
            // Vérifier que l'URL n'est pas vide
            if (!longUrl) {
                showMessage('Veuillez entrer une URL valide', 'error');
                return;
            }
            
            // Désactiver le bouton de soumission pendant le traitement
            const submitBtn = shortenForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
            
            // Préparer les données pour la requête AJAX
            const formData = new FormData();
            formData.append('url', longUrl);
            
            // Récupérer le code personnalisé et l'expiration si présents dans le formulaire
            const customCodeInput = document.getElementById('custom-code');
            const expirySelect = document.getElementById('expiry');
            
            if (customCodeInput) {
                formData.append('custom_code', customCodeInput.value.trim());
            }
            
            if (expirySelect) {
                formData.append('expiry', expirySelect.value);
            }
            
            // Envoyer la requête AJAX
            fetch('shorten_url.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Réactiver le bouton de soumission
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Raccourcir';
                
                if (data.status === 'error') {
                    showMessage(data.message, 'error');
                    return;
                }
                
                // Afficher l'URL raccourcie
                shortUrlDisplay.textContent = data.short_url;
                linkResult.style.display = 'block';
                
                // Ajouter les informations supplémentaires si présentes dans la UI
                const infoContainer = document.getElementById('link-info-container');
                if (infoContainer) {
                    const createdAtElement = document.getElementById('created-at');
                    const expiryElement = document.getElementById('expiry-datetime');
                    
                    if (createdAtElement) {
                        createdAtElement.textContent = formatDate(data.created_at);
                    }
                    
                    if (expiryElement && data.expiry) {
                        expiryElement.textContent = formatDate(data.expiry);
                        expiryElement.parentElement.style.display = 'block';
                    } else if (expiryElement) {
                        expiryElement.parentElement.style.display = 'none';
                    }
                }
                
                // Scroll jusqu'au résultat
                linkResult.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                
                // Enregistrer dans l'historique si l'élément existe
                if (typeof updateUrlHistory === 'function') {
                    updateUrlHistory(data);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Raccourcir';
                showMessage('Une erreur est survenue lors du raccourcissement de l\'URL. Veuillez réessayer.', 'error');
            });
        });
    }
    
    // Fonctionnalité du bouton de copie
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            const shortUrl = shortUrlDisplay.textContent;
            navigator.clipboard.writeText(shortUrl).then(function() {
                // Changer le texte du bouton temporairement
                const originalText = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fas fa-check me-1"></i> Copié!';
                
                setTimeout(function() {
                    copyBtn.innerHTML = originalText;
                }, 2000);
            }).catch(function() {
                alert('Impossible de copier le lien. Veuillez le sélectionner et le copier manuellement.');
            });
        });
    }
    
    // Fonctionnalité du bouton QR Code
    if (qrBtn) {
        qrBtn.addEventListener('click', function() {
            const shortUrl = shortUrlDisplay.textContent;
            const qrContainer = document.getElementById('qr-container');
            const qrCodeDiv = document.getElementById('qrcode');
            
            if (shortUrl) {
                // Basculer l'affichage du QR code
                if (qrContainer.style.display === 'none') {
                    // Nettoyer d'abord le contenu existant
                    qrCodeDiv.innerHTML = '';
                    
                    // Générer un nouveau QR code
                    new QRCode(qrCodeDiv, {
                        text: shortUrl,
                        width: 200,
                        height: 200,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                    
                    qrContainer.style.display = 'block';
                    qrBtn.innerHTML = '<i class="fas fa-times me-1"></i> Masquer QR';
                } else {
                    qrContainer.style.display = 'none';
                    qrBtn.innerHTML = '<i class="fas fa-qrcode me-1"></i> QR Code';
                }
            } else {
                console.error("URL courte non disponible");
            }
        });
    }
    
    /**
     * Affiche un message d'erreur ou de succès
     * 
     * @param {string} message Message à afficher
     * @param {string} type Type de message ('error' ou 'success')
     */
    function showMessage(message, type) {
        // Vérifier si le conteneur de message existe
        let messageContainer = document.getElementById('message-container');
        
        // Créer le conteneur s'il n'existe pas
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.id = 'message-container';
            messageContainer.className = 'mt-3';
            shortenForm.parentNode.insertBefore(messageContainer, linkResult);
        }
        
        // Définir la classe CSS en fonction du type de message
        const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
        
        // Créer le message
        messageContainer.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Faire disparaître le message après 5 secondes
        setTimeout(function() {
            const alertElement = messageContainer.querySelector('.alert');
            if (alertElement) {
                const bsAlert = new bootstrap.Alert(alertElement);
                bsAlert.close();
            }
        }, 5000);
    }
    
    /**
     * Formate une date au format lisible
     * 
     * @param {string} dateStr Date au format SQL
     * @return {string} Date formatée
     */
    function formatDate(dateStr) {
        if (!dateStr) return '';
        
        const date = new Date(dateStr);
        
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
});