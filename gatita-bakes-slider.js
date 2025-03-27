// assets/gatita-bakes-slider.js

document.addEventListener('DOMContentLoaded', function () {

  // --- Initialize Swiper ---
  const swiperContainer = document.querySelector('.gatita-product-slider');
  if (swiperContainer) {
    const swiper = new Swiper(swiperContainer, {
      slidesPerView: 1.2,
      spaceBetween: 15,
      breakpoints: {
        640: { slidesPerView: 2.3, spaceBetween: 20 },
        820: { slidesPerView: 2.5, spaceBetween: 25 },
        1024: { slidesPerView: 3, spaceBetween: 30 }
      },
      navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
      grabCursor: true,
      // loop: true, // Optional
    });
  }

  // --- Dynamic Form Logic ---
  const orderTypeRadios = document.querySelectorAll('input[name="order_type"]');
  const pickupFields = document.getElementById('pickup-location-fields');
  const deliveryFields = document.getElementById('delivery-address-fields');
  const form = document.getElementById('gatita-order-form'); // Get the form element

  // Function to update visibility and required attributes
  function toggleOrderFields() {
    let selectedType = form.querySelector('input[name="order_type"]:checked')?.value; // More robust selection

    if (pickupFields && deliveryFields) { // Ensure elements exist
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

        if (pickupSelect) {
            pickupSelect.required = (selectedType === 'pickup');
        }

        deliveryInputs.forEach(input => {
            // Only make street, city, zip required for delivery
            const isRequiredField = ['delivery_street', 'delivery_city', 'delivery_zip'].includes(input.id);
            input.required = (selectedType === 'delivery' && isRequiredField);
        });
         // Textarea is never required by default
         const deliveryNotes = deliveryFields.querySelector('#delivery_notes');
         if (deliveryNotes) deliveryNotes.required = false;
    }
  }

  // Add event listeners only if elements exist
  if (form && orderTypeRadios.length > 0 && pickupFields && deliveryFields) {
    orderTypeRadios.forEach(radio => {
      radio.addEventListener('change', toggleOrderFields);
    });

    // Initial call to set state on page load
    toggleOrderFields();
  }

}); // End DOMContentLoaded
