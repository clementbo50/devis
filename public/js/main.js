import { initLogo } from './logo.js';
import { initClient } from './client.js';
import { initTable } from './table.js';
import { generatePDF } from './pdf-generator.js';
import { updateTotals, initTvaEvents, getTvaRate } from './tva.js';

async function init() {
    console.log('[DEBUG] Initialisation du générateur de devis...');
    
    initLogo();
    console.log('[DEBUG] Logo initialisé');
    
    initClient();
    console.log('[DEBUG] Client initialisé');
    
    initTable();
    console.log('[DEBUG] Tableau initialisé');
    
    initTvaEvents();
    console.log('[DEBUG] TVA initialisé, taux:', getTvaRate());

    const generatePdfBtn = document.getElementById('generatePdfBtn');
    console.log('[DEBUG] Bouton PDF trouvé:', !!generatePdfBtn);

    if (generatePdfBtn) {
        generatePdfBtn.addEventListener('click', async () => {
            console.log('[DEBUG] Génération PDF déclenchée');
            try {
                await generatePDF();
                console.log('[DEBUG] PDF généré avec succès');
            } catch (error) {
                console.error('Erreur génération PDF:', error);
                alert('Erreur lors de la génération du PDF. Veuillez vérifier la console.');
            }
        });
    }

    updateTotals();
    console.log('[DEBUG] Totaux mis à jour');
}

document.addEventListener('DOMContentLoaded', init);
