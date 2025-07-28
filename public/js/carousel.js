document.addEventListener('DOMContentLoaded', function() {
    const carouselElement = document.getElementById('heroCarousel');
    
    if (carouselElement) {
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

            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                const carousel = bootstrap.Carousel.getInstance(carouselElement);
                if (carousel) {
                    if (diffX > 0) {
                        carousel.next();
                    } else {
                        carousel.prev();
                    }
                }
            }

            startX = null;
            startY = null;
        });
    }

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

    const animatedElements = document.querySelectorAll('.stat-card, .promo-card, .news-card, .local-card');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
});