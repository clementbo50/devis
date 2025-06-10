// Module de génération du PDF
import { getLogoBase64 } from './logo.js';
import { getClientData } from './client.js';
import { getTableData } from './table.js';

export async function generatePDF() {

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ unit: 'mm', format: 'a4' });
    const pageWidth = doc.internal.pageSize.getWidth();
    const margin = 10;
    let y = margin;

    // Ajout du logo avec base64 et proportions préservées
    const logoBase64 = await getLogoBase64();
    if (logoBase64) {
        // Dimensions du logo
        const maxWidth = 50;
        const maxHeight = 30;
        // On crée une image temporaire pour calculer le ratio
        const img = new window.Image();
        img.src = logoBase64;
        await new Promise((resolve) => { img.onload = resolve; });
        const imgWidth = img.width;
        const imgHeight = img.height;
        const ratio = Math.min(maxWidth / imgWidth, maxHeight / imgHeight);
        const logoWidth = imgWidth * ratio;
        const logoHeight = imgHeight * ratio;
        doc.addImage(logoBase64, 'PNG', margin, y, logoWidth, logoHeight);
        y += logoHeight + 5;
    } else {
        y += 20;
    }

    // En-tête
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(20);
    doc.setTextColor(26, 60, 109);
    doc.text('Devis', pageWidth - margin - 30, y);
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.setTextColor(0, 0, 0);
    doc.text(`Numéro: ${document.getElementById('quoteNumber').value}`, pageWidth - margin - 50, y + 7);
    
    // Formatage des dates
    const quoteDate = formatDateToFrench(document.getElementById('quoteDate').value);
    const quoteValidUntil = formatDateToFrench(document.getElementById('quoteValidUntil').value);

    doc.text(`Date d'émission: ${quoteDate}`, pageWidth - margin - 50, y + 12);
    doc.text(`Valide jusqu'au: ${quoteValidUntil}`, pageWidth - margin - 50, y + 17);
    y += 25;

    // Info entreprise (à gauche)
    const companyInfoX = margin;
    doc.setFontSize(14);
    doc.setTextColor(26, 60, 109);
    //doc.text('Émetteur', companyInfoX, y);
    doc.setLineWidth(0.5);
    doc.line(companyInfoX, y + 2, companyInfoX + 60, y + 2);
    y += 7;
    doc.setFontSize(10);
    doc.setTextColor(0, 0, 0);

    const companyName = document.getElementById('companyName').value;
    const companyAddress = document.getElementById('companyAddress').value.split('\n').join(', ');
    const companySiret = document.getElementById('companySiret').value;
    const companyEmail = document.getElementById('companyEmail').value;
    const companyLegalStatus = document.getElementById('companyLegalStatus').value;
    const companyLastname = document.getElementById('companyLastname').value;
    const companyFirstname = document.getElementById('companyFirstname').value;
    const companyTva = document.getElementById('companyTva').value;
    const companyTvaExempt = document.getElementById('companyTvaExempt').checked;
    const companyPhone = document.getElementById('companyPhone').value;

    if (companyLegalStatus) {
        doc.text(`Statut : ${companyLegalStatus}`, companyInfoX, y);
        y += 7;
    }
    if (companyName) {
        doc.text(companyName, companyInfoX, y);
        y += 7;
    }
    if (companyLastname || companyFirstname) {
        doc.text(`Représentant : ${companyFirstname} ${companyLastname}`.trim(), companyInfoX, y);
        y += 7;
    }
    if (companyAddress) {
        doc.text(companyAddress, companyInfoX, y, { maxWidth: 80 });
        y += Math.ceil(companyAddress.length / 50) * 5 + 7;
    }
    if (companySiret) {
        doc.text(`SIRET: ${companySiret}`, companyInfoX, y);
        y += 7;
    }
    
    if (companyTva) {
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
    y += 8; // espace avant la section client

    // Info client (à droite)
    const clientData = getClientData();
    const clientInfoX = pageWidth - margin - 80; // Alignement à droite avec 80mm de largeur max
    doc.setFontSize(14);
    doc.setTextColor(26, 60, 109);
    //doc.text('Pour', clientInfoX, y);
    doc.line(clientInfoX, y + 2, clientInfoX + 60, y + 2);
    y += 7;
    doc.setFontSize(10);
    doc.setTextColor(0, 0, 0);
    if (clientData.name && clientData.name !== 'N/A') {
        doc.text(`${clientData.name}`, clientInfoX, y);
        y += 7;
    }
    if (clientData.address && clientData.address !== 'N/A') {
        doc.text(`${clientData.address}`, clientInfoX, y, { maxWidth: 80 });
        y += Math.ceil(clientData.address.length / 50) * 5 + 7;
    }
    if (clientData.siret && clientData.siret !== 'N/A') {
        doc.text(`${clientData.siret}`, clientInfoX, y);
        y += 7;
    }
    if (clientData.codeApe && clientData.codeApe !== 'N/A') {
        doc.text(`${clientData.codeApe}`, clientInfoX, y);
        y += 7;
    }
    if (clientData.service && clientData.service !== 'N/A') {
        doc.text(`${clientData.service}`, clientInfoX, y);
        y += 7;
    }
    y += 10;

    // Note avant le tableau
    const note = document.getElementById('note').value;
    if (note) {
        // Fond plus sombre pour la section objet
        const noteBgColor = [240, 244, 250]; // bleu très clair
        const noteBoxHeight = 14 + Math.ceil(note.length / 80) * 5; // hauteur dynamique selon le texte
        doc.setFillColor(...noteBgColor);
        doc.rect(margin, y - 2, pageWidth - 2 * margin, noteBoxHeight, 'F');
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(12);
        doc.setTextColor(26, 60, 109);
        doc.text('Objet', margin + 2, y + 4);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        doc.text(note, margin + 2, y + 9, { maxWidth: pageWidth - 2 * margin - 4 });
        y += noteBoxHeight + 2;
    }

    // Tableau (couvre toute la largeur)
    const availableWidth = pageWidth - 2 * margin; // 190mm
    const dateFieldEnabled = document.getElementById('toggleDateField').checked;
    let tableHead, tableBody, columnStyles;
    if (dateFieldEnabled) {
        tableHead = [['Description', 'Date', 'Qté', 'Unité', 'Prix unitaire', 'Total']];
        tableBody = getTableData(true);
        columnStyles = {
            0: { cellWidth: availableWidth * 0.35 }, // Description: 35%
            1: { cellWidth: availableWidth * 0.15 }, // Date: 15%
            2: { cellWidth: availableWidth * 0.10 }, // Qté: 10%
            3: { cellWidth: availableWidth * 0.15 }, // Unité: 15%
            4: { cellWidth: availableWidth * 0.15 }, // Prix unitaire: 15%
            5: { cellWidth: availableWidth * 0.10 }  // Total: 10%
        };
    } else {
        tableHead = [['Description', 'Qté', 'Unité', 'Prix unitaire', 'Total']];
        tableBody = getTableData(false);
        columnStyles = {
            0: { cellWidth: availableWidth * 0.40 }, // Description: 40%
            1: { cellWidth: availableWidth * 0.13 }, // Qté: 13%
            2: { cellWidth: availableWidth * 0.17 }, // Unité: 17%
            3: { cellWidth: availableWidth * 0.17 }, // Prix unitaire: 17%
            4: { cellWidth: availableWidth * 0.13 }  // Total: 13%
        };
    }
    doc.autoTable({
        startY: y,
        head: tableHead,
        body: tableBody,
        theme: 'grid',
        headStyles: {
            fillColor: [26, 60, 109],
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
            cellPadding: 1,
        },
        margin: { top: margin, left: margin, right: margin },
        didDrawPage: (data) => {
            // Footer
            drawFooter(doc, pageWidth, margin);
        },
    });

    y = doc.lastAutoTable.finalY + 10;

    // Après le tableau
    const tvaRate = (() => {
        const tvaType = document.getElementById('tvaType').value;
        if (tvaType === 'custom') {
            return parseFloat(document.getElementById('customTva').value) || 0;
        }
        return parseFloat(tvaType);
    })();
    const totalHT = parseFloat(document.getElementById('totalHT').textContent) || 0;
    const totalTVA = parseFloat(document.getElementById('totalTVA').textContent) || 0;
    const totalTTC = parseFloat(document.getElementById('totalTTC').textContent) || 0;

    // Bloc résultat (carte bleue arrondie) - initialisation AVANT le calcul de la hauteur totale
    const resultBoxWidth = 60;
    const resultBoxHeight = companyTvaExempt ? 14 : 24;
    const resultBoxX = pageWidth - margin - resultBoxWidth;
    const conditions = document.getElementById('conditions').value;

    // Calcul de la hauteur totale nécessaire pour le bloc final (résultat, mention, conditions, signature)
    const pageHeight = doc.internal.pageSize.getHeight();
    let totalBlockHeight = resultBoxHeight + 8;
    if (companyTvaExempt) totalBlockHeight += 10; // mention légale
    let conditionsBoxHeight = 0;
    if (conditions) {
        conditionsBoxHeight = 14 + Math.ceil(conditions.length / 80) * 5 + 6;
        totalBlockHeight += conditionsBoxHeight;
    }
    totalBlockHeight += 30 + 14; // espace + signature
    if (y + totalBlockHeight > pageHeight - margin) {
        doc.addPage();
        y = margin;
        drawFooter(doc, pageWidth, margin);
    }

    // Bloc résultat (carte bleue carrée)
    const resultBoxY = y;
    doc.setFillColor(230, 238, 255); // bleu très clair
    doc.rect(resultBoxX, resultBoxY, resultBoxWidth, resultBoxHeight, 'F');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.setTextColor(26, 60, 109);
    let resultY = resultBoxY + 6;
    if (companyTvaExempt) {
        doc.text(`Total HT : ${totalHT.toFixed(2)} €`, resultBoxX + resultBoxWidth / 2, resultY, { align: 'center' });
        resultY += 8;
    } else {
        doc.text(`Total HT : ${totalHT.toFixed(2)} €`, resultBoxX + resultBoxWidth / 2, resultY, { align: 'center' });
        resultY += 8;
        doc.text(`TVA (${tvaRate}%) : ${totalTVA.toFixed(2)} €`, resultBoxX + resultBoxWidth / 2, resultY, { align: 'center' });
        resultY += 8;
        doc.text(`Total TTC : ${totalTTC.toFixed(2)} €`, resultBoxX + resultBoxWidth / 2, resultY, { align: 'center' });
        resultY += 8;
    }
    y += resultBoxHeight + 4;

    if (companyTvaExempt) {
        doc.setFont('helvetica', 'italic');
        doc.setFontSize(10);
        doc.setTextColor(80, 80, 80);
        doc.text("TVA non applicable, art. 293B du CGI", resultBoxX + resultBoxWidth, y, { align: 'right' });
        y += 10;
    }

    // Conditions
    if (conditions) {
        const noteBgColor = [240, 244, 250]; // bleu très clair
        doc.setFillColor(...noteBgColor);
        doc.rect(margin, y - 2, pageWidth - 2 * margin, conditionsBoxHeight, 'F');
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(12);
        doc.setTextColor(26, 60, 109);
        doc.text('Modalités et remarques', margin + 2, y + 4);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        doc.text(conditions, margin + 2, y + 10, { maxWidth: pageWidth - 2 * margin - 4 });
        y += conditionsBoxHeight;
    }

    // Zone de signature (fin du PDF)
    y += 30; // grand espace avant la zone de signature
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(11);
    doc.setTextColor(30, 30, 40);
    const leftX = margin;
    const rightX = pageWidth / 2 + 10;
    const signatureText = "Signature du client (précédé de la mention « Bon pour accord »)";
    doc.text(signatureText, rightX, y, { maxWidth: pageWidth / 2 - margin - 10 });
    doc.text("Date de signature :", leftX, y);

    doc.save(`devis-${document.getElementById('quoteNumber').value || 'document'}.pdf`);
}

// Fonction utilitaire pour dessiner le footer sur chaque page
function drawFooter(doc, pageWidth, margin) {
    doc.setFontSize(8);
    doc.setTextColor(100);
    doc.text(`${document.getElementById('companyLegalStatus').value} | ${document.getElementById('companyName').value || 'N/A'} | N° Siret : ${document.getElementById('companySiret').value || 'N/A'}`,
        margin,
        doc.internal.pageSize.getHeight() - 10
    );
}

function formatDateToFrench(dateString) {
    const date = new Date(dateString);
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return date.toLocaleDateString('fr-FR', options);
}