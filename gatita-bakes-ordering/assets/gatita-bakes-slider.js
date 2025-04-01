/**
 * Plugin Name:     Gatita Bakes Ordering
 * Filename:         assets/gatita-bakes-slider.js
 * Description:      Handles Swiper JS initialization, dynamic form field visibility, and dynamic order summary updates.
 * Version:          1.7.7
 * Author:           Bucketbranch
 */

document.addEventListener('DOMContentLoaded', function () {

    let swiperInstance = null;

    // --- Initialize Swiper ---
    const swiperContainer = document.querySelector('.gatita-product-slider');
    if (swiperContainer) {
        try {
            swiperInstance = new Swiper(swiperContainer, {
                slidesPerView: 1.2, spaceBetween: 15,
                breakpoints: {
                    640: { slidesPerView: 2.3, spaceBetween: 20 },
                    820: { slidesPerView: 2.5, spaceBetween: 25 },
                    1024: { slidesPerView: 3, spaceBetween: 30 }
                },
                navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                grabCursor: true, observer: true, observeParents: true, observeSlideChildren: true,
            });
        } catch (error) { console.error("Gatita Bakes: Error initializing Swiper:", error); }
    }

    // --- Dynamic Form Logic ---
    const orderTypeRadios = document.querySelectorAll('input[name="order_type"]');
    const pickupFields = document.getElementById('pickup-location-fields');
    const deliveryFields = document.getElementById('delivery-address-fields');
    const form = document.getElementById('gatita-order-form');

    function toggleOrderFields() {
        if (!form) return;
        let selectedType = form.querySelector('input[name="order_type"]:checked')?.value;
        if (pickupFields && deliveryFields) { /* ... Show/hide + required logic ... */ }
    }

    if (form && orderTypeRadios.length > 0 && pickupFields && deliveryFields) {
        orderTypeRadios.forEach(radio => { radio.addEventListener('change', toggleOrderFields); });
        toggleOrderFields(); // Initial call
    }

    // --- Add Swiper update on window load ---
    window.addEventListener('load', function() { /* ... Swiper update logic ... */ });


    // ==========================================
    // === NEW: DYNAMIC ORDER SUMMARY LOGIC ===
    // ==========================================
    const productCards = form.querySelectorAll('.gatita-product-card');
    const cartItemsList = document.getElementById('gatita-cart-items');
    const cartTotalsContainer = document.getElementById('gatita-cart-totals');

    function formatPrice(price) {
        return parseFloat(price).toFixed(2);
    }

    function updateSummary() {
        if (!cartItemsList || !cartTotalsContainer || !productCards.length) {
            // console.log("Summary elements not found, skipping summary update.");
            return; // Exit if essential elements aren't found
        }

        let cartItemsHTML = '';
        let subtotal = 0;
        const emptyCartMsg = '<li class="cart-empty-msg">Your cart is empty.</li>';
        const totalsPlaceholder = '<p class="cart-total-placeholder">Select items to see total.</p>';

        productCards.forEach(card => {
            const checkbox = card.querySelector('.product-select-checkbox');
            const quantityInput = card.querySelector('.product-quantity-input');

            if (checkbox && quantityInput && checkbox.checked) {
                const name = card.dataset.productName || 'Unknown Item';
                const price = parseFloat(card.dataset.productPrice) || 0;
                const quantity = parseInt(quantityInput.value, 10) || 1; // Default to 1 if invalid

                if (price > 0 && quantity > 0) {
                    const itemTotal = price * quantity;
                    subtotal += itemTotal;

                    cartItemsHTML += `
                        <li>
                            <span class="item-name">${name}</span>
                            <span class="item-qty">x ${quantity}</span>
                            <span class="item-total-price">$${formatPrice(itemTotal)}</span>
                        </li>
                    `;
                }
            }
        });

        // Update Cart Items List
        if (cartItemsHTML === '') {
            cartItemsList.innerHTML = emptyCartMsg;
        } else {
            cartItemsList.innerHTML = cartItemsHTML;
        }

        // Update Totals Area
        if (subtotal > 0) {
            // You might add tax/fees calculation here if needed later
            let tax = 0; // Example: No tax for now
            let grandTotal = subtotal + tax;

            cartTotalsContainer.innerHTML = `
                <p><span>Subtotal:</span> <span>$${formatPrice(subtotal)}</span></p>
                ${tax > 0 ? `<p><span>Tax:</span> <span>$${formatPrice(tax)}</span></p>` : ''}
                <p class="grand-total"><span>Total:</span> <span>$${formatPrice(grandTotal)}</span></p>
            `;
        } else {
            cartTotalsContainer.innerHTML = totalsPlaceholder;
        }
    }

    // Add Event Listeners to Checkboxes and Quantity Inputs
    if (form) {
        // Use event delegation on the form for better performance, especially with sliders
        form.addEventListener('change', function(event) {
            if (event.target.matches('.product-select-checkbox') || event.target.matches('.product-quantity-input')) {
                updateSummary();
            }
        });
        // Also listen for 'input' on quantity fields for more immediate updates
        form.addEventListener('input', function(event) {
             if (event.target.matches('.product-quantity-input')) {
                updateSummary();
            }
        });

         // Initial summary calculation on page load
         updateSummary();
    }

}); // End DOMContentLoaded