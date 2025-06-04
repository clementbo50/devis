// Module pour gérer le chargement du logo
let logoBase64 = null; // Stocke la version base64 du logo

export function initLogo() {
    const logoInput = document.getElementById('logoInput');
    const logo = document.getElementById('logo');

    logoInput.addEventListener('change', (event) => {
        if (event.target.files && event.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                logoBase64 = e.target.result; // Stocke le base64
                logo.src = logoBase64;
                logo.style.display = 'block';
                window.logoBase64 = e.target.result; // dans le FileReader.onload
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    });
}

// Retourne la version base64 du logo pour le PDF
export async function getLogoBase64() {
    return logoBase64;
}