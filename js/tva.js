export function getTvaRate() {
    const tvaType = document.getElementById('tvaType').value;
    if (tvaType === 'custom') {
        return parseFloat(document.getElementById('customTva').value) || 0;
    }
    return parseFloat(tvaType);
}

export function updateTotals() {
    // Calcule le total HT
    let totalHT = 0;
    document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
        const totalCell = row.querySelector('.item-total');
        if (totalCell) {
            totalHT += parseFloat(totalCell.textContent.replace(',', '.')) || 0;
        }
    });

    // Calcule la TVA et le TTC
    const tvaRate = getTvaRate();
    const totalTVA = tvaRate > 0 ? totalHT * tvaRate / 100 : 0;
    const totalTTC = totalHT + totalTVA;

    // Affiche les valeurs
    document.getElementById('totalHT').textContent = totalHT.toFixed(2);
    document.getElementById('totalTVA').textContent = totalTVA.toFixed(2);
    document.getElementById('totalTTC').textContent = totalTTC.toFixed(2);
    document.getElementById('tvaLabel').textContent = tvaRate + '%';

    // Mention légale si TVA non applicable
    const tvaMention = document.getElementById('tvaMention');
    if (tvaRate === 0) {
        tvaMention.textContent = "TVA non applicable, art. 293B du CGI";
    } else {
        tvaMention.textContent = "";
    }
}

export function initTvaEvents() {
    const tvaType = document.getElementById('tvaType');
    const customTva = document.getElementById('customTva');
    const customTvaGroup = document.getElementById('customTvaGroup');
    const tvaExempt = document.getElementById('companyTvaExempt');

    tvaType.addEventListener('change', function() {
        customTvaGroup.style.display = this.value === 'custom' ? 'block' : 'none';
        updateTotals();
    });
    customTva.addEventListener('input', updateTotals);

    if (tvaExempt) {
        tvaExempt.addEventListener('change', function() {
            if (this.checked) {
                tvaType.value = '0';
                tvaType.disabled = true;
                customTva.value = '0';
                customTva.disabled = true;
                customTvaGroup.style.display = 'none';
            } else {
                tvaType.disabled = false;
                customTva.disabled = false;
                // Optionnel : remettre le champ personnalisé visible si besoin
                if (tvaType.value === 'custom') {
                    customTvaGroup.style.display = 'block';
                }
            }
            updateTotals();
        });
        // Initialisation à l'ouverture
        if (tvaExempt.checked) {
            tvaType.value = '0';
            tvaType.disabled = true;
            customTva.value = '0';
            customTva.disabled = true;
            customTvaGroup.style.display = 'none';
        }
    }
}
