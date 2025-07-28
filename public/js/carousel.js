document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el carousel 
    const carouselElement = document.getElementById('heroCarousel');
    if (carouselElement) {
        const carousel = new bootstrap.Carousel(carouselElement, {
            interval: 5000, // 5 segundos entre slides
            pause: 'hover', // Pausar al hacer hover
            wrap: true,     // Volver al inicio después del ultimo slide
            keyboard: true  // Permitir navegacion con teclado
        });

        // Pausar el carousel cuando el mouse esta sobre el
        carouselElement.addEventListener('mouseenter', function() {
            carousel.pause();
        });

        // Reanudar el carousel cuando el mouse sale
        carouselElement.addEventListener('mouseleave', function() {
            carousel.cycle();
        });

        // Soporte para dispositivos moviles
        let startX = null;
        let startY = null;

        carouselElement.addEventListener('touchstart', function(e) {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        });

        carouselElement.addEventListener('touchend', function(e) {
            if (!startX || !startY) {
                return;
            }

            const endX = e.changedTouches[0].clientX;
            const endY = e.changedTouches[0].clientY;

            const diffX = startX - endX;
            const diffY = startY - endY;

            // Logica de swipes (tactil de celular)
            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                const carousel = bootstrap.Carousel.getInstance(carouselElement);
                if (diffX > 0) {
                    carousel.next();
                } else {
                    carousel.prev();
                }
            }

            startX = null;
            startY = null;
        });
    }

    // Smooth scroll para enlaces 
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                targetSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Animaciones para las cards
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Elementos para animaciones
    const animatedElements = document.querySelectorAll('.stat-card, .promo-card, .news-card, .local-card');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
});

// Funcion para centrar el carousel en pantallas pequeñas
function adjustCarouselHeight() {
    const carousel = document.getElementById('heroCarousel');
    if (carousel && window.innerWidth <= 768) {
        const windowHeight = window.innerHeight;
        carousel.style.height = windowHeight + 'px';
        
        const carouselItems = carousel.querySelectorAll('.carousel-item');
        carouselItems.forEach(item => {
            item.style.height = windowHeight + 'px';
        });
    }
}

// Ajustar altura al cargar y redimensionar
window.addEventListener('load', adjustCarouselHeight);
window.addEventListener('resize', adjustCarouselHeight);