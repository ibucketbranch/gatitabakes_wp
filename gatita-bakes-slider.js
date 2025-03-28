// assets/gatita-bakes-slider.js
// Version: 1.4.3 (Refined window.onload update, observer reliance)

document.addEventListener('DOMContentLoaded', function () {

  let swiperInstance = null; // Keep a reference to the Swiper instance

  // --- Initialize Swiper on DOMContentLoaded ---
  const swiperContainer = document.querySelector('.gatita-product-slider');
  if (swiperContainer) {
    try {
      swiperInstance = new Swiper(swiperContainer, { // Assign to swiperInstance
        slidesPerView: 1.2,
        spaceBetween: 15,
        breakpoints: {
          640: { slidesPerView: 2.3, spaceBetween: 20 },
          820: { slidesPerView: 2.5, spaceBetween: 25 },
          1024: { slidesPerView: 3, spaceBetween: 30 }
        },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        grabCursor: true,
        // Rely on observers to handle changes after initial load
        observer: true,
        observeParents: true,
        observeSlideChildren: true, // Observe changes within slides too
      });
    } catch (error) {
      console.error("Gatita Bakes: Error initializing Swiper:", error);
    }
  } else {
    // console.log("Gatita Bakes: Swiper container '.gatita-product-slider' not found.");
  }

  // --- Dynamic Form Logic (Unchanged) ---
  const orderTypeRadios = document.querySelectorAll('input[name="order_type"]');
  const pickupFields = document.getElementById('pickup-location-fields');
  const deliveryFields = document.getElementById('delivery-address-fields');
  const form = document.getElementById('gatita-order-form');

  function toggleOrderFields() {
    if (!form) return;
    let selectedType = form.querySelector('input[name="order_type"]:checked')?.value;
    if (pickupFields && deliveryFields) {
        if (selectedType === 'pickup') { pickupFields.classList.add('visible'); deliveryFields.classList.remove('visible'); } else if (selectedType === 'delivery') { pickupFields.classList.remove('visible'); deliveryFields.classList.add('visible'); } else { pickupFields.classList.remove('visible'); deliveryFields.classList.remove('visible'); }
        const pickupSelect = pickupFields.querySelector('select'); const deliveryInputs = deliveryFields.querySelectorAll('input[id^="delivery_"]');
        if (pickupSelect) { pickupSelect.required = (selectedType === 'pickup'); }
        deliveryInputs.forEach(input => { const isRequiredField = ['delivery_street', 'delivery_city', 'delivery_zip'].includes(input.id); input.required = (selectedType === 'delivery' && isRequiredField); });
        const deliveryNotes = deliveryFields.querySelector('#delivery_notes'); if (deliveryNotes) { deliveryNotes.required = false; }
    }
  }

  if (form && orderTypeRadios.length > 0 && pickupFields && deliveryFields) {
    orderTypeRadios.forEach(radio => { radio.addEventListener('change', toggleOrderFields); });
    toggleOrderFields();
  }

  // --- Add event listener for window.onload ---
  window.addEventListener('load', function() {
    // Force Swiper update after ALL resources (images etc.) are loaded
    // Check if instance exists and has the update method
    if (swiperInstance && typeof swiperInstance.update === 'function' && !swiperInstance.destroyed) {
        // console.log("Gatita Bakes: Forcing Swiper update on window.load");
        swiperInstance.update();

        // Maybe a tiny delay *after* load just in case of final reflows
        setTimeout(function() {
            if (swiperInstance && typeof swiperInstance.update === 'function' && !swiperInstance.destroyed) {
                // console.log("Gatita Bakes: Second Swiper update after window.load delay");
                 swiperInstance.update();
            }
        }, 150); // Shorter delay after window.load
    }
  });

}); // End DOMContentLoaded