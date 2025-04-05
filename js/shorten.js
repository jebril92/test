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
            
            const longUrl = longUrlInput.value.trim();
            
            if (!longUrl) {
                showMessage('Veuillez entrer une URL valide', 'error');
                return;
            }
            
            const submitBtn = shortenForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
            
            const formData = new FormData();
            formData.append('url', longUrl);
            
            const customCodeInput = document.getElementById('custom-code');
            const expirySelect = document.getElementById('expiry');
            
            if (customCodeInput) {
                formData.append('custom_code', customCodeInput.value.trim());
            }
            
            if (expirySelect) {
                formData.append('expiry', expirySelect.value);
            }
            
            fetch('shorten_url.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Raccourcir';
                
                if (data.status === 'error') {
                    showMessage(data.message, 'error');
                    return;
                }
                
                shortUrlDisplay.textContent = data.short_url;
                linkResult.style.display = 'block';
                
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
                
                linkResult.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                
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
    
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            const shortUrl = shortUrlDisplay.textContent;
            navigator.clipboard.writeText(shortUrl).then(function() {
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
    
    if (qrBtn) {
        qrBtn.addEventListener('click', function() {
            const shortUrl = shortUrlDisplay.textContent;
            const qrContainer = document.getElementById('qr-container');
            const qrCodeDiv = document.getElementById('qrcode');
            
            if (shortUrl) {
                if (qrContainer.style.display === 'none') {
                    qrCodeDiv.innerHTML = '';
                    
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
    
    function showMessage(message, type) {
        let messageContainer = document.getElementById('message-container');
        
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.id = 'message-container';
            messageContainer.className = 'mt-3';
            shortenForm.parentNode.insertBefore(messageContainer, linkResult);
        }
        
        const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
        
        messageContainer.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        setTimeout(function() {
            const alertElement = messageContainer.querySelector('.alert');
            if (alertElement) {
                const bsAlert = new bootstrap.Alert(alertElement);
                bsAlert.close();
            }
        }, 5000);
    }
    
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