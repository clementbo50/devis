// Module pour gérer le tableau des prestations
import { updateTotals } from './tva.js';

export function initTable() {
    const tableBody = document.getElementById('itemsTable').getElementsByTagName('tbody')[0];
    const addRowBtn = document.getElementById('addRowBtn');
    const toggleDateCheckbox = document.getElementById('toggleDateField');

    // Active/désactive tous les champs date
    function toggleDateFields(enabled) {
        const dateInputs = tableBody.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            input.disabled = !enabled;
        });
    }

    // Ajoute une nouvelle ligne au tableau
    function addRow() {
        const row = tableBody.insertRow();
        row.innerHTML = `
            <td><input type="text" class="item-description required" placeholder="Description" required></td>
            <td><input type="date" class="required" required></td>
            <td><input type="number" class="item-qty required" min="1" value="1" required></td>
            <td><input type="text" class="required" placeholder="Unité" required></td>
            <td><input type="number" class="item-amount required" min="0" step="0.01" value="0" required></td>
            <td class="item-total">0.00</td>
            <td><button class="btn btn-delete">Supprimer</button></td>
        `;
        attachRowListeners(row);
        // Désactive le champ date si la case n'est pas cochée
        if (toggleDateCheckbox && !toggleDateCheckbox.checked) {
            row.querySelector('input[type="date"]').disabled = true;
        }
        calculateTotal();
    }

    // Supprime une ligne
    function deleteRow(event) {
        event.target.closest('tr').remove();
        calculateTotal();
    }

    // Calcule les totaux
    function calculateTotal() {
        const rows = tableBody.querySelectorAll('tr');
        let grandTotal = 0;
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const amount = parseFloat(row.querySelector('.item-amount').value) || 0;
            const lineTotal = qty * amount;
            row.querySelector('.item-total').textContent = lineTotal.toFixed(2);
            grandTotal += lineTotal;
        });
        document.getElementById('totalHT').textContent = grandTotal.toFixed(2);
        document.getElementById('totalTVA').textContent = (grandTotal * 0.2).toFixed(2);
        document.getElementById('totalTTC').textContent = (grandTotal * 1.2).toFixed(2);
        updateTotals();
    }

    // Attache les écouteurs d'événements à une ligne
    function attachRowListeners(row) {
        row.querySelector('.item-qty').addEventListener('input', calculateTotal);
        row.querySelector('.item-amount').addEventListener('input', calculateTotal);
        row.querySelector('.btn-delete').addEventListener('click', deleteRow);
    }

    // Initialisation des écouteurs
    tableBody.querySelectorAll('tr').forEach(attachRowListeners);
    addRowBtn.addEventListener('click', addRow);
    calculateTotal();

    // Gestion de la case à cocher pour activer/désactiver le champ date
    if (toggleDateCheckbox) {
        toggleDateCheckbox.addEventListener('change', function() {
            toggleDateFields(this.checked);
        });
        // Applique l'état initial
        toggleDateFields(toggleDateCheckbox.checked);
    }
}

// Récupère les données du tableau pour le PDF
export function getTableData(withDate = true) {
    const rows = document.querySelectorAll('#itemsTable tbody tr');
    const data = [];
    rows.forEach(row => {
        const cells = row.querySelectorAll('input');
        const qty = parseFloat(cells[2].value) || 0;
        const amount = parseFloat(cells[4].value) || 0;
        if (withDate) {
            data.push([
                cells[0].value || 'N/A',
                cells[1].value || 'N/A',
                cells[2].value || '0',
                cells[3].value || 'N/A',
                cells[4].value || '0.00',
                (qty * amount).toFixed(2)
            ]);
        } else {
            data.push([
                cells[0].value || 'N/A',
                cells[2].value || '0',
                cells[3].value || 'N/A',
                cells[4].value || '0.00',
                (qty * amount).toFixed(2)
            ]);
        }
    });
    return data;
}