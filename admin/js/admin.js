/**
 * URLink Admin JavaScript
 * Scripts pour le tableau de bord d'administration
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialisation des popovers Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Animation pour les cartes de statistiques
    const statsCards = document.querySelectorAll('.stats-card');
    statsCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Fonctionnalité de recherche dans les tables
    const searchInputs = document.querySelectorAll('.table-search-input');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableId = this.getAttribute('data-table');
            const table = document.getElementById(tableId);
            
            if (table) {
                const tbody = table.querySelector('tbody');
                const rows = tbody.querySelectorAll('tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.indexOf(searchTerm) > -1) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    });

    // Gestion des formulaires de confirmation pour les actions critiques
    const confirmForms = document.querySelectorAll('.confirm-action');
    confirmForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const confirmMessage = this.getAttribute('data-confirm') || 'Êtes-vous sûr de vouloir effectuer cette action?';
            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });
    });

    // Initialisation des graphiques si Charts.js est chargé et que l'élément existe
    if (typeof Chart !== 'undefined') {
        // Graphique des clics par jour si l'élément existe
        const clicksChartElement = document.getElementById('clicksChart');
        if (clicksChartElement) {
            initClicksChart(clicksChartElement);
        }
        
        // Graphique des inscriptions par jour si l'élément existe
        const registrationsChartElement = document.getElementById('registrationsChart');
        if (registrationsChartElement) {
            initRegistrationsChart(registrationsChartElement);
        }
    }

    // Exemple de fonction pour initialiser un graphique de clics
    function initClicksChart(canvas) {
        // Ces données devraient idéalement venir du backend
        const data = {
            labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            datasets: [{
                label: 'Clics',
                data: [65, 59, 80, 81, 56, 55, 40],
                backgroundColor: 'rgba(67, 97, 238, 0.2)',
                borderColor: 'rgba(67, 97, 238, 1)',
                borderWidth: 1,
                tension: 0.4
            }]
        };
        
        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Clics par jour'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            },
        };
        
        new Chart(canvas, config);
    }

    // Exemple de fonction pour initialiser un graphique d'inscriptions
    function initRegistrationsChart(canvas) {
        // Ces données devraient idéalement venir du backend
        const data = {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
            datasets: [{
                label: 'Inscriptions',
                data: [12, 19, 3, 5, 2, 3],
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        };
        
        const config = {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Inscriptions par mois'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            },
        };
        
        new Chart(canvas, config);
    }
});

// Fonction pour copier du texte dans le presse-papiers
function copyToClipboard(text) {
    navigator.clipboard.writeText(text)
        .then(() => {
            // Afficher une notification de succès
            showNotification('Copié dans le presse-papiers !', 'success');
        })
        .catch(err => {
            console.error('Erreur lors de la copie :', err);
            // Afficher une notification d'erreur
            showNotification('Erreur lors de la copie. Veuillez réessayer.', 'danger');
        });
}

// Fonction pour afficher une notification
function showNotification(message, type = 'info') {
    const container = document.createElement('div');
    container.className = 'position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '1050';
    
    const toast = document.createElement('div');
    toast.className = `toast bg-${type} text-white`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="toast-header bg-${type} text-white">
            <strong class="me-auto">URLink Admin</strong>
            <small>à l'instant</small>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            ${message}
        </div>
    `;
    
    container.appendChild(toast);
    document.body.appendChild(container);
    
    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: 3000
    });
    
    bsToast.show();
    
    // Supprimer l'élément après la fermeture
    toast.addEventListener('hidden.bs.toast', function () {
        container.remove();
    });
}