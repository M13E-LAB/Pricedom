// Fix pour le menu mobile
document.addEventListener('DOMContentLoaded', function() {
    // Fermer le menu mobile au chargement
    const mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenu) {
        mobileMenu.classList.remove('active');
    }
    
    // Ajouter un bouton de fermeture d'urgence
    const emergencyClose = document.createElement('button');
    emergencyClose.innerHTML = '✕ Fermer le menu';
    emergencyClose.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        background: #f97316;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        display: none;
    `;
    
    emergencyClose.onclick = function() {
        if (mobileMenu) {
            mobileMenu.classList.remove('active');
        }
        emergencyClose.style.display = 'none';
    };
    
    document.body.appendChild(emergencyClose);
    
    // Montrer le bouton d'urgence si le menu est ouvert
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.target.classList.contains('active')) {
                emergencyClose.style.display = 'block';
            } else {
                emergencyClose.style.display = 'none';
            }
        });
    });
    
    if (mobileMenu) {
        observer.observe(mobileMenu, { attributes: true, attributeFilter: ['class'] });
    }
});