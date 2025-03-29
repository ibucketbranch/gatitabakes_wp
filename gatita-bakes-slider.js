/**
 ** Plugin Name:       Gatita Bakes Ordering       ** Filename: gatita-bakes-cart.js
 * Plugin URI:        https://www.gatitabakes.com/
 * Description:       Shopping cart functionality for order form
 * Version:           1.5
 * Author:            Bucketbranch
 * Author URI:        https://www.gatitabakes.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gatita-bakes-ordering
 */

/**
 * This file handles all the shopping cart functionality including:
 * - Product slider initialization using Swiper
 * - Adding/removing items from cart
 * - Updating quantities
 * - Sample selection (limited to 3)
 * - Price calculations
 * - Toggling between pickup/delivery fields
 */

document.addEventListener('DOMContentLoaded', function() {
    // -------------------------------------------------------------------------
    // SWIPER SLIDER INITIALIZATION
    // -------------------------------------------------------------------------
    // This initializes the product slider using the Swiper library
    let swiperInstance = null;
    const swiperContainer = document.querySelector('.gatita-product-slider');
    if (swiperContainer) {
        try {
            swiperInstance = new Swiper(swiperContainer, {
                // Default view on mobile: 1.2 slides visible
                slidesPerView: 1.2,
                spaceBetween: 15,
                // Responsive breakpoints for different screen sizes
                breakpoints: {
                    640: { slidesPerView: 2.3, spaceBetween: 20 },  // Tablet
                    820: { slidesPerView: 2.5, spaceBetween: 25 },  // Small desktop
                    1024: { slidesPerView: 3, spaceBetween: 30 }    // Large desktop
                },
                // Navigation arrows
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                },
                grabCursor: true,  // Changes cursor to "grab" when hovering over slider
                // Make Swiper respond to dynamic content changes
                observer: true,
                observeParents: true,
                observeSlideChildren: true
            });
        } catch (error) {
            console.error("Error initializing Swiper:", error);
        }
    }
    
    // -------------------------------------------------------------------------
    // SHOPPING CART OBJECT
    // -------------------------------------------------------------------------
    // This object contains all cart functionality and state
    const cart = {
        // State storage for cart items and selected samples
        items: {},         // Object to store cart items {productId: {id, name, price, quantity}}
        samples: [],       // Array to store selected sample IDs (max 3)
        
        /**
         * Add an item to the cart
         * @param {string} productId - The ID of the product to add
         */
        addItem: function(productId) {
            // Check if item already exists in cart
            if (this.items[productId]) {
                // If it exists, just increase quantity
                this.items[productId].quantity++;
            } else {
                // If it's new, find the product info from the DOM
                const productCard = document.querySelector(`.gatita-product-card [data-product="${productId}"]`)
                                            .closest('.gatita-product-card');
                const productName = productCard.querySelector('h3').textContent;
                const productPrice = parseFloat(productCard.querySelector('.price').textContent.replace('$', ''));
                
                // Add the new item to our items object
                this.items[productId] = {
                    id: productId,
                    name: productName,
                    price: productPrice,
                    quantity: 1
                };
            }
            
            // Update the UI to show the updated cart
            this.updateCartUI();
            this.updateTotals();
        },
        
        /**
         * Remove an item from the cart
         * @param {string} productId - The ID of the product to remove
         */
        removeItem: function(productId) {
            if (this.items[productId]) {
                // Delete the item from our items object
                delete this.items[productId];
                this.updateCartUI();
                this.updateTotals();
            }
        },
        
        /**
         * Update the quantity of an item in the cart
         * @param {string} productId - The ID of the product to update
         * @param {number} quantity - The new quantity
         */
        updateQuantity: function(productId, quantity) {
            if (this.items[productId]) {
                // Ensure quantity is at least 1
                this.items[productId].quantity = Math.max(1, parseInt(quantity) || 1);
                this.updateCartUI();
                this.updateTotals();
            }
        },
        
        /**
         * Toggle a sample selection (add or remove)
         * @param {string} sampleId - The ID of the sample to toggle
         * @returns {boolean} - Whether the toggle was successful
         */
        toggleSample: function(sampleId) {
            const index = this.samples.indexOf(sampleId);
            if (index === -1) {
                // Sample is not selected, try to add it
                // Check if we can add more samples (limit is 3)
                if (this.samples.length < 3) {
                    this.samples.push(sampleId);
                } else {
                    alert("You can only select up to 3 free samples.");
                    return false;
                }
            } else {
                // Sample is already selected, remove it
                this.samples.splice(index, 1);
            }
            this.updateSamplesUI();
            return true;
        },
        
        /**
         * Update the cart UI elements to reflect the current state
         */
        updateCartUI: function() {
            // Update cart item count in the header
            const itemCount = document.getElementById('cart-item-count');
            let totalQuantity = 0;
            
            // Process each item in the cart
            Object.values(this.items).forEach(item => {
                totalQuantity += item.quantity;
                
                // Update or show the cart item row
                const cartItemEl = document.getElementById(`cart-item-${item.id}`);
                if (cartItemEl) {
                    cartItemEl.style.display = 'flex';  // Make the item visible
                    const quantityInput = cartItemEl.querySelector('.gatita-quantity-input');
                    if (quantityInput) {
                        quantityInput.value = item.quantity;  // Update quantity input
                    }
                }
            });
            
            // Update the total quantity display
            if (itemCount) {
                itemCount.textContent = totalQuantity;
            }
            
            // Hide cart items that were removed
            document.querySelectorAll('.gatita-cart-item').forEach(el => {
                const productId = el.dataset.productId;
                if (!this.items[productId]) {
                    el.style.display = 'none';
                }
            });
        },
        
        /**
         * Update the samples UI to reflect current selection
         */
        updateSamplesUI: function() {
            // Update samples count remaining
            const samplesCount = document.getElementById('samples-count');
            if (samplesCount) {
                samplesCount.textContent = 3 - this.samples.length;
            }
            
            // Update visual selection state of each sample
            document.querySelectorAll('.gatita-sample-item').forEach(el => {
                const sampleId = el.dataset.sampleId;
                const isSelected = this.samples.includes(sampleId);
                
                if (isSelected) {
                    // Apply selected styling and update hidden input
                    el.classList.add('selected');
                    el.querySelector('.sample-checkbox').value = '1';
                } else {
                    // Remove selected styling and reset hidden input
                    el.classList.remove('selected');
                    el.querySelector('.sample-checkbox').value = '0';
                }
            });
        },
        
        /**
         * Calculate and update order totals in the UI
         */
        updateTotals: function() {
            let subtotal = 0;
            
            // Sum up the price * quantity for each item
            Object.values(this.items).forEach(item => {
                subtotal += item.price * item.quantity;
            });
            
            // Calculate tax (change the rate as needed)
            const taxRate = 0.0; // Set to 0 initially, adjust as needed
            const tax = subtotal * taxRate;
            const total = subtotal + tax;
            
            // Update total displays in the UI
            const subtotalEl = document.querySelector('.gatita-subtotal-amount');
            const taxEl = document.querySelector('.gatita-tax-amount');
            const totalEl = document.querySelector('.gatita-total-amount');
            
            if (subtotalEl) subtotalEl.textContent = '$' + subtotal.toFixed(2);
            if (taxEl) taxEl.textContent = '$' + tax.toFixed(2);
            if (totalEl) totalEl.textContent = '$' + total.toFixed(2);
        }
    };
    
    // -------------------------------------------------------------------------
    // EVENT LISTENERS
    // -------------------------------------------------------------------------
    
    // ADD TO CART BUTTONS
    // These buttons appear on each product card in the slider
    document.querySelectorAll('.gatita-add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            // Get the product ID from the data attribute
            const productId = this.dataset.product;
            cart.addItem(productId);
        });
    });
    
    // QUANTITY DECREASE BUTTONS (-)
    // These appear in the cart for each product
    document.querySelectorAll('.gatita-quantity-decrease').forEach(button => {
        button.addEventListener('click', function() {
            // Find the parent cart item and get its ID
            const cartItem = this.closest('.gatita-cart-item');
            const productId = cartItem.dataset.productId;
            const quantityInput = cartItem.querySelector('.gatita-quantity-input');
            const currentQty = parseInt(quantityInput.value);
            
            // Only decrease if quantity is greater than 1
            if (currentQty > 1) {
                cart.updateQuantity(productId, currentQty - 1);
            }
        });
    });
    
    // QUANTITY INCREASE BUTTONS (+)
    // These appear in the cart for each product
    document.querySelectorAll('.gatita-quantity-increase').forEach(button => {
        button.addEventListener('click', function() {
            // Find the parent cart item and get its ID
            const cartItem = this.closest('.gatita-cart-item');
            const productId = cartItem.dataset.productId;
            const quantityInput = cartItem.querySelector('.gatita-quantity-input');
            const currentQty = parseInt(quantityInput.value);
            
            // Increase the quantity
            cart.updateQuantity(productId, currentQty + 1);
        });
    });
    
    // QUANTITY INPUT FIELD CHANGES
    // Direct input in the quantity field
    document.querySelectorAll('.gatita-quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            // Find the parent cart item and get its ID
            const cartItem = this.closest('.gatita-cart-item');
            const productId = cartItem.dataset.productId;
            cart.updateQuantity(productId, this.value);
        });
    });
    
    // REMOVE ITEM BUTTONS
    // These appear in the cart for each product
    document.querySelectorAll('.gatita-remove-item').forEach(button => {
        button.addEventListener('click', function() {
            // Find the parent cart item and get its ID
            const cartItem = this.closest('.gatita-cart-item');
            const productId = cartItem.dataset.productId;
            cart.removeItem(productId);
        });
    });
    
    // SAMPLE SELECTION
    // Clicking on a sample in the samples grid
    document.querySelectorAll('.gatita-sample-item').forEach(item => {
        item.addEventListener('click', function() {
            // Get the sample ID from data attribute
            const sampleId = this.dataset.sampleId;
            cart.toggleSample(sampleId);
        });
    });
    
    // PROMO CODE APPLICATION
    // Clicking the "APPLY" button for promo codes
    const applyPromoBtn = document.getElementById('apply-promo');
    if (applyPromoBtn) {
        applyPromoBtn.addEventListener('click', function() {
            const promoCode = document.getElementById('promo_code').value.trim();
            if (promoCode) {
                // Very simple promo code handling - you would add more logic here
                alert('Promo code applied: ' + promoCode);
                // TODO: Add actual promo code validation and discount logic
                cart.updateTotals();
            } else {
                alert('Please enter a promo code');
            }
        });
    }
    
    // -------------------------------------------------------------------------
    // ORDER TYPE TOGGLE (PICKUP/DELIVERY)
    // -------------------------------------------------------------------------
    const orderTypeRadios = document.querySelectorAll('input[name="order_type"]');
    const pickupFields = document.getElementById('pickup-location-fields');
    const deliveryFields = document.getElementById('delivery-address-fields');
    
    /**
     * Toggle visibility of pickup/delivery fields based on selection
     */
    function toggleOrderFields() {
        const selectedType = document.querySelector('input[name="order_type"]:checked')?.value;
        
        if (pickupFields && deliveryFields) {
            if (selectedType === 'pickup') {
                // Show pickup fields, hide delivery fields
                pickupFields.classList.add('visible');
                deliveryFields.classList.remove('visible');
                
                // Toggle required attributes for form validation
                document.getElementById('pickup_location').required = true;
                document.getElementById('delivery_street').required = false;
                document.getElementById('delivery_city').required = false;
                document.getElementById('delivery_zip').required = false;
            } else if (selectedType === 'delivery') {
                // Show delivery fields, hide pickup fields
                pickupFields.classList.remove('visible');
                deliveryFields.classList.add('visible');
                
                // Toggle required attributes for form validation
                document.getElementById('pickup_location').required = false;
                document.getElementById('delivery_street').required = true;
                document.getElementById('delivery_city').required = true;
                document.getElementById('delivery_zip').required = true;
            }
        }
    }
    
    // Add change listeners to the radio buttons
    if (orderTypeRadios.length > 0) {
        orderTypeRadios.forEach(radio => {
            radio.addEventListener('change', toggleOrderFields);
        });
        
        // Initialize on page load to set correct state
        toggleOrderFields();
    }
    
    // -------------------------------------------------------------------------
    // FORM SUBMISSION VALIDATION
    // -------------------------------------------------------------------------
    const orderForm = document.getElementById('gatita-order-form');
    if (orderForm) {
        orderForm.addEventListener('submit', function(e)
