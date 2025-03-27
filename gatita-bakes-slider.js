// assets/gatita-bakes-slider.js
// Version: 1.4.1 (Includes setTimeout and observer options for Swiper)

document.addEventListener('DOMContentLoaded', function () {

  // --- Initialize Swiper WITH A DELAY and OBSERVER OPTIONS ---
  const swiperContainer = document.querySelector('.gatita-product-slider');
  if (swiperContainer) {
    // Add a small delay to allow layout rendering before initialization
    setTimeout(function() {
        try { // Add error handling just in case Swiper isn't fully loaded yet
            const swiper = new Swiper(swiperContainer, {
                slidesPerView: 1.2, // Slides per view on mobile
                spaceBetween: 15, // Space between slides on mobile

                // Responsive breakpoints
                breakpoints: {
                    640: { // Screens >= 640px
                        slidesPerView: 2.3,
                        spaceBetween: 20
                    },
                    820: { // Screens >= 820px (matches column layout breakpoint)
                        slidesPerView: 2.5,
                        spaceBetween: 25
                    },
                    1024: { // Screens >= 1024px
                        slidesPerView: 3,
                        spaceBetween: 30
                    }
                },

                // Navigation buttons
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },

                // Enable grab cursor
                grabCursor: true,

                // NEW: Observer options to auto-update on DOM changes
                observer: true,
                observeParents: true,
                observeSlideChildren: true, // Also observe changes within slides

                // Optional: Add loop if desired, but test without it first
                // loop: true,

            });

            // Optional: Add a re-update after a longer delay just in case images loading slow affect things
            setTimeout(function() {
                if (swiper && typeof swiper.update === 'function') {
                    swiper.update();
                }
            }, 800); // Longer delay (adjust if needed)

        } catch (error) {
            console.error("Error initializing Swiper:", error);
        }
    }, 250); // Delay in milliseconds (try 200-300 if 250 doesn't work)
  } else {
      // console.log("Swiper container '.gatita-product-slider' not found."); // Uncomment for debugging
  }

  // --- Dynamic Form Logic ---
  const orderTypeRadios = document.querySelectorAll('input[name="order_type"]');
  const pickupFields = document.getElementById('pickup-location-fields');
  const deliveryFields = document.getElementById('delivery-address-fields');
  const form = document.getElementById('gatita-order-form');

  // Function to update visibility and required attributes
  function toggleOrderFields() {
    // Ensure form element exists before querying inside it
    if (!form) return;
    let selectedType = form.querySelector('input[name="order_type"]:checked')?.value;

    // Ensure field sections exist before trying to access them
    if (pickupFields && deliveryFields) {
        // --- Visibility Control ---
        if (selectedType === 'pickup') {
            pickupFields.classList.add('visible');
            deliveryFields.classList.remove('visible');
        } else if (selectedType === 'delivery') {
            pickupFields.classList.remove('visible');
            deliveryFields.classList.add('visible');
        } else { // Default or error case
            pickupFields.classList.remove('visible');
            deliveryFields.classList.remove('visible');
        }

        // --- Required Attribute Control ---
        const pickupSelect = pickupFields.querySelector('select');
        const deliveryInputs = deliveryFields.querySelectorAll('input[id^="delivery_"]'); // Target specific inputs

        // Toggle required for pickup select
        if (pickupSelect) {
            pickupSelect.required = (selectedType === 'pickup');
        }

        // Toggle required for specific delivery inputs
        deliveryInputs.forEach(input => {
            const isRequiredField = ['delivery_street', 'delivery_city', 'delivery_zip'].includes(input.id);
            input.required = (selectedType === 'delivery' && isRequiredField);
        });
         // Ensure textarea is never required by default
         const deliveryNotes = deliveryFields.querySelector('#delivery_notes');
         if (deliveryNotes) {
             deliveryNotes.required = false;
         }
    }
  }

  // Add event listeners only if all relevant elements exist
  if (form && orderTypeRadios.length > 0 && pickupFields && deliveryFields) {
    orderTypeRadios.forEach(radio => {
      radio.addEventListener('change', toggleOrderFields);
    });

    // Initial call to set state on page load based on default checked radio
    toggleOrderFields();
  } else {
      // console.log("Form logic elements missing, skipping event listeners."); // Uncomment for debugging
  }

}); // End DOMContentLoaded
