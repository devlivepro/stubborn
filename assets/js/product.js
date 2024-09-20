document.addEventListener('DOMContentLoaded', function () {
    checkStock();
});

function checkStock() {
    // Récupérer l'élément sélectionné dans le menu déroulant
    const sizeSelect = document.getElementById('size');
    const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];
    const stock = parseInt(selectedOption.getAttribute('data-stock'), 10);
    
    // Désactiver ou activer le bouton Ajouter au panier en fonction du stock
    const addToCartButton = document.getElementById('btn-add-to-cart');
    if (stock === 0) {
        addToCartButton.disabled = true;
    } else {
        addToCartButton.disabled = false;
    }
}

document.getElementById('size').addEventListener('change', function () {
    checkStock();
});