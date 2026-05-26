(function () {
    var nav = document.querySelector('.landing-nav');

    function atualizarNav() {
        if (!nav) {
            return;
        }

        if (window.scrollY > 10) {
            nav.classList.add('is-scrolled');
        } else {
            nav.classList.remove('is-scrolled');
        }
    }

    atualizarNav();
    window.addEventListener('scroll', atualizarNav, { passive: true });

    document.querySelectorAll('a[href^="#"]').forEach(function (link) {
        link.addEventListener('click', function (evento) {
            var destino = document.querySelector(link.getAttribute('href'));

            if (!destino) {
                return;
            }

            evento.preventDefault();
            destino.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    document.querySelectorAll('form[novalidate]').forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            if (!form.checkValidity()) {
                evento.preventDefault();
                evento.stopPropagation();
            }

            form.classList.add('was-validated');
        });
    });

    var inputBanner = document.querySelector('#imagem_banner');
    var previewBanner = document.querySelector('.banner-preview');

    if (inputBanner && previewBanner) {
        inputBanner.addEventListener('change', function () {
            var arquivo = inputBanner.files && inputBanner.files[0];

            if (!arquivo || !arquivo.type.match(/^image\/(jpeg|png|webp)$/)) {
                return;
            }

            previewBanner.src = URL.createObjectURL(arquivo);
        });
    }
}());
