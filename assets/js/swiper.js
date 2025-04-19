document.addEventListener('DOMContentLoaded', function () {
    // Check if the slider element exists before initializing
    const sliderElement = document.querySelector('.featured-categories-slider');
    if (sliderElement) {
        const swiper = new Swiper('.featured-categories-slider', {
            // Optional parameters
            slidesPerView: 1,
            spaceBetween: 10,
            loop: true, // Enable looping
            autoplay: { // Enable autoplay
                delay: 3000, // Delay between transitions (in ms)
                disableOnInteraction: false, // Autoplay will not be disabled after user interactions (swipes)
                pauseOnMouseEnter: true, // Pause autoplay when mouse enters slider
            },
            // Responsive breakpoints
            breakpoints: {
                // when window width is >= 640px
                640: {
                    slidesPerView: 2,
                    spaceBetween: 20
                },
                // when window width is >= 1024px
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30
                }
            },
            // Navigation arrows
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            // Only show nav buttons if needed
            on: {
                init: function (swiperInstance) {
                    if (swiperInstance.isLocked) {
                        if(swiperInstance.navigation.nextEl) swiperInstance.navigation.nextEl.style.display = 'none';
                        if(swiperInstance.navigation.prevEl) swiperInstance.navigation.prevEl.style.display = 'none';
                    } else {
                         if(swiperInstance.navigation.nextEl) swiperInstance.navigation.nextEl.style.display = 'flex'; // Use flex to match CSS
                         if(swiperInstance.navigation.prevEl) swiperInstance.navigation.prevEl.style.display = 'flex'; // Use flex to match CSS
                    }
                },
                 resize: function (swiperInstance) {
                      if (swiperInstance.isLocked) {
                        if(swiperInstance.navigation.nextEl) swiperInstance.navigation.nextEl.style.display = 'none';
                        if(swiperInstance.navigation.prevEl) swiperInstance.navigation.prevEl.style.display = 'none';
                    } else {
                         if(swiperInstance.navigation.nextEl) swiperInstance.navigation.nextEl.style.display = 'flex'; // Use flex to match CSS
                         if(swiperInstance.navigation.prevEl) swiperInstance.navigation.prevEl.style.display = 'flex'; // Use flex to match CSS
                    }
                }
            }
        });
    } else {
        console.log('Featured categories slider not found on this page.');
    }
});
