// Point d'entrée de l'application
import { initLogo } from './logo.js';
import { initClient } from './client.js';
import { initTable } from './table.js';
import { generatePDF } from './pdf.js';
import { getTvaRate, updateTotals, initTvaEvents } from './tva.js';

// Initialisation des modules
async function init() {
    initLogo();
    initClient();
    initTable();

    // Attache les événements aux boutons
    document.getElementById('addRowBtn').addEventListener('click', initTable.addRow);
    document.getElementById('generatePdfBtn').addEventListener('click', async () => await generatePDF());

    // Initialise les événements TVA au chargement
    initTvaEvents();

    // Appelle updateTotals() après chaque modification du tableau ou des taux
    // Par exemple, après chaque ajout/suppression/modification de ligne :
    updateTotals();
}

// Lancez l'initialisation une fois le DOM chargé
document.addEventListener('DOMContentLoaded', init);