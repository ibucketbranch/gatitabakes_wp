/**
 ** Plugin Name:       Gatita Bakes Ordering       **
 * Filename:          gatita-bakes-slider.js
 * Plugin URI:        https://www.gatitabakes.com/
 * Description:       Product slider and form field visibility handler
 * Version:           1.9.1 // "Restored slider with quantity controls"
 * Author:            Bucketbranch
 * Author URI:        https://www.gatitabakes.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gatita-bakes-ordering
 */

document.addEventListener('DOMContentLoaded', function () {
    // --- Initialize Swiper ---
    const swiperContainer = document.querySelector('.gatita-product-slider');
    if (swiperContainer) {
        try {
            const swiper = new Swiper('.gatita-product-slider', {
                slidesPerView: 'auto',
                spaceBetween: 40,
                centeredSlides: false,
                loop: false,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true
                },
                breakpoints: {
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 20
                    },
                    480: {
                        slidesPerView: 2,
                        spaceBetween: 30
                    },
                    768: {
                        slidesPerView: 3,
                        spaceBetween: 30
                    },
                    1024: {
                        slidesPerView: 4,
                        spaceBetween: 40
                    },
                    1440: {
                        slidesPerView: 5,
                        spaceBetween: 40
                    }
                },
                grabCursor: true,
                observer: true,
                observeParents: true
            });
        } catch (error) {
            console.error("Gatita Bakes: Error initializing Swiper:", error);
        }
    }

    // --- Quantity Input Enhancement ---
    document.addEventListener('click', function(e) {
        if (e.target.matches('.gatita-quantity-decrease, .gatita-quantity-increase')) {
            const input = e.target.closest('.gatita-quantity-controls').querySelector('.gatita-quantity-input');
            if (input) {
                let value = parseInt(input.value) || 0;
                if (e.target.matches('.gatita-quantity-decrease')) {
                    value = Math.max(1, value - 1);
                } else {
                    value = Math.min(10, value + 1);
                }
                input.value = value;
                input.dispatchEvent(new Event('change'));
            }
        }
    });

    // Prevent negative numbers in quantity inputs
    document.addEventListener('input', function(e) {
        if (e.target.matches('.gatita-quantity-input')) {
            const value = parseInt(e.target.value) || 0;
            if (value < 1) e.target.value = 1;
            if (value > 10) e.target.value = 10;
        }
    });

    // --- Dynamic Form Logic ---
    const orderTypeRadios = document.querySelectorAll('input[name="order_type"]');
    const pickupFields = document.getElementById('pickup-location-fields');
    const deliveryFields = document.getElementById('delivery-address-fields');
    const form = document.getElementById('gatita-order-form');

    function toggleOrderFields() {
        if (!form) return;
        const selectedType = form.querySelector('input[name="order_type"]:checked')?.value;
        
        if (selectedType === 'pickup') {
            pickupFields?.classList.add('visible');
            deliveryFields?.classList.remove('visible');
            document.querySelectorAll('#pickup-location-fields [required]').forEach(field => {
                field.required = true;
            });
            document.querySelectorAll('#delivery-address-fields [required]').forEach(field => {
                field.required = false;
            });
        } else if (selectedType === 'delivery') {
            pickupFields?.classList.remove('visible');
            deliveryFields?.classList.add('visible');
            document.querySelectorAll('#pickup-location-fields [required]').forEach(field => {
                field.required = false;
            });
            document.querySelectorAll('#delivery-address-fields [required]').forEach(field => {
                field.required = true;
            });
        }
    }

    // Initialize form fields
    if (form && orderTypeRadios.length > 0) {
        orderTypeRadios.forEach(radio => {
            radio.addEventListener('change', toggleOrderFields);
        });
        toggleOrderFields();
    }
});