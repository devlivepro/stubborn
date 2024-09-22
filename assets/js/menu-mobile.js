document.addEventListener('DOMContentLoaded', function() {
    var burgerIcon = document.getElementById('menu-mobile');
    var nav = document.getElementById('main-nav');

    if (burgerIcon) {
        burgerIcon.addEventListener('click', function() {
            nav.classList.toggle('nav-active'); // Bascule la classe pour le menu
            burgerIcon.classList.toggle('nav-active'); // Bascule la classe pour le bouton burger
        });
    }
});