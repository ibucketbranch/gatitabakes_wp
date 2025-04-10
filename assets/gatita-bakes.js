document.addEventListener('DOMContentLoaded', function() {
    // Initialize Swiper with fixed slides and snapping
    const swiper = new Swiper('.gatita-product-slider', {
        slidesPerView: 3,
        slidesPerGroup: 3, // Move 3 slides at a time
        spaceBetween: 20,
        loop: false,
        speed: 400, // Faster transition
        resistance: true,
        resistanceRatio: 0, // Disable overscrolling
        watchSlidesProgress: true,
        preventInteractionOnTransition: true,
        slidesPerGroupSkip: 0,
        rewind: false,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
            type: 'bullets',
            renderBullet: function (index, className) {
                return '<span class="' + className + '"></span>';
            }
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev'
        },
        breakpoints: {
            // Mobile - single slide
            320: {
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 10
            },
            // Tablet - 2 slides
            768: {
                slidesPerView: 2,
                slidesPerGroup: 2,
                spaceBetween: 15
            },
            // Desktop - 3 slides
            1024: {
                slidesPerView: 3,
                slidesPerGroup: 3,
                spaceBetween: 20
            }
        }
    });

    // Cart state
    let cart = [];

    // Handle quantity changes
    document.querySelectorAll('.gatita-quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const product = this.dataset.product;
            const price = parseFloat(this.dataset.price);
            const quantity = parseInt(this.value) || 0;

            updateCart(product, price, quantity);
        });
    });

    // Update cart
    function updateCart(product, price, quantity) {
        // Remove existing product from cart
        cart = cart.filter(item => item.product !== product);

        // Add product if quantity > 0
        if (quantity > 0) {
            cart.push({ product, price, quantity });
        }

        // Update cart display
        renderCart();
    }

    // Render cart items
    function renderCart() {
        const cartContainer = document.getElementById('gatita-cart-items');
        let cartHTML = '';
        let total = 0;

        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;

            cartHTML += `
                <div class="gatita-cart-item">
                    <div class="gatita-product-details">
                        <span class="gatita-item-name">${item.product}</span>
                        <span class="gatita-item-quantity">x${item.quantity}</span>
                    </div>
                    <span class="gatita-item-price">$${itemTotal.toFixed(2)}</span>
                    <button class="gatita-remove-item" data-product="${item.product}">×</button>
                </div>
            `;
        });

        // Add total if there are items
        if (cart.length > 0) {
            cartHTML += `
                <div class="gatita-cart-item">
                    <strong>Total:</strong>
                    <span class="gatita-item-price">$${total.toFixed(2)}</span>
                </div>
            `;
        } else {
            cartHTML = '<p>Your cart is empty</p>';
        }

        cartContainer.innerHTML = cartHTML;

        // Add remove button listeners
        document.querySelectorAll('.gatita-remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const product = this.dataset.product;
                const input = document.querySelector(`.gatita-quantity-input[data-product="${product}"]`);
                if (input) {
                    input.value = 0;
                    updateCart(product, parseFloat(input.dataset.price), 0);
                }
            });
        });
    }

    // Form validation
    document.querySelector('.gatita-submit-order').addEventListener('click', function(e) {
        e.preventDefault();

        // Check if cart is empty
        if (cart.length === 0) {
            alert('Please add at least one item to your cart');
            return;
        }

        // Validate required fields
        const requiredFields = document.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('error');
            } else {
                field.classList.remove('error');
            }
        });

        if (!isValid) {
            alert('Please fill in all required fields');
            return;
        }

        // Validate delivery date
        const deliveryDate = new Date(document.getElementById('delivery-date').value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (deliveryDate < today) {
            alert('Please select a future delivery date');
            return;
        }

        // If all validation passes, prepare order data
        const orderData = {
            customer: {
                name: document.getElementById('customer-name').value,
                email: document.getElementById('customer-email').value,
                phone: document.getElementById('customer-phone').value
            },
            delivery: {
                date: document.getElementById('delivery-date').value,
                time: document.getElementById('delivery-time').value,
                address: document.getElementById('delivery-address').value
            },
            items: cart
        };

        // Submit order data to WordPress
        fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'submit_gatita_order',
                order_data: JSON.stringify(orderData),
                nonce: gatitaBakesSettings.nonce // Make sure this is defined in your PHP
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Store order data in session storage for confirmation page
                sessionStorage.setItem('gatitaOrderData', JSON.stringify(orderData));
                
                // Redirect to confirmation page
                window.location.href = data.redirect_url;
            } else {
                alert(data.message || 'There was an error processing your order. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error processing your order. Please try again.');
        });
    });

    // Check if we're on the confirmation page and have order data
    if (window.location.pathname.includes('confirmation')) {
        const orderData = sessionStorage.getItem('gatitaOrderData');
        if (orderData) {
            // Display order details
            const order = JSON.parse(orderData);
            displayOrderConfirmation(order);
            // Clear the session storage
            sessionStorage.removeItem('gatitaOrderData');
        }
    }

    function displayOrderConfirmation(order) {
        const confirmationDetails = document.querySelector('.gatita-confirmation-details');
        if (!confirmationDetails) return;

        // Calculate total
        const total = order.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);

        // Update order items
        const itemsHTML = order.items.map(item => `
            <div class="gatita-order-item">
                <span class="item-name">${item.product}</span>
                <span class="item-quantity">x${item.quantity}</span>
                <span class="item-price">$${(item.price * item.quantity).toFixed(2)}</span>
            </div>
        `).join('');

        // Update customer info
        document.querySelector('.gatita-customer-info').innerHTML = `
            <h3>Customer Information</h3>
            <p><strong>Name:</strong> ${order.customer.name}</p>
            <p><strong>Email:</strong> ${order.customer.email}</p>
            <p><strong>Phone:</strong> ${order.customer.phone}</p>
        `;

        // Update delivery info
        document.querySelector('.gatita-delivery-info').innerHTML = `
            <h3>Delivery Details</h3>
            <p><strong>Date:</strong> ${order.delivery.date}</p>
            <p><strong>Time:</strong> ${order.delivery.time}</p>
            <p><strong>Address:</strong> ${order.delivery.address}</p>
        `;

        // Update order items and total
        document.querySelector('.gatita-order-items').innerHTML = `
            ${itemsHTML}
            <div class="gatita-order-total">
                <strong>Total:</strong>
                <span>$${total.toFixed(2)}</span>
            </div>
        `;
    }
}); 