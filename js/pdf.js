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
    doc.text(`Date d'émission: ${document.getElementById('quoteDate').value}`, pageWidth - margin - 50, y + 12);
    doc.text(`Valide jusqu'au: ${document.getElementById('quoteValidUntil').value}`, pageWidth - margin - 50, y + 17);
    y += 25;

    // Info entreprise (à gauche)
    const companyInfoX = margin;
    doc.setFontSize(14);
    doc.setTextColor(26, 60, 109);
    doc.text('Émetteur', companyInfoX, y);
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
    if (companyTvaExempt) {
        doc.text('Exonéré de TVA', companyInfoX, y);
        y += 7;
    } else if (companyTva) {
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
    doc.text('Pour', clientInfoX, y);
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

    // Tableau (couvre toute la largeur)
    const availableWidth = pageWidth - 2 * margin; // 190mm
    doc.autoTable({
        startY: y,
        head: [['Description', 'Date', 'Qté', 'Unité', 'Prix unitaire', 'Total']],
        body: getTableData(),
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
        columnStyles: {
            0: { cellWidth: availableWidth * 0.35 }, // Description: 35%
            1: { cellWidth: availableWidth * 0.15 }, // Date: 15%
            2: { cellWidth: availableWidth * 0.10 }, // Qté: 10%
            3: { cellWidth: availableWidth * 0.15 }, // Unité: 15%
            4: { cellWidth: availableWidth * 0.15 }, // Prix unitaire: 15%
            5: { cellWidth: availableWidth * 0.10 }  // Total: 10%
        },
        styles: {
            overflow: 'linebreak',
            cellPadding: 1,
        },
        margin: { top: margin, left: margin, right: margin },
        didDrawPage: (data) => {
            // Footer
            const pageHeight = doc.internal.pageSize.getHeight();
            doc.setFontSize(8);
            doc.setTextColor(100);
            doc.text(`${document.getElementById('companyName').value || 'N/A'} | ${document.getElementById('companySiret').value || 'N/A'}`, data.settings.margin.left, pageHeight - 10);
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

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.setTextColor(26, 60, 109);
    doc.text(`Total HT : ${totalHT.toFixed(2)} €`, pageWidth - margin, y, { align: 'right' });
    y += 7;
    doc.text(`TVA (${tvaRate}%) : ${totalTVA.toFixed(2)} €`, pageWidth - margin, y, { align: 'right' });
    y += 7;
    doc.text(`Total TTC : ${totalTTC.toFixed(2)} €`, pageWidth - margin, y, { align: 'right' });
    y += 10;

    if (tvaRate === 0) {
        doc.setFont('helvetica', 'italic');
        doc.setFontSize(10);
        doc.setTextColor(80, 80, 80);
        doc.text("TVA non applicable, art. 293B du CGI", pageWidth - margin, y, { align: 'right' });
        y += 7;
    }

    // Conditions
    const conditions = document.getElementById('conditions').value;
    if (conditions) {
        doc.setDrawColor(212, 160, 23); // Couleur or
        doc.setLineWidth(0.5);
        doc.rect(margin, y - 2, pageWidth - 2 * margin, 15, 'S'); // Encadré
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(12);
        doc.setTextColor(26, 60, 109);
        doc.text('Conditions générales et notes', margin + 2, y + 4);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        doc.text(conditions, margin + 2, y + 10, { maxWidth: pageWidth - 2 * margin - 4 });
    }

   

    doc.save(`devis-${document.getElementById('quoteNumber').value || 'document'}.pdf`);
}
