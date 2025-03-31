/**
 * Gatita Bakes Ordering - JavaScript Functions
 * Version: 1.8.3
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize quantity controls
    initQuantityControls();
    
    // Initialize carousels
    initCarousels();
    
    // Update summary on page load
    updateOrderSummary();
});

/**
 * Initialize quantity controls for all products
 */
function initQuantityControls() {
    // Add focus and blur event listeners to all quantity inputs
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    quantityInputs.forEach(input => {
        // Create and append the custom arrow controls if they don't exist
        if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('quantity-control-arrows')) {
            const arrowsContainer = document.createElement('div');
            arrowsContainer.className = 'quantity-control-arrows';
            
            const arrowUp = document.createElement('div');
            arrowUp.className = 'arrow-up';
            arrowUp.innerHTML = '▲';
            arrowUp.addEventListener('click', function() {
                updateQuantity(input, 1);
            });
            
            const arrowDown = document.createElement('div');
            arrowDown.className = 'arrow-down';
            arrowDown.innerHTML = '▼';
            arrowDown.addEventListener('click', function() {
                updateQuantity(input, -1);
            });
            
            arrowsContainer.appendChild(arrowUp);
            arrowsContainer.appendChild(arrowDown);
            
            input.parentNode.insertBefore(arrowsContainer, input.nextSibling);
        }
        
        // Add focus events to show arrows
        input.addEventListener('focus', function() {
            this.classList.add('active-input');
            const arrowsContainer = this.nextElementSibling;
            if (arrowsContainer && arrowsContainer.classList.contains('quantity-control-arrows')) {
                arrowsContainer.classList.add('flex-visible');
            }
        });
        
        // Add blur events to hide arrows
        input.addEventListener('blur', function() {
            this.classList.remove('active-input');
            setTimeout(() => {
                const arrowsContainer = this.nextElementSibling;
                if (arrowsContainer && arrowsContainer.classList.contains('quantity-control-arrows')) {
                    arrowsContainer.classList.remove('flex-visible');
                }
            }, 200); // Small delay to allow for arrow clicks
        });
        
        // Add change event to validate and update
        input.addEventListener('change', function() {
            updateQuantity(this, 0);
        });
    });
    
    // Add click events to all quantity buttons
    const quantityButtons = document.querySelectorAll('.quantity-btn');
    quantityButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('.quantity-input');
            const change = this.textContent === '+' ? 1 : -1;
            updateQuantity(input, change);
        });
    });
}

/**
 * Update quantity value and related UI
 * @param {HTMLElement} input - The quantity input element
 * @param {number} change - Amount to change (1, -1, or 0 for direct input)
 */
function updateQuantity(input, change) {
    // Current value
    let value = parseInt(input.value) || 0;
    
    // Update value based on change
    if (change !== 0) {
        value += change;
    }
    
    // Ensure minimum value is 0 or specified min
    const min = parseInt(input.getAttribute('min') || 0);
    if (value < min) value = min;
    
    // Ensure maximum value is not exceeded
    const max = parseInt(input.getAttribute('max') || 99);
    if (value > max) value = max;
    
    // Update input value
    input.value = value;
    
    // Select product card if quantity > 0
    const productCard = findClosestParent(input, '.product-card');
    if (productCard) {
        if (value > 0) {
            productCard.classList.add('selected');
        } else {
            productCard.classList.remove('selected');
        }
    }
    
    // Update cart summary
    updateOrderSummary();
}

/**
 * Find closest parent element with specified selector
 * @param {HTMLElement} element - The starting element
 * @param {string} selector - CSS selector to match
 * @returns {HTMLElement|null} - The matching parent or null
 */
function findClosestParent(element, selector) {
    while (element && !element.matches(selector)) {
        element = element.parentElement;
    }
    return element;
}

/**
 * Initialize product carousels
 */
function initCarousels() {
    const carousels = document.querySelectorAll('.carousel-container');
    
    carousels.forEach(carousel => {
        // Add scroll event for dynamic navigation visibility
        carousel.addEventListener('scroll', function() {
            toggleCarouselNavVisibility(this);
        });
        
        // Initial nav visibility check
        toggleCarouselNavVisibility(carousel);
    });
}

