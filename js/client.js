// Module pour gérer les champs client
export function initClient() {
    const clientTypeSelect = document.getElementById('clientType');

    // Toggle les champs spécifiques selon le type de client
    function togglePublicFields() {
        const clientType = clientTypeSelect.value;
        const publicFields = document.querySelectorAll('.public-field');
        publicFields.forEach(field => {
            field.style.display = (clientType === 'public' || clientType === 'entreprise') ? 'block' : 'none';
        });
    }

    // Initialisation de l'événement
    clientTypeSelect.addEventListener('change', togglePublicFields);
    togglePublicFields(); // Appel initial
}

// Récupère les données client pour le PDF
export function getClientData() {
    const clientType = document.getElementById('clientType').value;
    const data = {
        type: clientType || 'N/A',
        name: document.getElementById('clientName').value || 'N/A',
        address: document.getElementById('clientAddress').value.split('\n').join(', ') || 'N/A'
    };

    if (clientType !== 'particulier') {
        data.siret = document.getElementById('clientSiret').value || 'N/A';
        data.codeApe = document.getElementById('clientCodeApe').value || 'N/A';
        if (clientType === 'public') {
            data.service = document.getElementById('clientService').value || 'N/A';
        }
    }
    return data;
}