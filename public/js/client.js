export function initClient() {
    const clientSelector = document.getElementById('client_selector');
    const clientIdInput = document.getElementById('client_id');

    if (clientSelector && clientIdInput) {
        // Pre-fill hidden fields if a client is already selected (edit mode)
        const selectedOption = clientSelector.options[clientSelector.selectedIndex];
        if (selectedOption && selectedOption.value && selectedOption.dataset.name) {
            fillClientFields(selectedOption);
        }

        clientSelector.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value && selectedOption && selectedOption.dataset.name) {
                if (clientIdInput) {
                    clientIdInput.value = this.value;
                }
                fillClientFields(selectedOption);
            } else {
                if (clientIdInput) {
                    clientIdInput.value = '';
                }
                clearClientFields();
            }
        });
    }
}

function fillClientFields(option) {
    const name = option.dataset.name || '';
    const type = option.dataset.type || 'particulier';
    const address = option.dataset.address || '';
    const siret = option.dataset.siret || '';
    const codeApe = option.dataset.codeApe || '';
    const service = option.dataset.service || '';

    const fields = {
        'client_name': name,
        'client_type': type,
        'client_address': address,
        'client_siret': siret,
        'client_code_ape': codeApe,
        'client_service': service
    };

    for (const [id, value] of Object.entries(fields)) {
        const el = document.getElementById(id);
        if (el) {
            el.value = value;
        }
    }
}

function clearClientFields() {
    const fields = ['client_name', 'client_type', 'client_address', 'client_siret', 'client_code_ape', 'client_service'];
    fields.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.value = '';
        }
    });
}

export function getClientData() {
    const clientIdEl = document.getElementById('client_id');
    const clientTypeEl = document.getElementById('client_type');
    const clientNameEl = document.getElementById('client_name');
    const clientAddressEl = document.getElementById('client_address');
    const clientSiretEl = document.getElementById('client_siret');
    const clientCodeApeEl = document.getElementById('client_code_ape');
    const clientServiceEl = document.getElementById('client_service');

    const clientId = clientIdEl ? clientIdEl.value : null;
    const clientType = clientTypeEl ? clientTypeEl.value : 'particulier';
    
    const data = {
        id: clientId,
        type: clientType || 'N/A',
        name: clientNameEl ? clientNameEl.value : 'N/A',
        address: clientAddressEl ? clientAddressEl.value.replace(/\n/g, ', ') : 'N/A'
    };

    if (clientType !== 'particulier') {
        data.siret = clientSiretEl ? clientSiretEl.value : '';
        data.codeApe = clientCodeApeEl ? clientCodeApeEl.value : '';
        if (clientType === 'public') {
            data.service = clientServiceEl ? clientServiceEl.value : '';
        }
    }

    return data;
}