/**
 * Toggle carousel navigation visibility based on scroll position
 * @param {HTMLElement} carousel - The carousel container
 */
function toggleCarouselNavVisibility(carousel) {
    const carouselWrapper = carousel.closest('.products-carousel');
    if (!carouselWrapper) return;
    
    const prevBtn = carouselWrapper.querySelector('.carousel-nav.prev');
    const nextBtn = carouselWrapper.querySelector('.carousel-nav.next');
    
    if (prevBtn && nextBtn) {
        // Show/hide prev button based on scroll position
        if (carousel.scrollLeft > 30) {
            prevBtn.classList.add('visible');
        } else {
            prevBtn.classList.remove('visible');
        }
        
        // Show/hide next button based on scroll position
        if (carousel.scrollLeft + carousel.clientWidth < carousel.scrollWidth - 30) {
            nextBtn.classList.add('visible');
        } else {
            nextBtn.classList.remove('visible');
        }
    }
}

/**
 * Slide the carousel left or right
 * @param {HTMLElement} button - The clicked nav button
 */
function slideCarousel(button) {
    const direction = button.classList.contains('prev') ? -1 : 1;
    const carousel = button.closest('.products-carousel').querySelector('.carousel-container');
    
    const scrollAmount = 200 * direction;
    carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
}

/**
 * Update the order summary with current selections
 */
function updateOrderSummary() {
    const summaryList = document.getElementById('summary-list');
    if (!summaryList) return;
    
    const subtotalElement = document.getElementById('subtotal');
    const totalElement = document.getElementById('total');
    
    // Get all product quantities
    const quantityInputs = document.querySelectorAll('.quantity-input');
    let summaryHTML = '';
    let subtotal = 0;
    let itemCount = 0;
    
    quantityInputs.forEach(input => {
        const quantity = parseInt(input.value) || 0;
        
        if (quantity > 0) {
            const productCard = findClosestParent(input, '.product-card');
            if (productCard) {
                const productTitle = productCard.querySelector('.product-title').textContent;
                const priceText = productCard.querySelector('.product-price').textContent;
                const price = parseFloat(priceText.replace(/[^0-9.]/g, ''));
                const itemTotal = price * quantity;
                
                subtotal += itemTotal;
                itemCount += quantity;
                
                summaryHTML += `
                    <div class="summary-item">
                        <span class="item-name">${quantity} × ${productTitle}</span>
                        <span class="item-price">$${itemTotal.toFixed(2)}</span>
                    </div>
                `;
            }
        }
    });
    
    // Update summary content
    if (itemCount > 0) {
        summaryList.innerHTML = summaryHTML;
        
        // Update cart title to show item count
        const cartTitle = document.querySelector('.order-summary-title');
        if (cartTitle) {
            cartTitle.textContent = `Your Cart (${itemCount} item${itemCount !== 1 ? 's' : ''})`;
        }
    } else {
        summaryList.innerHTML = '<div class="summary-item empty-message">Your cart is empty</div>';
        
        // Reset cart title
        const cartTitle = document.querySelector('.order-summary-title');
        if (cartTitle) {
            cartTitle.textContent = 'Your Cart';
        }
    }
    
    // Update totals
    if (subtotalElement) {
        subtotalElement.textContent = '$' + subtotal.toFixed(2);
    }
    
    if (totalElement) {
        // Add any tax calculations here if needed
        totalElement.textContent = '$' + subtotal.toFixed(2);
    }
}

/**
 * Limit the number of selectable free samples
 * @param {HTMLElement} checkbox - The clicked checkbox
 * @param {number} maxAllowed - Maximum number of samples allowed
 */
function limitSamples(checkbox, maxAllowed) {
    const sampleCheckboxes = document.querySelectorAll('.sample-checkbox:checked');
    
    if (sampleCheckboxes.length > maxAllowed) {
        checkbox.checked = false;
        alert(`You can select up to ${maxAllowed} free samples.`);
    }
    
    // Update free samples in summary if needed
    updateOrderSummary();
}
}); // End DOMContentLoaded
