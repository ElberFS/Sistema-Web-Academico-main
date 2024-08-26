document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const mainMenu = document.querySelector('.main-menu');
    const content = document.querySelector('.content');

    menuToggle.addEventListener('click', function() {
        mainMenu.classList.toggle('open');

        if (mainMenu.classList.contains('open')) {
            content.style.marginLeft = '220px'; // Ajusta según el ancho del menú expandido
        } else {
            content.style.marginLeft = '60px'; // Ajusta según el ancho del menú colapsado
        }
    });
});
