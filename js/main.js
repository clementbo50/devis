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

    document.getElementById('addRowBtn').addEventListener('click', initTable.addRow);
    document.getElementById('generatePdfBtn').addEventListener('click', async () => await generatePDF());

    initTvaEvents();

    updateTotals();
}

document.addEventListener('DOMContentLoaded', init);