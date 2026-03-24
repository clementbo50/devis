let logoBase64 = null;

export function initLogo() {
    const logoInput = document.getElementById('logoInput');
    const logo = document.getElementById('logo');

    if (logoInput) {
        logoInput.addEventListener('change', (event) => {
            if (event.target.files && event.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoBase64 = e.target.result;
                    if (logo) {
                        logo.src = logoBase64;
                        logo.style.display = 'block';
                    }
                };
                reader.readAsDataURL(event.target.files[0]);
            }
        });
    }

    const existingLogo = document.querySelector('#logo');
    if (existingLogo && existingLogo.src && existingLogo.src !== window.location.href) {
        logoBase64 = existingLogo.src;
    }

    const logoPathInput = document.getElementById('companyLogoPath');
    if (logoPathInput && logoPathInput.value && !logoBase64) {
        const logoPath = logoPathInput.value;
        fetch(logoPath)
            .then(response => response.blob())
            .then(blob => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoBase64 = e.target.result;
                };
                reader.readAsDataURL(blob);
            })
            .catch(err => {
                console.log('Impossible de charger le logo:', err);
            });
    }
}

export async function getLogoBase64() {
    if (logoBase64) {
        return logoBase64;
    }

    const logoPathInput = document.getElementById('companyLogoPath');
    if (logoPathInput && logoPathInput.value) {
        try {
            const response = await fetch(logoPathInput.value);
            const blob = await response.blob();
            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoBase64 = e.target.result;
                    resolve(logoBase64);
                };
                reader.readAsDataURL(blob);
            });
        } catch (err) {
            console.log('Impossible de charger le logo:', err);
        }
    }
    
    return null;
}
