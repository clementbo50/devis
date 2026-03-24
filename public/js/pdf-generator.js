import { getLogoBase64 } from './logo.js';
import { getClientData } from './client.js';
import { getTableData } from './table.js';
import { formatDateToFrench } from './date.js';

const { jsPDF } = window.jspdf;

const COLORS = {
    primary: [26, 53, 87],
    secondary: [69, 123, 157],
    light: [168, 218, 220],
    veryLight: [241, 250, 238],
    danger: [230, 57, 70],
    gray: [150, 150, 150]
};

function getElementValue(id) {
    const el = document.getElementById(id);
    return el ? el.value : '';
}

function getElementText(id) {
    const el = document.getElementById(id);
    return el ? el.textContent : '';
}

function getHiddenValue(id) {
    const el = document.querySelector(`input[type="hidden"][id="${id}"]`);
    return el ? el.value : '';
}

function getCheckboxValue(id) {
    const el = document.getElementById(id);
    if (!el) return false;
    if (el.type === 'checkbox') {
        return el.checked;
    }
    return el.value == '1' || el.value === 'true';
}

function drawFooter(doc, pageWidth, margin) {
    const companyName = getElementValue('companyName') || 'N/A';
    const companySiret = getElementValue('companySiret') || 'N/A';
    const companyLegalStatus = getElementValue('companyLegalStatus') || 'N/A';
    
    doc.setFontSize(8);
    doc.setTextColor(100);
    doc.text(
        `${companyLegalStatus} | ${companyName} | N° Siret : ${companySiret}`,
        margin,
        doc.internal.pageSize.getHeight() - 10
    );
}

