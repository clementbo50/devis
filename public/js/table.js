import { updateTotals } from './tva.js';

export function initTable() {
    const tableBody = document.getElementById('itemsBody');
    const addRowBtn = document.getElementById('addRowBtn');
    const toggleDateCheckbox = document.getElementById('toggle_date_field');

    if (!tableBody || !addRowBtn) return;

    function toggleDateFields(enabled) {
        const dateInputs = tableBody.querySelectorAll('input[name="item_date[]"]');
        dateInputs.forEach(input => {
            input.disabled = !enabled;
        });
    }

    function calculateTotal() {
        const rows = tableBody.querySelectorAll('.item-row');
        let grandTotal = 0;
        
        rows.forEach(row => {
            const qtyInput = row.querySelector('input[name="item_quantity[]"]');
            const priceInput = row.querySelector('input[name="item_unit_price[]"]');
            const totalCell = row.querySelector('.item-total');
            
            const qty = parseFloat(qtyInput?.value) || 0;
            const price = parseFloat(priceInput?.value) || 0;
            const lineTotal = qty * price;
            
            if (totalCell) {
                totalCell.textContent = lineTotal.toFixed(2) + ' €';
            }
            grandTotal += lineTotal;
        });

        const totalHT = document.getElementById('totalHT');
        if (totalHT) {
            totalHT.textContent = grandTotal.toFixed(2) + ' €';
        }

        if (typeof updateTotals === 'function') {
            updateTotals();
        }
    }

    function addRow() {
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td><input type="text" class="form-control" name="item_description[]" placeholder="Description" required></td>
            <td><input type="date" class="form-control" name="item_date[]"></td>
            <td><input type="number" class="form-control" name="item_quantity[]" value="1" min="0.01" step="0.01" required></td>
            <td><input type="text" class="form-control" name="item_unit[]" placeholder="Unité"></td>
            <td><input type="number" class="form-control" name="item_unit_price[]" value="0" min="0" step="0.01" required></td>
            <td class="text-end item-total">0.00 €</td>
            <td><button type="button" class="btn btn-sm btn-danger btn-delete-row"><i class="bi bi-trash"></i></button></td>
        `;
        
        const deleteBtn = row.querySelector('.btn-delete-row');
        deleteBtn.addEventListener('click', () => {
            row.remove();
            calculateTotal();
        });

        const qtyInput = row.querySelector('input[name="item_quantity[]"]');
        const priceInput = row.querySelector('input[name="item_unit_price[]"]');
        
        qtyInput.addEventListener('input', calculateTotal);
        priceInput.addEventListener('input', calculateTotal);

        if (toggleDateCheckbox && !toggleDateCheckbox.checked) {
            row.querySelector('input[name="item_date[]"]').disabled = true;
        }

        tableBody.appendChild(row);
        calculateTotal();
    }

    function attachRowListeners(row) {
        const qtyInput = row.querySelector('input[name="item_quantity[]"]');
        const priceInput = row.querySelector('input[name="item_unit_price[]"]');
        const deleteBtn = row.querySelector('.btn-delete-row');

        if (qtyInput) {
            qtyInput.addEventListener('input', calculateTotal);
        }
        if (priceInput) {
            priceInput.addEventListener('input', calculateTotal);
        }
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => {
                row.remove();
                calculateTotal();
            });
        }
    }

    tableBody.querySelectorAll('.item-row').forEach(attachRowListeners);
    addRowBtn.addEventListener('click', addRow);
    calculateTotal();

    if (toggleDateCheckbox) {
        toggleDateCheckbox.addEventListener('change', function() {
            toggleDateFields(this.checked);
        });
        toggleDateFields(toggleDateCheckbox.checked);
    }
}

export function getTableData(withDate = true) {
    const rows = document.querySelectorAll('#itemsBody .item-row');
    const data = [];

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        
        let description, itemDate, qty, unit, unitPrice, total;
        
        const descInput = row.querySelector('input[name="item_description[]"]');
        
        if (descInput) {
            const dateInput = row.querySelector('input[name="item_date[]"]');
            const qtyInput = row.querySelector('input[name="item_quantity[]"]');
            const unitInput = row.querySelector('input[name="item_unit[]"]');
            const priceInput = row.querySelector('input[name="item_unit_price[]"]');

            description = descInput?.value || '-';
            itemDate = dateInput?.value || '-';
            qty = parseFloat(qtyInput?.value) || 0;
            unit = unitInput?.value || '-';
            unitPrice = parseFloat(priceInput?.value) || 0;
        } else {
            description = cells[0]?.textContent?.trim() || '-';
            itemDate = cells[1]?.textContent?.trim() || '-';
            const qtyText = cells[2]?.textContent?.trim() || '0';
            unit = cells[3]?.textContent?.trim() || '-';
            const priceText = cells[4]?.textContent?.trim().replace(' €', '').replace(',', '.') || '0';
            const totalText = cells[5]?.textContent?.trim().replace(' €', '').replace(',', '.') || '0';
            
            qty = parseFloat(qtyText.replace(',', '.')) || 0;
            unitPrice = parseFloat(priceText) || 0;
            total = parseFloat(totalText) || 0;
        }
        
        if (!total) {
            total = qty * unitPrice;
        }

        if (withDate) {
            data.push([
                description,
                itemDate !== '-' ? itemDate : '-',
                qty.toFixed(2).replace('.', ','),
                unit,
                unitPrice.toFixed(2).replace('.', ',') + ' €',
                total.toFixed(2).replace('.', ',') + ' €'
            ]);
        } else {
            data.push([
                description,
                qty.toFixed(2).replace('.', ','),
                unit,
                unitPrice.toFixed(2).replace('.', ',') + ' €',
                total.toFixed(2).replace('.', ',') + ' €'
            ]);
        }
    });

    return data;
}
