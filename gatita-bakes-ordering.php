/**
 ** Plugin Name:       Gatita Bakes Ordering       ** Filename: gatita-bakes-order-form.php
 * Plugin URI:        https://www.gatitabakes.com/
 * Description:       Order form shortcode with shopping cart functionality
 * Version:           1.5
 * Author:            Bucketbranch
 * Author URI:        https://www.gatitabakes.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gatita-bakes-ordering
 */

// =========================================================================
// 4. ORDER FORM SHORTCODE [gatita_bakes_order_form] - SHOPPING CART STYLE
// =========================================================================
function gatita_bakes_order_form_shortcode() {
    // --- Display Status Messages ---
    // This checks for query parameters in the URL that indicate order status
    // and displays appropriate success or error messages
    $output = '';
    if ( isset( $_GET['order_status'] ) ) {
        if ( $_GET['order_status'] === 'success' ) { 
            $output .= "<div class='gatita-notice gatita-notice-success'>Thank you for your order! Please check your email for confirmation and payment instructions.</div>"; 
        }
        elseif ( strpos($_GET['order_status'], 'error') === 0 ) { 
            $error_message = 'Sorry, there was an error processing your order. Please check your details and try again or contact us directly.'; 
            $output .= "<div class='gatita-notice gatita-notice-error'>" . esc_html($error_message) . "</div>"; 
        }
    }

    // --- Get product and location data from our helper functions ---
    $products = gatita_bakes_get_products(); // Gets all available products
    $pickup_locations = gatita_bakes_get_pickup_locations(); // Gets pickup locations
    $free_samples = gatita_bakes_get_free_samples(); // Gets free sample options

    // --- Start Form Output using output buffering ---
    // This captures all HTML output until ob_get_clean() is called
    ob_start();
    ?>
    <!-- Main order form - posts to admin-post.php which is WordPress' form handling endpoint -->
    <form id="gatita-order-form" method="POST" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
        <!-- Hidden fields for WordPress form handling -->
        <input type="hidden" name="action" value="gatita_bakes_submit_order">
        <?php wp_nonce_field( 'gatita_bakes_order_nonce', 'gatita_bakes_nonce' ); // Security nonce to prevent CSRF attacks ?>

        <!-- Cart title with dynamic item count -->
        <h1 class="gatita-cart-title">YOUR BAG (<span id="cart-item-count">0</span> UNIT)</h1>
        
        <!-- Shipping notification - the "almost there" message for free shipping -->
        <div class="gatita-shipping-notification">
            <div class="gatita-notification-icon">i</div>
            <div class="gatita-notification-text">
                <strong>ALMOST THERE!</strong>
                <p>You're only $20.00 away from free shipping.</p>
            </div>
        </div>
        
        <!-- Shipping information - processing time notice -->
        <div class="gatita-shipping-info">
            <div class="gatita-truck-icon">🚚</div>
            <p>Orders submitted by 11am PT are typically processed within 2 business days (M-F, excluding major holidays). Please factor in this processing time when selecting a shipping method.</p>
        </div>
        
        <!-- Cart items section - initially hidden, shows when items are added -->
        <div class="gatita-cart-items">
            <?php 
            // Loop through all available products to create hidden cart item rows
            foreach ($products as $slug => $product) : 
            ?>
                <!-- Each cart item - has data attributes for JS functionality -->
                <div class="gatita-cart-item" id="cart-item-<?php echo esc_attr($slug); ?>" 
                     style="display: none;" 
                     data-product-id="<?php echo esc_attr($slug); ?>" 
                     data-price="<?php echo esc_attr($product['price']); ?>">
                    
                    <!-- Product image -->
                    <div class="gatita-product-image">
                        <img src="<?php echo esc_url(GATITA_BAKES_PLUGIN_URL . 'images/' . $product['image']); ?>" 
                             alt="<?php echo esc_attr($product['name']); ?>">
                    </div>
                    
                    <!-- Product details and controls -->
                    <div class="gatita-product-details">
                        <h3><?php echo esc_html($product['name']); ?></h3>
                        <div class="gatita-product-price">$<?php echo number_format($product['price'], 2); ?></div>
                        
                        <!-- Quantity controls with +/- buttons -->
                        <div class="gatita-quantity-controls">
                            <button type="button" class="gatita-quantity-decrease">-</button>
                            <input type="number" name="quantity[<?php echo esc_attr($slug); ?>]" 
                                   class="gatita-quantity-input" value="1" min="1" max="10">
                            <button type="button" class="gatita-quantity-increase">+</button>
                            <button type="button" class="gatita-remove-item">Remove</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Product Selection Slider - allows browsing all available products -->
        <div class="gatita-product-selection">
            <h2>Choose Your Baked Goods</h2>
            <!-- Swiper slider container -->
            <div class="swiper gatita-product-slider">
                <div class="swiper-wrapper">
                    <?php 
                    // Loop through all products to create slides
                    foreach ($products as $slug => $product) : 
                    ?>
                        <div class="swiper-slide">
                            <div class="gatita-product-card">
                                <!-- Product image -->
                                <img src="<?php echo esc_url(GATITA_BAKES_PLUGIN_URL . 'images/' . $product['image']); ?>" 
                                     alt="<?php echo esc_attr($product['name']); ?>">
                                
                                <!-- Product information -->
                                <h3><?php echo esc_html($product['name']); ?></h3>
                                <p><?php echo isset($product['description']) ? esc_html($product['description']) : ''; ?></p>
                                <p class="price">$<?php echo esc_html(number_format((float)$product['price'], 2)); ?></p>
                                
                                <!-- Add to cart button with data attribute for JS functionality -->
                                <div class="gatita-product-order-controls">
                                    <button type="button" class="gatita-add-to-cart" 
                                            data-product="<?php echo esc_attr($slug); ?>">Add to Cart</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Slider navigation buttons -->
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>

        <!-- Free Samples Section -->
        <div class="gatita-samples-section">
            <h2>GET 3 FREE SAMPLES WITH YOUR ORDER</h2>
            <p class="gatita-samples-remaining"><span id="samples-count">3</span> SAMPLES REMAINING</p>
            <p class="gatita-samples-info">Click on a sample to add to your bag.</p>
            
            <!-- Grid layout for sample selection -->
            <div class="gatita-samples-grid">
                <?php 
                // Loop through all samples to create the sample grid
                foreach ($free_samples as $sample_id => $sample) : 
                ?>
                    <!-- Each sample item with data attribute for JS selection -->
                    <div class="gatita-sample-item" data-sample-id="<?php echo esc_attr($sample_id); ?>">
                        <div class="gatita-sample-image">
                            <img src="<?php echo esc_url(GATITA_BAKES_PLUGIN_URL . 'images/samples/' . $sample['image']); ?>" 
                                 alt="<?php echo esc_attr($sample['name']); ?>">
                        </div>
                        <div class="gatita-sample-name"><?php echo esc_html($sample['name']); ?></div>
                        <!-- Hidden input to track selection state -->
                        <input type="hidden" name="samples[<?php echo esc_attr($sample_id); ?>]" value="0" class="sample-checkbox">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Two-column layout for customer details and order summary -->
        <div class="gatita-order-details-wrapper">
            <!-- Left column: Customer details -->
            <div class="gatita-order-main-details">
                <!-- Personal information section -->
                <div class="gatita-details-section">
                    <h2>Your Details</h2>
                    <div class="gatita-form-row">
                        <label for="customer_name">Name <span class="required">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>
                    <div class="gatita-form-row">
                        <label for="customer_email">Email <span class="required">*</span></label>
                        <input type="email" id="customer_email" name="customer_email" required>
                    </div>
                    <div class="gatita-form-row">
                        <label for="customer_phone">Phone <span class="required">*</span></label>
                        <input type="tel" id="customer_phone" name="customer_phone" required>
                    </div>
                </div>

                <!-- Order method section (pickup or delivery) -->
                <div class="gatita-details-section">
                    <h2>Order Method <span class="required">*</span></h2>
                    <!-- Radio button group for pickup/delivery -->
                    <div class="gatita-form-row gatita-radio-group">
                        <label><input type="radio" name="order_type" value="pickup" checked required> Pickup</label>
                        <label><input type="radio" name="order_type" value="delivery" required> Delivery</label>
                    </div>
                    
                    <!-- Pickup Location Fields - shown when pickup is selected -->
                    <div id="pickup-location-fields" class="gatita-form-section-dynamic">
                        <div class="gatita-form-row">
                            <label for="pickup_location">Pickup Location <span class="required">*</span></label>
                            <select id="pickup_location" name="pickup_location" required>
                                <option value="">-- Select --</option>
                                <?php foreach ($pickup_locations as $key => $display_text) : ?>
                                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($display_text); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <p><small>Please adhere to the pickup schedule for your chosen location.</small></p>
                    </div>
                    
                    <!-- Delivery Address Fields - shown when delivery is selected -->
                    <div id="delivery-address-fields" class="gatita-form-section-dynamic">
                        <h3>Delivery Address <span class="required">*</span></h3>
                        <p><small>(Required for delivery orders)</small></p>
                        <div class="gatita-form-row">
                            <label for="delivery_street">Street Address</label>
                            <input type="text" id="delivery_street" name="delivery_street">
                        </div>
                        <div class="gatita-form-row">
                            <label for="delivery_city">City</label>
                            <input type="text" id="delivery_city" name="delivery_city" value="">
                        </div>
                        <div class="gatita-form-row">
                            <label for="delivery_zip">ZIP Code</label>
                            <input type="text" id="delivery_zip" name="delivery_zip">
                        </div>
                        <div class="gatita-form-row">
                            <label for="delivery_notes">Delivery Notes (optional)</label>
                            <textarea id="delivery_notes" name="delivery_notes"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right column: Order summary -->
            <div class="gatita-order-summary-area">
                <!-- Sticky content that stays visible as user scrolls -->
                <div class="gatita-summary-sticky-content">
                    <h2>Summary</h2>
                    
                    <!-- Promo Code field -->
                    <div class="gatita-promo-code">
                        <label for="promo_code">PROMO CODE</label>
                        <div class="gatita-promo-input">
                            <input type="text" id="promo_code" name="promo_code" placeholder="Enter it Here">
                            <button type="button" id="apply-promo" class="gatita-apply-promo">APPLY</button>
                        </div>
                    </div>
                    
                    <!-- Order Totals section -->
                    <div class="gatita-order-totals">
                        <div class="gatita-total-row">
                            <span>SUBTOTAL</span>
                            <span class="gatita-subtotal-amount">$0.00</span>
                        </div>
                        <div class="gatita-total-row">
                            <span>TAX</span>
                            <span class="gatita-tax-amount">$0.00</span>
                        </div>
                        <div class="gatita-total-row gatita-order-total">
                            <span>ORDER TOTAL</span>
                            <span class="gatita-total-amount">$0.00</span>
                        </div>
                    </div>
                    
                    <!-- Checkout Button -->
                    <button type="submit" class="gatita-button gatita-place-order-button">PROCEED TO CHECKOUT</button>
                    
                    <!-- Payment Options section -->
                    <div class="gatita-payment-options">
                        <div class="gatita-afterpay-option">
                            <img src="<?php echo GATITA_BAKES_PLUGIN_URL; ?>images/afterpay-logo.png" alt="Afterpay">
                            <span>available for orders between $100 - $2,000</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php
    // Capture all the HTML output from the buffer
    $output .= ob_get_clean();
    return $output;
}
add_shortcode('gatita_bakes_order_form', 'gatita_bakes_order_form_shortcode');

