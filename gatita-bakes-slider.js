/**
 ** Plugin Name:       Gatita Bakes Ordering       ** 
 * Filename: assets/gatita-bakes-slider.js
 * Plugin URI:        https://www.gatitabakes.com/
 * Description:       Shopping cart functionality for order form
 * Version:           1.5.2
 * Author:            Bucketbranch
 * Author URI:        https://www.gatitabakes.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gatita-bakes-ordering
 */

// Version: 1.5.2 (Cart Functionality + Address Verification)

jQuery(document).ready(function($) {
    // Initialize Swiper for product slider
    let swiperInstance = null;
    const swiperContainer = document.querySelector('.gatita-product-slider');
    if (swiperContainer) {
        try {
            swiperInstance = new Swiper(swiperContainer, {
                slidesPerView: 1.2,
                spaceBetween: 15,
                breakpoints: {
                    640: { slidesPerView: 2.3, spaceBetween: 20 },
                    820: { slidesPerView: 2.5, spaceBetween: 25 },
                    1024: { slidesPerView: 3, spaceBetween: 30 }
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                },
                grabCursor: true,
                observer: true,
                observeParents: true,
                observeSlideChildren: true
            });
        } catch (error) {
            console.error("Error initializing Swiper:", error);
        }
    }
    
    // Initialize cart functionality
    const cart = {
        items: {},
        
        // Add item to cart
        addItem: function(productId) {
            // Check if item already exists in cart
            if (this.items[productId]) {
                this.items[productId].quantity++;
            } else {
                const productCard = $(`.gatita-product-card button[data-product="${productId}"]`).closest('.gatita-product-card');
                const productName = productCard.find('h3').text();
                const productPrice = parseFloat(productCard.find('.price').text().replace('$', ''));
                
                this.items[productId] = {
                    id: productId,
                    name: productName,
                    price: productPrice,
                    quantity: 1
                };
            }
            
            this.updateCartUI();
            this.updateTotals();
        },
        
        // Remove item from cart
        removeItem: function(productId) {
            if (this.items[productId]) {
                delete this.items[productId];
                this.updateCartUI();
                this.updateTotals();
            }
        },
        
        // Update item quantity
        updateQuantity: function(productId, quantity) {
            if (this.items[productId]) {
                this.items[productId].quantity = Math.max(1, parseInt(quantity) || 1);
                this.updateCartUI();
                this.updateTotals();
            }
        },
        
        // Update the cart UI
        updateCartUI: function() {
            // Update cart item count
            let totalQuantity = 0;
            
            Object.values(this.items).forEach(item => {
                totalQuantity += item.quantity;
                
                // Update or show the cart item
                const cartItemEl = $(`#cart-item-${item.id}`);
                if (cartItemEl.length) {
                    cartItemEl.show();
                    const quantityInput = cartItemEl.find('.gatita-quantity-input');
                    if (quantityInput.length) {
                        quantityInput.val(item.quantity);
                    }
                }
            });
            
            // Update the total quantity in the title
            $('#cart-item-count').text(totalQuantity);
            
            // Hide removed items
            $('.gatita-cart-item').each(function() {
                const productId = $(this).data('product-id');
                if (!cart.items[productId]) {
                    $(this).hide();
                }
            });
        },
        
        // Calculate and update order totals
        updateTotals: function() {
            let subtotal = 0;
            
            Object.values(this.items).forEach(item => {
                subtotal += item.price * item.quantity;
            });
            
            const taxRate = 0.0; // Update as needed
            const tax = subtotal * taxRate;
            const total = subtotal + tax;
            
            // Update total displays
            $('.gatita-subtotal-amount').text(' + subtotal.toFixed(2));
            $('.gatita-tax-amount').text(' + tax.toFixed(2));
            $('.gatita-total-amount').text(' + total.toFixed(2));
        }