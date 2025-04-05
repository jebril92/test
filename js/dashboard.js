
document.addEventListener('DOMContentLoaded', function() {
    initializeDataTable();
    setupEventListeners();
});


function initializeDataTable() {
    if($('#linksTable').length > 0) {
        $('#linksTable').DataTable({
            "language": {
                "lengthMenu": "Afficher _MENU_ entrées",
                "search": "Rechercher :",
                "zeroRecords": "Aucun résultat trouvé",
                "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                "infoEmpty": "Aucune entrée à afficher",
                "infoFiltered": "(filtré de _MAX_ entrées totales)",
                "paginate": {
                    "first": "Premier",
                    "last": "Dernier",
                    "next": "Suivant",
                    "previous": "Précédent"
                }
            },
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
            "dom": '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l<"ms-2"f>><"">>' +
                   'rt' +
                   '<"d-flex justify-content-between align-items-center mt-3"i<"">p>',
            "drawCallback": function(settings) {
                var pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');
                pagination.find('.paginate_button').addClass('btn btn-sm btn-outline-primary mx-1');
                pagination.find('.paginate_button.current').removeClass('btn-outline-primary').addClass('btn-primary active');
                pagination.find('.paginate_button.disabled').removeClass('btn-outline-primary').addClass('disabled');
            },
            order: [[2, 'desc']],
            responsive: true
        });
    }
}

function setupEventListeners() {
    const createLinkBtn = document.getElementById('createLinkBtn');
    if (createLinkBtn) {
        createLinkBtn.addEventListener('click', createNewLink);
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text)
        .then(() => {
            showToast('Lien copié !');
        })
        .catch(err => {
            console.error('Erreur lors de la copie : ', err);
            alert('Impossible de copier le lien. Veuillez le sélectionner et copier manuellement.');
        });
}

function copyNewLink() {
    const shortenedUrl = document.getElementById('shortened_url').value;
    copyToClipboard(shortenedUrl);
}

function showToast(message) {
    const toastContainer = document.createElement('div');
    toastContainer.style.position = 'fixed';
    toastContainer.style.bottom = '20px';
    toastContainer.style.right = '20px';
    toastContainer.style.zIndex = '9999';
    
    const toast = document.createElement('div');
    toast.className = 'toast show';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="toast-header">
            <strong class="me-auto">URLink</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close" onclick="this.parentElement.parentElement.parentElement.remove()"></button>
        </div>
        <div class="toast-body">
            ${message}
        </div>
    `;
    
    toastContainer.appendChild(toast);
    document.body.appendChild(toastContainer);
    
    setTimeout(() => {
        toastContainer.remove();
    }, 3000);
}

function createNewLink() {
    const form = document.getElementById('newLinkForm');
    const modalError = document.getElementById('modalError');
    modalError.style.display = 'none';
    
    const formData = new FormData(form);
    
    const originalUrl = formData.get('url');
    if (!originalUrl) {
        modalError.textContent = 'Veuillez entrer une URL valide';
        modalError.style.display = 'block';
        return;
    }
    
    console.log('Envoi des données : ', {
        url: formData.get('url'),
        custom_code: formData.get('custom_code'),
        expiry: formData.get('expiry')
    });
    
    const createBtn = document.getElementById('createLinkBtn');
    createBtn.disabled = true;
    createBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
    
    fetch('shorten_url.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Réponse brute reçue:', response);
        return response.json();
    })
    .then(data => {
        console.log('Données reçues:', data);
        
        createBtn.disabled = false;
        createBtn.innerHTML = 'Créer le lien';
        
        if (data.status === 'error') {
            modalError.textContent = data.message;
            modalError.style.display = 'block';
            return;
        }
        
        document.getElementById('newLinkForm').style.display = 'none';
        document.getElementById('newLinkResult').style.display = 'block';
        document.getElementById('shortened_url').value = data.short_url;
        
        createBtn.innerHTML = 'Créer un autre lien';
        createBtn.onclick = function() {
            document.getElementById('newLinkForm').reset();
            document.getElementById('newLinkForm').style.display = 'block';
            document.getElementById('newLinkResult').style.display = 'none';
            createBtn.innerHTML = 'Créer le lien';
            createBtn.onclick = createNewLink;
        };
        
        setTimeout(() => {
            window.location.reload();
        }, 3000);
    })
    .catch(error => {
        createBtn.disabled = false;
        createBtn.innerHTML = 'Créer le lien';
        
        console.error('Erreur:', error);
        modalError.textContent = 'Une erreur est survenue lors du raccourcissement de l\'URL. Veuillez réessayer.';
        modalError.style.display = 'block';
    });
}