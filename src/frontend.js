document.addEventListener('DOMContentLoaded', function () {
    const sliders = document.querySelectorAll('.wp-block-my-plugin-testimonial-slider');

    sliders.forEach(slider => {
        const slidesPerView = parseInt(slider.dataset.slidesperview, 10) || 1;
        const loop = slider.dataset.loop === 'true';
        const autoplay = slider.dataset.autoplay === 'true';
        const breakpoints = JSON.parse(slider.dataset.breakpoints || '{}');
        const delay = parseInt(slider.dataset.delay, 10) || 2500;
        const spaceBetween = parseInt(slider.dataset.spacing, 10) || 30;

        const swiperConfig = {
            slidesPerView,
            loop,
            autoplay: autoplay ? { delay, disableOnInteraction: false } : false,
            spaceBetween,
            breakpoints,
        };

        const paginationEl = slider.querySelector('.swiper-pagination');
        if (paginationEl) {
            swiperConfig.pagination = {
                el: paginationEl,
                clickable: true,
            };
        }

        const prevEl = slider.querySelector('.swiper-button-prev');
        const nextEl = slider.querySelector('.swiper-button-next');
        if (prevEl && nextEl) {
            swiperConfig.navigation = {
                nextEl,
                prevEl,
            };
        }

        const swiper = new Swiper(slider.querySelector('.swiper'), swiperConfig);

        // Pause on hover, resume on leave
        const container = slider.querySelector('.swiper');
        container.addEventListener('mouseenter', () => {
            if (swiper.params.autoplay && swiper.autoplay) { swiper.autoplay.stop(); }
        });
        container.addEventListener('mouseleave', () => {
            if (swiper.params.autoplay && swiper.autoplay) { swiper.autoplay.start(); }
        });
    });
});
