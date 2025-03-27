// assets/gatita-bakes-slider.js

document.addEventListener('DOMContentLoaded', function () {
  // Check if the Swiper container exists on the page
  if (document.querySelector('.gatita-product-slider')) {
    const swiper = new Swiper('.gatita-product-slider', {
      // Optional parameters
      // How many slides to show at once
      slidesPerView: 1.2, // Show 1 full and part of the next on mobile
      spaceBetween: 15, // Space between slides

      // Responsive breakpoints
      breakpoints: {
        // when window width is >= 640px
        640: {
          slidesPerView: 2.3, // Show 2 and part of the third
          spaceBetween: 20
        },
        // when window width is >= 820px (matches our column layout breakpoint)
        820: {
          slidesPerView: 2.5, // Show 2 and a half slides in the main content column
          spaceBetween: 25
        },
        // when window width is >= 1024px
        1024: {
          slidesPerView: 3, // Show 3 slides on wider screens
          spaceBetween: 30
        }
      },

      // Add navigation buttons
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },

      // Add pagination bullets (optional)
      // pagination: {
      //   el: '.swiper-pagination',
      //   clickable: true,
      // },

       // Make slides loop (optional)
       // loop: true,

       // Grab cursor effect (optional)
       grabCursor: true,

    });
  }
});