export async function generatePDF() {
    const doc = new jsPDF({ unit: 'mm', format: 'a4' });
    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const margin = 10;
    let y = margin;

    const logoBase64 = await getLogoBase64();
    if (logoBase64) {
        const maxWidth = 50;
        const maxHeight = 30;
        const img = new window.Image();
        img.src = logoBase64;
        await new Promise((resolve) => { img.onload = resolve; });
        const ratio = Math.min(maxWidth / img.width, maxHeight / img.height);
        const logoWidth = img.width * ratio;
        const logoHeight = img.height * ratio;
        doc.addImage(logoBase64, 'PNG', margin, y, logoWidth, logoHeight);
        y += logoHeight + 5;
    } else {
        y += 20;
    }

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(20);
    doc.setTextColor(...COLORS.primary);
    doc.text('Devis', pageWidth - margin - 30, y);
    
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.setTextColor(0, 0, 0);
    
    const quoteNumber = getElementValue('pdf_quote_number');
    const quoteDate = formatDateToFrench(getElementValue('pdf_quote_date'));
    const validUntil = formatDateToFrench(getElementValue('pdf_valid_until'));
    
    doc.text(`Numéro: ${quoteNumber}`, pageWidth - margin - 50, y + 7);
    doc.text(`Date d'émission: ${quoteDate}`, pageWidth - margin - 50, y + 12);
    doc.text(`Valide jusqu'au: ${validUntil}`, pageWidth - margin - 50, y + 17);
    y += 25;

    const companyInfoX = margin;
    doc.setFontSize(14);
    doc.setTextColor(...COLORS.primary);
    doc.line(companyInfoX, y + 2, companyInfoX + 60, y + 2);
    y += 7;
    doc.setFontSize(10);
    doc.setTextColor(0, 0, 0);

    const companyLegalStatus = getElementValue('companyLegalStatus');
    const companyName = getElementValue('companyName');
    const companyFirstname = getElementValue('companyFirstname');
    const companyLastname = getElementValue('companyLastname');
    const companyAddress = getElementValue('companyAddress');
    const companySiret = getElementValue('companySiret');
    const companyTva = getElementValue('companyTva');
    const companyEmail = getElementValue('companyEmail');
    const companyPhone = getElementValue('companyPhone');
    const companyTvaExempt = getCheckboxValue('companyTvaExempt');

    if (companyLegalStatus) {
        doc.text(`Statut : ${companyLegalStatus}`, companyInfoX, y);
        y += 7;
    }
    if (companyName) {
        doc.text(companyName, companyInfoX, y);
        y += 7;
    }
    if (companyFirstname || companyLastname) {
        doc.text(`Représentant : ${companyFirstname} ${companyLastname}`.trim(), companyInfoX, y);
        y += 7;
    }
    if (companyAddress) {
        doc.text(companyAddress.replace(/\n/g, ', '), companyInfoX, y, { maxWidth: 80 });
        y += Math.ceil(companyAddress.length / 50) * 5 + 7;
    }
    if (companySiret) {
        doc.text(`SIRET: ${companySiret}`, companyInfoX, y);
        y += 7;
    }
    if (companyTva && !companyTvaExempt) {
        doc.text(`TVA : ${companyTva}`, companyInfoX, y);
        y += 7;
    }
    if (companyEmail) {
        doc.text(companyEmail, companyInfoX, y);
        y += 7;
    }
    if (companyPhone) {
        doc.text(`Tél : ${companyPhone}`, companyInfoX, y);
        y += 7;
    }
    y += 8;

    const clientData = getClientData();
    const clientInfoX = pageWidth - margin - 80;
    doc.setFontSize(14);
    doc.setTextColor(...COLORS.primary);
    doc.line(clientInfoX, y + 2, clientInfoX + 60, y + 2);
    y += 7;
    doc.setFontSize(10);
    doc.setTextColor(0, 0, 0);
    
    if (clientData.name && clientData.name !== 'N/A') {
        doc.text(clientData.name, clientInfoX, y);
        y += 7;
    }
    if (clientData.address && clientData.address !== 'N/A') {
        doc.text(clientData.address.replace(/\n/g, ', '), clientInfoX, y, { maxWidth: 80 });
        y += Math.ceil(clientData.address.length / 50) * 5 + 7;
    }
    if (clientData.siret && clientData.siret !== 'N/A') {
        doc.text(clientData.siret, clientInfoX, y);
        y += 7;
    }
    if (clientData.codeApe && clientData.codeApe !== 'N/A') {
        doc.text(`Code APE: ${clientData.codeApe}`, clientInfoX, y);
        y += 7;
    }
    if (clientData.service && clientData.service !== 'N/A') {
        doc.text(`Service: ${clientData.service}`, clientInfoX, y);
        y += 7;
    }
    y += 10;

    const note = getElementValue('pdf_note');
    if (note) {
        const noteBgColor = [240, 244, 250];
        const noteBoxHeight = 14 + Math.ceil(note.length / 80) * 5;
        doc.setFillColor(...noteBgColor);
        doc.rect(margin, y - 2, pageWidth - 2 * margin, noteBoxHeight, 'F');
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(12);
        doc.setTextColor(...COLORS.primary);
        doc.text('Objet', margin + 2, y + 4);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        doc.text(note, margin + 2, y + 9, { maxWidth: pageWidth - 2 * margin - 4 });
        y += noteBoxHeight + 2;
    }

    const availableWidth = pageWidth - 2 * margin;
    const dateFieldEl = document.getElementById('toggle_date_field') || document.getElementById('pdf_toggle_date_field');
    const dateFieldEnabled = dateFieldEl ? (dateFieldEl.type === 'checkbox' ? dateFieldEl.checked : dateFieldEl.value === '1') : true;
    
    let tableHead, tableBody, columnStyles;
    
    if (dateFieldEnabled) {
        tableHead = [['Description', 'Date', 'Qté', 'Unité', 'Prix unitaire', 'Total']];
        tableBody = getTableData(true);
        columnStyles = {
            0: { cellWidth: availableWidth * 0.35 },
            1: { cellWidth: availableWidth * 0.15 },
            2: { cellWidth: availableWidth * 0.10 },
            3: { cellWidth: availableWidth * 0.15 },
            4: { cellWidth: availableWidth * 0.15 },
            5: { cellWidth: availableWidth * 0.10 }
        };
    } else {
        tableHead = [['Description', 'Qté', 'Unité', 'Prix unitaire', 'Total']];
        tableBody = getTableData(false);
        columnStyles = {
            0: { cellWidth: availableWidth * 0.40 },
            1: { cellWidth: availableWidth * 0.13 },
            2: { cellWidth: availableWidth * 0.17 },
            3: { cellWidth: availableWidth * 0.17 },
            4: { cellWidth: availableWidth * 0.13 }
        };
    }
    
    doc.autoTable({
        startY: y,
        head: tableHead,
        body: tableBody,
        theme: 'grid',
        headStyles: {
            fillColor: COLORS.primary,
            textColor: [255, 255, 255],
            fontSize: 10,
            font: 'helvetica',
            fontStyle: 'bold',
        },
        bodyStyles: {
            fontSize: 9,
            font: 'helvetica',
            textColor: [0, 0, 0],
        },
        columnStyles: columnStyles,
        styles: {
            overflow: 'linebreak',
            cellPadding: 2,
        },
        margin: { top: margin, left: margin, right: margin },
        didDrawPage: (data) => {
            drawFooter(doc, pageWidth, margin);
        },
    });

    y = doc.lastAutoTable.finalY + 10;

    const tvaTypeEl = document.getElementById('tva_rate') || document.querySelector('select[id="tva_rate"]');
    const tvaType = tvaTypeEl ? tvaTypeEl.value : '20';
    const customTvaEl = document.getElementById('tva_custom_rate');
    const customTva = customTvaEl ? customTvaEl.value : '0';
    const tvaRate = tvaType === 'custom' ? parseFloat(customTva) || 0 : parseFloat(tvaType);
    
    const totalHT = parseFloat(getHiddenValue('pdf_totalHT')) || 0;
    const totalTVA = parseFloat(getHiddenValue('pdf_totalTVA')) || 0;
    const totalTTC = parseFloat(getHiddenValue('pdf_totalTTC')) || 0;

    const resultBoxWidth = 60;
    const resultBoxHeight = companyTvaExempt ? 14 : 24;
    const resultBoxX = pageWidth - margin - resultBoxWidth;
    const conditions = getElementValue('pdf_conditions');

    let totalBlockHeight = resultBoxHeight + 8;
    if (companyTvaExempt) totalBlockHeight += 10;
    
    let conditionsBoxHeight = 0;
    if (conditions) {
        conditionsBoxHeight = 14 + Math.ceil(conditions.length / 80) * 5 + 6;
        totalBlockHeight += conditionsBoxHeight;
    }
    totalBlockHeight += 30 + 14;
    
    if (y + totalBlockHeight > pageHeight - margin) {
        doc.addPage();
        y = margin;
        drawFooter(doc, pageWidth, margin);
    }

    const resultBoxY = y;
    doc.setFillColor(230, 238, 255);
    doc.rect(resultBoxX, resultBoxY, resultBoxWidth, resultBoxHeight, 'F');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.setTextColor(...COLORS.primary);
    
    let resultY = resultBoxY + 6;
    doc.text(`Total HT : ${totalHT.toFixed(2)} €`, resultBoxX + resultBoxWidth / 2, resultY, { align: 'center' });
    
    if (!companyTvaExempt && tvaRate > 0) {
        resultY += 8;
        doc.text(`TVA (${tvaRate}%) : ${totalTVA.toFixed(2)} €`, resultBoxX + resultBoxWidth / 2, resultY, { align: 'center' });
        resultY += 8;
        doc.text(`Total TTC : ${totalTTC.toFixed(2)} €`, resultBoxX + resultBoxWidth / 2, resultY, { align: 'center' });
    }
    y += resultBoxHeight + 4;

    if (companyTvaExempt) {
        doc.setFont('helvetica', 'italic');
        doc.setFontSize(10);
        doc.setTextColor(80, 80, 80);
        doc.text("TVA non applicable, art. 293B du CGI", resultBoxX + resultBoxWidth, y, { align: 'right' });
        y += 10;
    }

    if (conditions) {
        const noteBgColor = [240, 244, 250];
        doc.setFillColor(...noteBgColor);
        doc.rect(margin, y - 2, pageWidth - 2 * margin, conditionsBoxHeight, 'F');
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(12);
        doc.setTextColor(...COLORS.primary);
        doc.text('Modalités et remarques', margin + 2, y + 4);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        doc.text(conditions, margin + 2, y + 10, { maxWidth: pageWidth - 2 * margin - 4 });
        y += conditionsBoxHeight;
    }

    y += 30;
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(11);
    doc.setTextColor(30, 30, 40);
    
    const leftX = margin;
    const rightX = pageWidth / 2 + 10;
    const signatureText = "Signature du client (précédé de la mention « Bon pour accord »)";
    
    doc.text(signatureText, rightX, y, { maxWidth: pageWidth / 2 - margin - 10 });
    doc.text("Date de signature :", leftX, y);

    const filename = quoteNumber ? `devis-${quoteNumber}.pdf` : 'devis.pdf';
    doc.save(filename);
}
