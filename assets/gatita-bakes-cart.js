/**
 ** Plugin Name:       Gatita Bakes Ordering       **
 * Filename:          gatita-bakes-cart.js
 * Plugin URI:        https://www.gatitabakes.com/
 * Description:       Cart functionality for Gatita Bakes ordering system
 * Version:           1.9.1
 * Author:            Bucketbranch Inc.
 * Author URI:        https://www.gatitabakes.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gatita-bakes-ordering
 */

jQuery(document).ready(function($) {
    // Initialize Swiper
    function initializeProductSwiper() {
        // Wait for Swiper to be loaded
        if (typeof Swiper === 'undefined') {
            console.error('Swiper not loaded yet, waiting...');
            setTimeout(initializeProductSwiper, 500);
            return;
        }

        // Check if container exists
        const swiperContainer = document.querySelector('.product-swiper');
        if (!swiperContainer) {
            console.error('Swiper container not found');
            return;
        }

        // Check if slides exist
        const slides = swiperContainer.querySelectorAll('.swiper-slide');
        console.log('Found slides:', slides.length);

        // Destroy existing instance if it exists
        if (window.productSwiper && window.productSwiper.destroy) {
            window.productSwiper.destroy(true, true);
        }

        try {
            // Initialize Swiper
            window.productSwiper = new Swiper('.product-swiper', {
                init: false,
                slidesPerView: 1,
                spaceBetween: 20,
                centeredSlides: false,
                loop: false,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                },
                breakpoints: {
                    640: {
                        slidesPerView: 2,
                        spaceBetween: 15
                    },
                    1024: {
                        slidesPerView: 3,
                        spaceBetween: 20
                    }
                },
                on: {
                    init: function() {
                        console.log('Swiper initialized:', {
                            slides: this.slides.length,
                            activeIndex: this.activeIndex,
                            params: this.params
                        });
                    },
                    click: function(swiper, event) {
                        console.log('Slide clicked:', event.target);
                    }
                }
            });

            // Initialize swiper
            window.productSwiper.init();

            // Add direct click handlers for navigation
            const nextButton = document.querySelector('.swiper-button-next');
            const prevButton = document.querySelector('.swiper-button-prev');

            if (nextButton) {
                nextButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Next button clicked');
                    if (window.productSwiper) {
                        window.productSwiper.slideNext();
                    }
                });
            }

            if (prevButton) {
                prevButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Prev button clicked');
                    if (window.productSwiper) {
                        window.productSwiper.slidePrev();
                    }
                });
            }

        } catch (error) {
            console.error('Error initializing Swiper:', error);
        }
    }

    // Initialize when document is ready
    initializeProductSwiper();

    // Also initialize on window load to ensure all assets are loaded
    window.addEventListener('load', initializeProductSwiper);

    // Cart functionality
    let cart = {
        items: {},
        
        addItem: function(id, name, price) {
            if (!id || !name || isNaN(price)) {
                console.error('Invalid item data:', { id, name, price });
                return;
            }
            
            console.log('Adding item to cart:', { id, name, price });
            
            if (this.items[id]) {
                this.items[id].quantity++;
            } else {
                this.items[id] = {
                    name: name,
                    price: parseFloat(price),
                    quantity: 1
                };
            }
            
            this.saveToStorage();
            this.updateUI();
            
            // Show feedback
            $('.gatita-cart-notification').remove();
            const notification = $('<div class="gatita-cart-notification">Item added to cart!</div>');
            $('body').append(notification);
            setTimeout(() => notification.fadeOut(300, function() { $(this).remove(); }), 2000);
        },
        
        removeItem: function(id) {
            console.log('Removing item:', id);
            delete this.items[id];
            this.saveToStorage();
            this.updateUI();
        },
        
        saveToStorage: function() {
            console.log('Saving cart to storage');
            localStorage.setItem('gatitaCart', JSON.stringify(this.items));
        },
        
        loadFromStorage: function() {
            console.log('Loading cart from storage');
            const stored = localStorage.getItem('gatitaCart');
            if (stored) {
                try {
                    this.items = JSON.parse(stored);
                    console.log('Loaded items:', this.items);
                } catch (e) {
                    console.error('Error loading cart:', e);
                    this.items = {};
                }
                this.updateUI();
            }
        },
        
        updateUI: function() {
            console.log('Updating cart UI');
            const $cartItems = $('#gatita-cart-items');
            const $cartTotal = $('.gatita-cart-total .total-amount');
            const $orderSummary = $('.order-summary');
            let total = 0;

            // Clear existing items
            $cartItems.empty();
            if ($orderSummary.length) {
                $orderSummary.find('.order-items').empty();
            }
            
            if (Object.keys(this.items).length === 0) {
                $cartItems.html('<li class="cart-empty-msg">Your cart is empty</li>');
                $cartTotal.text('$0.00');
                if ($orderSummary.length) {
                    $orderSummary.find('.order-total').text('$0.00');
                }
                return;
            }

            // Add items to cart and order summary
            for (let id in this.items) {
                const item = this.items[id];
                const itemTotal = item.price * item.quantity;
                total += itemTotal;

                // Add to cart
                $cartItems.append(`
                    <li class="gatita-cart-item" data-id="${id}">
                        <span class="cart-item-name">${item.name}</span>
                        <span class="cart-item-quantity">×${item.quantity}</span>
                        <span class="cart-item-price">$${itemTotal.toFixed(2)}</span>
                        <button class="remove-item" data-id="${id}">×</button>
                    </li>
                `);

                // Add to order summary if it exists
                if ($orderSummary.length) {
                    $orderSummary.find('.order-items').append(`
                        <div class="order-item">
                            <span class="item-name">${item.name} ×${item.quantity}</span>
                            <span class="item-price">$${itemTotal.toFixed(2)}</span>
                        </div>
                    `);
                }
            }

            // Update totals
            $cartTotal.text('$' + total.toFixed(2));
            if ($orderSummary.length) {
                $orderSummary.find('.order-total').text('$' + total.toFixed(2));
            }

            // Update hidden form field
            $('#gatita-cart-data').val(JSON.stringify(this.items));
        },
        
        clear: function() {
            console.log('Clearing cart');
            this.items = {};
            this.saveToStorage();
            this.updateUI();
        }
    };

    // Initialize cart from localStorage
    cart.loadFromStorage();

    // Add to cart button click handler
    $(document).on('click', '.gatita-add-to-cart', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $button = $(this);
        const $card = $button.closest('.gatita-product-card');
        
        if (!$card.length) {
            console.error('Product card not found');
            return;
        }

        const id = $card.data('product-id');
        const name = $card.find('h3').text().trim();
        const priceText = $card.find('.gatita-product-price').text().trim();
        const price = parseFloat(priceText.replace(/[^0-9.]/g, ''));

        console.log('Adding to cart:', { id, name, price, card: $card[0] });

        if (!id || !name || isNaN(price)) {
            console.error('Invalid product data:', { id, name, price });
            return;
        }

        cart.addItem(id, name, price);
        
        // Visual feedback
        $button.prop('disabled', true).text('Added!');
        setTimeout(() => {
            $button.prop('disabled', false).text('Add to Cart');
        }, 1000);
    });

    // Remove from cart button click handler
    $(document).on('click', '.remove-item', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        cart.removeItem(id);
    });

    // Form submission handler
    $('#gatita-order-form').on('submit', function(e) {
        e.preventDefault();
        
        // Validate cart
        if (Object.keys(cart.items).length === 0) {
            alert('Please add at least one item to your cart before submitting.');
            return false;
        }

        // Get form data
        const formData = new FormData(this);
        
        // Add cart data
        formData.append('action', 'submit_gatita_order');
        formData.append('cart_data', JSON.stringify(cart.items));
        
        // Disable submit button and show loading state
        const $submitButton = $(this).find('button[type="submit"]');
        const originalText = $submitButton.text();
        $submitButton.prop('disabled', true).text('Processing...');
        
        // Submit form via AJAX
        $.ajax({
            url: gatitaBakesSettings.ajaxurl || this.action,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Order submission response:', response);
                if (response.success && response.data && response.data.redirect_url) {
                    // Clear cart
                    cart.clear();
                    // Force redirect to the confirmation page
                    window.location.replace(response.data.redirect_url);
                } else {
                    alert('There was an error processing your order. Please try again.');
                    $submitButton.prop('disabled', false).text(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.error('Order submission error:', error);
                alert('There was an error processing your order. Please try again.');
                $submitButton.prop('disabled', false).text(originalText);
            }
        });
    });

    // Initialize order type fields
    $('input[name="order_type"]:checked').trigger('change');

    // Order type toggle
    $('input[name="order_type"]').on('change', function() {
        const isDelivery = $(this).val() === 'delivery';
        $('#gatita-pickup-fields').toggle(!isDelivery);
        $('#gatita-delivery-fields').toggle(isDelivery);
        
        // Toggle required fields
        $('#pickup_location').prop('required', !isDelivery);
        $('#delivery_street, #delivery_city, #delivery_zip').prop('required', isDelivery);
    });

    // Phone number formatting
    $('#customer_phone').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length >= 3) {
            value = '(' + value.substr(0,3) + ')-' + value.substr(3);
        }
        if (value.length >= 9) {
            value = value.substr(0,9) + '-' + value.substr(9);
        }
        if (value.length > 14) {
            value = value.substr(0,14);
        }
        $(this).val(value);
    });

    // Make cart object globally accessible
    window.cart = cart;
}); 