/**
 * Function to define free samples available to customers
 * 
 * This is a new helper function to provide sample product data
 * similar to how products and pickup locations are defined
 */
function gatita_bakes_get_free_samples() {
    return array(
        // Each sample has a unique ID as the array key
        'awapuhi' => array(
            'name' => 'Awapuhi Shampoo Sample',  // Display name
            'image' => 'awapuhi-sample.jpg'      // Image filename in images/samples/ directory
        ),
        'detangler' => array(
            'name' => 'The Detangler Sample',
            'image' => 'detangler-sample.jpg'
        ),
        'blonde_conditioner' => array(
            'name' => 'Forever Blonde Conditioner Sample',
            'image' => 'blonde-conditioner-sample.jpg'
        ),
        'blonde_shampoo' => array(
            'name' => 'Forever Blonde Shampoo Sample',
            'image' => 'blonde-shampoo-sample.jpg'
        ),
        'gloss_drops' => array(
            'name' => 'Gloss Drops Sample',
            'image' => 'gloss-drops-sample.jpg'
        ),
        'lavender_conditioner' => array(
            'name' => 'Lavender Mint Moisturizing Conditioner Sample',
            'image' => 'lavender-conditioner-sample.jpg'
        ),
        'lavender_shampoo' => array(
            'name' => 'Lavender Mint Moisturizing Shampoo Sample',
            'image' => 'lavender-shampoo-sample.jpg'
        ),
        'round_trip' => array(
            'name' => 'Round Trip Sample',
            'image' => 'round-trip-sample.jpg'
        )
    );
}
