export function getTvaRate() {
    const companyTvaExemptEl = document.getElementById('companyTvaExempt');
    const isCompanyExempt = companyTvaExemptEl && (companyTvaExemptEl.value == '1' || companyTvaExemptEl.value === 'true');
    
    if (isCompanyExempt) {
        console.log('[DEBUG getTvaRate] Entreprise exonérée, taux = 0');
        return 0;
    }
    
    // Try to find the TVA rate element (select or hidden input)
    let tvaTypeEl = document.getElementById('tva_rate');
    const customTvaEl = document.getElementById('tva_custom_rate');
    
    // If no tva_rate found, try tva_rate_hidden
    if (!tvaTypeEl) {
        tvaTypeEl = document.getElementById('tva_rate_hidden');
    }
    
    if (!tvaTypeEl) {
        console.log('[DEBUG getTvaRate] Aucun élément tva_rate trouvé, taux par défaut = 20');
        return 20;
    }
    
    const tvaType = tvaTypeEl.value;
    if (tvaType === 'custom') {
        return parseFloat(customTvaEl?.value) || 0;
    }
    if (tvaType === '0') {
        return 0;
    }
    return parseFloat(tvaType) || 20;
}

export function updateTotals() {
    const rows = document.querySelectorAll('#itemsBody .item-row');
    console.log('[DEBUG updateTotals] Nombre de lignes:', rows.length);
    let totalHT = 0;
    
    rows.forEach(row => {
        const qtyInput = row.querySelector('input[name="item_quantity[]"]');
        const priceInput = row.querySelector('input[name="item_unit_price[]"]');
        
        const qty = parseFloat(qtyInput?.value) || 0;
        const price = parseFloat(priceInput?.value) || 0;
        totalHT += qty * price;
    });

    const tvaRate = getTvaRate();
    const totalTVA = tvaRate > 0 ? totalHT * tvaRate / 100 : 0;
    const totalTTC = totalHT + totalTVA;

    // Update visible elements
    const totalHTEl = document.getElementById('totalHT');
    const totalTVAEl = document.getElementById('totalTVA');
    const totalTTCEl = document.getElementById('totalTTC');
    const tvaLabelEl = document.getElementById('tvaLabel');
    const tvaMentionEl = document.getElementById('tvaMention');

    if (totalHTEl) {
        totalHTEl.textContent = totalHT.toFixed(2) + ' €';
    }
    if (totalTVAEl) {
        totalTVAEl.textContent = totalTVA.toFixed(2) + ' €';
    }
    if (totalTTCEl) {
        totalTTCEl.textContent = totalTTC.toFixed(2) + ' €';
    }

    // Update hidden inputs for PDF
    const pdfTotalHT = document.getElementById('pdf_totalHT');
    const pdfTotalTVA = document.getElementById('pdf_totalTVA');
    const pdfTotalTTC = document.getElementById('pdf_totalTTC');

    if (pdfTotalHT) pdfTotalHT.value = totalHT.toFixed(2);
    if (pdfTotalTVA) pdfTotalTVA.value = totalTVA.toFixed(2);
    if (pdfTotalTTC) pdfTotalTTC.value = totalTTC.toFixed(2);
    if (tvaLabelEl) tvaLabelEl.textContent = tvaRate + '%';

    if (tvaMentionEl) {
        if (tvaRate === 0) {
            tvaMentionEl.textContent = "TVA non applicable, art. 293B du CGI";
            tvaMentionEl.style.display = 'block';
        } else {
            tvaMentionEl.style.display = 'none';
        }
    }
}

export function initTvaEvents() {
    // Try to find the TVA rate element
    let tvaTypeEl = document.getElementById('tva_rate');
    if (!tvaTypeEl) {
        tvaTypeEl = document.getElementById('tva_rate_hidden');
    }
    
    const customTvaEl = document.getElementById('tva_custom_rate');
    const customTvaGroupEl = document.getElementById('customTvaGroup');

    if (tvaTypeEl && customTvaEl && tvaTypeEl.tagName === 'SELECT') {
        tvaTypeEl.addEventListener('change', function() {
            if (customTvaGroupEl) {
                customTvaGroupEl.style.display = this.value === 'custom' ? 'block' : 'none';
            }
            updateTotals();
        });
        
        customTvaEl.addEventListener('input', updateTotals);
    }

    const companyTvaExemptEl = document.getElementById('companyTvaExempt');
    if (companyTvaExemptEl) {
        const isCompanyExempt = companyTvaExemptEl.value == '1' || companyTvaExemptEl.value === 'true';
        
        if (isCompanyExempt && tvaTypeEl && customTvaEl && tvaTypeEl.tagName === 'SELECT') {
            tvaTypeEl.value = '0';
            tvaTypeEl.disabled = true;
            customTvaEl.value = '0';
            customTvaEl.disabled = true;
            if (customTvaGroupEl) customTvaGroupEl.style.display = 'none';
        }
    }
    
    updateTotals();
}
