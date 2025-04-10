<?php
/**
 ** Plugin Name:      Gatita Bakes Ordering       **
 * Filename:          gatita-bakes-ordering.php
 * Plugin URI:        https://www.gatitabakes.com/
 * Description:       Custom ordering system for Gatita Bakes artisan bread and bagels
 * Version:           1.8.1
 * Author:            Bucketbranch Inc.
 * Author URI:        https://www.gatitabakes.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gatita-bakes-ordering
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('GATITA_BAKES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GATITA_BAKES_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GATITA_BAKES_VERSION', '1.8.1'); // Match the version in the plugin header

// =========================================================================
// 1. ENQUEUE STYLES & SCRIPTS
// =========================================================================
/**
 * Enqueue plugin styles and scripts
 */
function gatita_bakes_enqueue_assets() {
    // Only load on pages with our shortcode
    global $post;
    
    if (!is_a($post, 'WP_Post') || (!has_shortcode($post->post_content, 'gatita_bakes_order_form') && !is_page_template('standalone-order.php'))) {
        return;
    }

    // Enqueue Swiper CSS
    wp_enqueue_style(
        'swiper-css',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
        array(),
        GATITA_BAKES_VERSION
    );

    // Enqueue plugin CSS
    wp_enqueue_style(
        'gatita-bakes-style',
        plugins_url('assets/gatita-bakes.css', __FILE__),
        array('swiper-css'),
        GATITA_BAKES_VERSION
    );

    // Enqueue jQuery
    wp_enqueue_script('jquery');

    // Enqueue Swiper JS
    wp_enqueue_script(
        'swiper-js',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
        array('jquery'),
        GATITA_BAKES_VERSION,
        true
    );

    // Enqueue plugin JS
    wp_enqueue_script(
        'gatita-bakes-cart',
        plugins_url('assets/gatita-bakes-cart.js', __FILE__),
        array('jquery', 'swiper-js'),
        GATITA_BAKES_VERSION,
        true
    );

    // Remove gatita-bakes-form-logic from debug check since it's not needed
    global $wp_scripts;
    $required_scripts = array('jquery', 'swiper-js', 'gatita-bakes-cart');
}
add_action('wp_enqueue_scripts', 'gatita_bakes_enqueue_assets');

// =========================================================================
// 2. DEFINE PRODUCTS & PICKUP LOCATIONS
// =========================================================================
function gatita_bakes_get_products() {
    return array(
        array(
            'id' => 'plain-sourdough',
            'name' => 'Plain Sourdough Loaf',
            'description' => 'Classic tangy sourdough with a chewy crust.',
            'price' => 8.00,
            'image' => 'Plain-Sourdough-Loaf.jpg'
        ),
        array(
            'id' => 'rosemary-sourdough',
            'name' => 'Rosemary Sourdough Loaf',
            'description' => 'Infused with fresh rosemary for an aromatic flavor.',
            'price' => 9.00,
            'image' => 'Rosemary-Sourdough-Loaf.jpg'
        ),
        array(
            'id' => 'everything-sourdough',
            'name' => 'Everything Sourdough Loaf',
            'description' => 'Coated with a savory everything bagel seasoning.',
            'price' => 9.50,
            'image' => 'Everything-Sourdough-Loaf.jpg'
        ),
        array(
            'id' => 'specialty-sourdough',
            'name' => 'Specialty Sourdough',
            'description' => 'Our special sourdough of the week.',
            'price' => 10.00,
            'image' => 'Other-Sourdough-Loaf.jpg'
        ),
        array(
            'id' => 'plain-bagels',
            'name' => 'Plain Bagels (Set of 4)',
            'description' => 'Classic sourdough bagels.',
            'price' => 12.00,
            'image' => 'Plain-Bagels.png'
        ),
        array(
            'id' => 'cheese-jalapeno-bagels',
            'name' => 'Cheese Jalapeño Bagels (Set of 4)',
            'description' => 'Spicy jalapeño and cheese bagels.',
            'price' => 14.00,
            'image' => 'Cheese-Jalapeno-Bagels.png'
        )
    );
}

function gatita_bakes_get_pickup_locations() {
    // ** IMPORTANT **: Update these with your actual locations and details
    return array(
        'main_bakery' => 'Main Bakery (123 Bread Lane, Mon-Fri 10am-4pm)',
        'farmers_market' => 'Saturday Farmers Market (Downtown Park, 8am-12pm)',
        'eastside_cafe' => 'Eastside Cafe Partnership (456 Coffee St, Wed ONLY 11am-2pm)',
    );
}

// =========================================================================
// 3. LANDING PAGE SHORTCODE [gatita_bakes_landing]
// =========================================================================
// This function generates the hero defined in the plugin.
// Make sure the shortcode [gatita_bakes_landing] is on the page where you want this hero.
function gatita_bakes_landing_page_shortcode() {
    ob_start();
    // Note: $hero_image_url is defined but not used if using CSS background below
    // $hero_image_url = GATITA_BAKES_PLUGIN_URL . 'images/hero-page-fullpage.png';
    $tagline = "The smell of fresh bread is the best kind of welcome.";
    ?>
    <div class="gatita-hero-section"> <?php // CSS targets this for background/layout ?>
        <div class="gatita-hero-content">
            <h1>Gatita Bakes</h1>
            <p><?php echo esc_html($tagline); ?></p>
            <a href="<?php echo esc_url(home_url('/order')); ?>" class="gatita-button">Order Now</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Register shortcodes on init
function gatita_bakes_register_shortcodes() {
    error_log('Registering Gatita Bakes shortcodes');
    add_shortcode('gatita_bakes_order_form', 'gatita_bakes_order_form_shortcode');
    add_shortcode('gatita_bakes_landing', 'gatita_bakes_landing_page_shortcode');
}
add_action('init', 'gatita_bakes_register_shortcodes');

// =========================================================================
// 4. ORDER FORM SHORTCODE [gatita_bakes_order_form] - SHOPPING CART STYLE
// =========================================================================

/**
 * Generate HTML for all product cards
 */
function gatita_bakes_get_product_cards() {
    $products = gatita_bakes_get_products();
    $output = '<div class="gatita-product-grid">';
    
    foreach ($products as $product) {
        $output .= gatita_bakes_product_card($product);
    }
    
    $output .= '</div>';
    return $output;
}

/**
 * Generate HTML for a single product card
 */
function gatita_bakes_product_card($product) {
    $id = isset($product['id']) ? $product['id'] : sanitize_title($product['name']);
    $name = isset($product['name']) ? esc_html($product['name']) : '';
    $description = isset($product['description']) ? esc_html($product['description']) : '';
    $price = isset($product['price']) ? floatval($product['price']) : 0;
    $image = isset($product['image']) ? esc_url(GATITA_BAKES_PLUGIN_URL . 'assets/images/' . $product['image']) : '';
    
    ob_start();
    ?>
    <div class="gatita-product-card" data-product-id="<?php echo esc_attr($id); ?>">
        <div class="gatita-product-image">
            <?php if ($image): ?>
                <img src="<?php echo $image; ?>" alt="<?php echo esc_attr($name); ?>">
            <?php endif; ?>
        </div>
        <div class="product-info">
            <h3 class="product-name"><?php echo $name; ?></h3>
            <p class="product-description"><?php echo $description; ?></p>
            <div class="product-price">$<?php echo number_format($price, 2); ?></div>
            <button type="button" class="gatita-add-to-cart">
                Add to Cart
            </button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Generate HTML for contact information fields
 */
function gatita_bakes_get_contact_fields() {
    ob_start();
    ?>
    <div class="gatita-form-section">
        <h2>Contact Information</h2>
        <div class="gatita-name-fields">
            <div class="gatita-form-row">
                <label for="customer_first_name">First Name <span class="required">*</span></label>
                <input type="text" id="customer_first_name" name="customer_first_name" required>
            </div>
            <div class="gatita-form-row">
                <label for="customer_last_name">Last Name <span class="required">*</span></label>
                <input type="text" id="customer_last_name" name="customer_last_name" required>
            </div>
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
    <?php
    return ob_get_clean();
}

/**
 * Generate HTML for delivery/pickup fields
 */
function gatita_bakes_get_delivery_fields() {
    ob_start();
    ?>
    <div class="gatita-form-row">
        <label>Order Type <span class="required">*</span></label>
        <div class="gatita-radio-group">
            <label>
                <input type="radio" name="order_type" value="pickup" checked> Pickup
            </label>
            <label>
                <input type="radio" name="order_type" value="delivery"> Delivery
            </label>
        </div>
    </div>

    <div id="gatita-pickup-fields" class="gatita-delivery-section">
        <div class="gatita-form-row">
            <label for="pickup_location">Pickup Location <span class="required">*</span></label>
            <select id="pickup_location" name="pickup_location" required>
                <option value="">Select a location</option>
                <?php
                $locations = gatita_bakes_get_pickup_locations();
                foreach ($locations as $key => $location) {
                    echo '<option value="' . esc_attr($key) . '">' . esc_html($location) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="gatita-form-row">
            <label for="pickup_notes">Pickup Notes</label>
            <textarea id="pickup_notes" name="pickup_notes" rows="3"></textarea>
        </div>
    </div>

    <div id="gatita-delivery-fields" class="gatita-delivery-section" style="display: none;">
        <div class="gatita-form-row">
            <label for="delivery_street">Street Address <span class="required">*</span></label>
            <input type="text" id="delivery_street" name="delivery_street">
        </div>
        <div class="gatita-form-row">
            <label for="delivery_city">City <span class="required">*</span></label>
            <input type="text" id="delivery_city" name="delivery_city">
        </div>
        <div class="gatita-form-row">
            <label for="delivery_zip">ZIP Code <span class="required">*</span></label>
            <input type="text" id="delivery_zip" name="delivery_zip">
        </div>
        <div class="gatita-form-row">
            <label for="delivery_notes">Delivery Notes</label>
            <textarea id="delivery_notes" name="delivery_notes" rows="3"></textarea>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Order form shortcode
 */
function gatita_bakes_order_form_shortcode() {
    error_log('Executing Gatita Bakes order form shortcode');
    
    // Get all products instead of limiting to 3
    $products = gatita_bakes_get_products();
    error_log('Number of products: ' . count($products));
    
    ob_start();
    ?>
    <form id="gatita-order-form" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
        <input type="hidden" name="action" value="submit_gatita_order">
        <?php wp_nonce_field('gatita_order_nonce', 'gatita_order_nonce'); ?>
        
        <h2>Place Your Order</h2>
        
        <!-- Product Slider Section -->
        <div class="gatita-order-product-section">
            <div class="swiper product-swiper">
                <div class="swiper-wrapper">
                    <?php 
                    foreach ($products as $product) : 
                        error_log('Adding product to slider: ' . $product['name']);
                    ?>
                        <div class="swiper-slide">
                            <?php echo gatita_bakes_product_card($product); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Add Navigation -->
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
                
                <!-- Add Pagination -->
                <div class="swiper-pagination"></div>
            </div>
        </div>

        <!-- Order Details Section -->
        <div class="gatita-order-details-wrapper">
            <div class="gatita-form-layout">
                <div class="gatita-main-content">
                    <?php echo gatita_bakes_get_contact_fields(); ?>
                    <?php echo gatita_bakes_get_delivery_fields(); ?>
                </div>
                <div class="gatita-order-summary">
                    <h2>Order Summary</h2>
                    <ul id="gatita-cart-items" class="gatita-cart-items">
                        <li class="cart-empty-msg">Your cart is empty</li>
                    </ul>
                    <div class="gatita-cart-total">
                        Total: <span id="gatita-cart-total">$0.00</span>
                    </div>
                    <input type="hidden" name="cart_data" id="gatita-cart-data" value="">
                    <button type="submit" class="gatita-submit-order">Place Order</button>
                </div>
            </div>
        </div>
    </form>
    <?php
    return ob_get_clean();
}

// =========================================================================
// 5. FORM SUBMISSION HANDLER
// =========================================================================
function gatita_bakes_handle_ajax_submission() {
    error_log('=== START: Gatita Bakes Order Submission ===');
    error_log('POST Data: ' . print_r($_POST, true));
    
    // Verify nonce
    if (!check_ajax_referer('gatita_order_nonce', 'gatita_order_nonce', false)) {
        error_log('Nonce verification failed');
        error_log('Expected nonce: ' . wp_create_nonce('gatita_order_nonce'));
        error_log('Received nonce: ' . $_POST['gatita_order_nonce']);
        wp_send_json_error('Invalid security token');
        wp_die();
    }

    // Get the last order number and increment it
    $order_number = get_option('gatita_bakes_last_order_number', 1000) + 1;
    error_log("Generated Order Number: " . $order_number);

    // Get form data
    $cart_data = json_decode(stripslashes($_POST['cart_data']), true);
    error_log("Cart Data: " . print_r($cart_data, true));

    $order_data = array(
        'status' => 'success',
        'order_number' => $order_number,
        'cart_items' => $cart_data,
        'customer_first_name' => sanitize_text_field($_POST['customer_first_name']),
        'customer_last_name' => sanitize_text_field($_POST['customer_last_name']),
        'customer_email' => sanitize_email($_POST['customer_email']),
        'customer_phone' => sanitize_text_field($_POST['customer_phone']),
        'order_type' => sanitize_text_field($_POST['order_type']),
        'pickup_location' => isset($_POST['pickup_location']) ? sanitize_text_field($_POST['pickup_location']) : '',
        'pickup_notes' => isset($_POST['pickup_notes']) ? sanitize_textarea_field($_POST['pickup_notes']) : '',
        'delivery_street' => isset($_POST['delivery_street']) ? sanitize_text_field($_POST['delivery_street']) : '',
        'delivery_city' => isset($_POST['delivery_city']) ? sanitize_text_field($_POST['delivery_city']) : '',
        'delivery_zip' => isset($_POST['delivery_zip']) ? sanitize_text_field($_POST['delivery_zip']) : '',
        'order_date' => current_time('mysql'),
        'total_amount' => 0
    );

    error_log("Processed Order Data: " . print_r($order_data, true));

    // Calculate total amount
    if (!empty($order_data['cart_items'])) {
        foreach ($order_data['cart_items'] as $item) {
            $order_data['total_amount'] += $item['price'] * $item['quantity'];
        }
    }
    error_log("Total Amount: $" . $order_data['total_amount']);

    // Update last order number
    update_option('gatita_bakes_last_order_number', $order_number);
    error_log("Updated last order number in database to: " . $order_number);

    // Save order data
    update_option('gatita_bakes_order_' . $order_number, $order_data);
    error_log("Saved order data to database with key: gatita_bakes_order_" . $order_number);

    // Send confirmation emails
    error_log("Attempting to send confirmation emails...");
    $email_result = gatita_bakes_send_order_emails($order_data);
    error_log("Email sending result: " . ($email_result ? 'Success' : 'Failed'));

    error_log('=== END: Gatita Bakes Order Submission ===');

    // Get confirmation page URL
    $confirmation_page = get_page_by_path('order-confirmation');
    if ($confirmation_page) {
        $redirect_url = add_query_arg(array(
            'order' => $order_number,
            'status' => 'success'
        ), get_permalink($confirmation_page->ID));
        error_log("Redirect URL: " . $redirect_url);
    } else {
        $redirect_url = home_url();
        error_log("Warning: Confirmation page not found, using home URL");
    }

    wp_send_json_success(array(
        'message' => 'Order submitted successfully',
        'redirect_url' => $redirect_url,
        'order_number' => $order_number
    ));

    wp_die();
}

// Admin email template
function gatita_bakes_get_admin_email_content($order_data) {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                line-height: 1.6; 
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            .order-details { 
                background: #fdfaf7;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border: 1px solid #e5a98c;
            }
            .order-item { 
                padding: 10px;
                border-bottom: 1px solid #eee;
                display: flex;
                justify-content: space-between;
            }
            .order-item:last-child {
                border-bottom: none;
            }
            .total { 
                font-weight: bold; 
                margin-top: 20px;
                padding-top: 20px;
                border-top: 2px solid #e5a98c;
                text-align: right;
                font-size: 1.2em;
            }
            .customer-info {
                background: #fdfaf7;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border: 1px solid #e5a98c;
            }
            .delivery-info {
                background: #fdfaf7;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border: 1px solid #e5a98c;
            }
            h2 {
                color: #5a4e46;
                margin-bottom: 20px;
                font-size: 24px;
            }
            h3 {
                color: #5a4e46;
                margin: 20px 0 10px;
                font-size: 20px;
            }
            h4 {
                color: #5a4e46;
                margin: 15px 0 10px;
                font-size: 18px;
            }
            .payment-info {
                background: #fdfaf7;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border: 1px solid #e5a98c;
            }
            .important-note {
                color: #721c24;
                background: #f8d7da;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
                border: 1px solid #f5c6cb;
            }
        </style>
    </head>
    <body>
        <h2>Hi Katerina!</h2>
        
        <p>You have a new order from <?php echo esc_html($order_data['customer_first_name'] . ' ' . $order_data['customer_last_name']); ?>.</p>
        
        <div class="order-details">
            <h3>Order #<?php echo esc_html($order_data['order_number']); ?> Summary</h3>
            <?php foreach ($order_data['cart_items'] as $item): ?>
                <div class="order-item">
                    <span><?php echo esc_html($item['name']); ?> × <?php echo esc_html($item['quantity']); ?></span>
                    <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                </div>
            <?php endforeach; ?>
            <div class="total">
                Total: $<?php echo number_format($order_data['total_amount'], 2); ?>
            </div>
        </div>

        <div class="customer-info">
            <h3>Customer Details</h3>
            <p><strong>Name:</strong> <?php echo esc_html($order_data['customer_first_name'] . ' ' . $order_data['customer_last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo esc_html($order_data['customer_email']); ?></p>
            <p><strong>Phone:</strong> <?php echo esc_html($order_data['customer_phone']); ?></p>
        </div>

        <div class="delivery-info">
            <h3><?php echo esc_html(ucfirst($order_data['order_type'])); ?> Details</h3>
            <?php if ($order_data['order_type'] === 'pickup'): ?>
                <p><strong>Location:</strong> <?php echo esc_html($order_data['pickup_location']); ?></p>
                <?php if (!empty($order_data['pickup_notes'])): ?>
                    <p><strong>Notes:</strong> <?php echo esc_html($order_data['pickup_notes']); ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p><strong>Delivery Address:</strong><br>
                <?php echo esc_html($order_data['delivery_street']); ?><br>
                <?php echo esc_html($order_data['delivery_city']); ?>, <?php echo esc_html($order_data['delivery_zip']); ?></p>
            <?php endif; ?>
        </div>

        <div class="payment-info">
            <h4>Payment Status</h4>
            <p>The customer has been instructed to send payment via Venmo to <strong>@katvalderrama</strong></p>
            <p>They should include order #<?php echo esc_html($order_data['order_number']); ?> in their payment note.</p>
        </div>

        <div class="important-note">
            <strong>Reminder:</strong> Please verify payment before confirming the order. Once payment is received, you can update the order status in your admin dashboard.
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// Customer email template
function gatita_bakes_get_customer_email_content($order_data) {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            :root {
                --gatita-brand-primary: #e5a98c;
                --gatita-brand-secondary: #8c6e5a;
                --gatita-brand-dark: #5a4e46;
                --gatita-brand-light: #fdfaf7;
                --gatita-brand-border: #e0e0e0;
                --gatita-text-main: #333333;
                --gatita-text-light: #666666;
            }
            
            body { 
                font-family: Arial, sans-serif; 
                line-height: 1.6; 
                color: var(--gatita-text-main);
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            
            .header {
                background-color: var(--gatita-brand-primary);
                color: white;
                padding: 20px;
                text-align: center;
                border-radius: 8px 8px 0 0;
                margin-bottom: 0;
            }
            
            .header h1 {
                margin: 0;
                font-size: 28px;
            }
            
            .content {
                background: white;
                padding: 30px;
                border: 1px solid var(--gatita-brand-border);
                border-radius: 0 0 8px 8px;
            }
            
            .order-details { 
                background: var(--gatita-brand-light);
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border: 1px solid var(--gatita-brand-primary);
            }
            
            .order-items {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            
            .order-items th {
                text-align: left;
                padding: 10px;
                border-bottom: 2px solid var(--gatita-brand-primary);
                color: var(--gatita-brand-dark);
            }
            
            .order-items td {
                padding: 10px;
                border-bottom: 1px solid var(--gatita-brand-border);
            }
            
            .order-items .qty {
                text-align: center;
            }
            
            .order-items .price {
                text-align: right;
            }
            
            .total { 
                font-weight: bold; 
                margin-top: 20px;
                padding-top: 20px;
                border-top: 2px solid var(--gatita-brand-primary);
                text-align: right;
                font-size: 1.2em;
                color: var(--gatita-brand-dark);
            }
            
            .customer-info {
                background: var(--gatita-brand-light);
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border: 1px solid var(--gatita-brand-primary);
            }
            
            .payment-info {
                background: var(--gatita-brand-light);
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border: 1px solid var(--gatita-brand-primary);
            }
            
            .important-note {
                color: #721c24;
                background: #f8d7da;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
                border: 1px solid #f5c6cb;
            }
            
            h2 {
                color: var(--gatita-brand-dark);
                margin-bottom: 20px;
                font-size: 24px;
            }
            
            h3 {
                color: var(--gatita-brand-dark);
                margin: 20px 0 10px;
                font-size: 20px;
            }
            
            .venmo-button {
                display: inline-block;
                background-color: #008CFF;
                color: white;
                text-decoration: none;
                padding: 12px 25px;
                border-radius: 5px;
                margin: 20px 0;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Thank You for Your Order!</h1>
        </div>
        
        <div class="content">
            <p>Hi <?php echo esc_html($order_data['customer_first_name']); ?>,</p>
            <p>Thank you for choosing Gatita Bakes! Your order (#<?php echo esc_html($order_data['order_number']); ?>) has been received and is pending payment.</p>

            <div class="order-details">
                <h2>Order Summary</h2>
                <table class="order-items">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="qty">Qty</th>
                            <th class="price">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_data['cart_items'] as $item): ?>
                            <tr>
                                <td><?php echo esc_html($item['name']); ?></td>
                                <td class="qty"><?php echo esc_html($item['quantity']); ?></td>
                                <td class="price">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="total">
                    Total: $<?php echo number_format($order_data['total_amount'], 2); ?>
                </div>
            </div>

            <div class="customer-info">
                <h3>Your Information</h3>
                <p><strong>Name:</strong> <?php echo esc_html($order_data['customer_first_name'] . ' ' . $order_data['customer_last_name']); ?></p>
                <p><strong>Email:</strong> <?php echo esc_html($order_data['customer_email']); ?></p>
                <p><strong>Phone:</strong> <?php echo esc_html($order_data['customer_phone']); ?></p>
                
                <h3><?php echo esc_html(ucfirst($order_data['order_type'])); ?> Details</h3>
                <?php if ($order_data['order_type'] === 'pickup'): ?>
                    <p><strong>Location:</strong> <?php echo esc_html($order_data['pickup_location']); ?></p>
                    <?php if (!empty($order_data['pickup_notes'])): ?>
                        <p><strong>Notes:</strong> <?php echo esc_html($order_data['pickup_notes']); ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p><strong>Delivery Address:</strong><br>
                    <?php echo esc_html($order_data['delivery_street']); ?><br>
                    <?php echo esc_html($order_data['delivery_city']); ?>, <?php echo esc_html($order_data['delivery_zip']); ?></p>
                <?php endif; ?>
            </div>

            <div class="payment-info">
                <h3>Payment Instructions</h3>
                <p>Please send payment via Venmo to: <strong>@katvalderrama</strong></p>
                <p>Include your order number (#<?php echo esc_html($order_data['order_number']); ?>) in the payment note.</p>
                
                <?php
                $venmo_url = sprintf(
                    'https://venmo.com/%s?txn=pay&amount=%s&note=%s',
                    'katvalderrama',
                    urlencode($order_data['total_amount']),
                    urlencode('GatitaBakes Order #' . $order_data['order_number'])
                );
                ?>
                <a href="<?php echo esc_url($venmo_url); ?>" class="venmo-button" target="_blank">
                    Pay $<?php echo number_format($order_data['total_amount'], 2); ?> on Venmo
                </a>
            </div>

            <div class="important-note">
                <strong>Important:</strong> Your order is not confirmed until payment is received and verified.
            </div>

            <p>If you have any questions, please don't hesitate to contact us.</p>
            <p>Thank you for choosing Gatita Bakes!</p>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// =========================================================================
// 6. VENMO PAYMENT INTEGRATION
// =========================================================================

/**
 * Add Venmo payment information to confirmation emails
 */
function gatita_bakes_add_venmo_to_email($email_content, $order_data) {
    // Generate unique payment tracking ID
    $tracking_id = 'GB' . $order_data['order_number'] . strtoupper(substr(uniqid(), -6));
    
    // Create Venmo payment URL with pre-filled amount and note
    $venmo_username = 'katvalderrama'; // Updated Venmo username
    $venmo_amount = $order_data['total_amount'];
    $venmo_note = 'GatitaBakesOrder #' . $order_data['order_number'] . ' ' . $tracking_id;
    
    $venmo_url = sprintf(
        'https://venmo.com/%s?txn=pay&amount=%s&note=%s',
        $venmo_username,
        esc_attr($venmo_amount),
        urlencode($venmo_note)
    );
    
    // Create Venmo payment button
    $venmo_button = '
    <div style="text-align: center; margin: 20px 0;">
        <a href="' . esc_url($venmo_url) . '" 
           style="display: inline-block; background-color: #0074de; color: white !important; 
                  padding: 12px 25px; text-decoration: none; border-radius: 5px; 
                  font-weight: bold;" target="_blank">
            Pay $' . $venmo_amount . ' on Venmo
        </a>
    </div>';
    
    // Add payment tracking information
    $payment_info = '
    <div style="margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-left: 4px solid #e5a98c;">
        <h3 style="margin-top: 0; color: #5a4e46;">Payment Information</h3>
        <p><strong>Payment Tracking ID:</strong> ' . esc_html($tracking_id) . '</p>
        <p><strong>IMPORTANT:</strong> Please include this tracking ID in your Venmo payment note.</p>
        <p>This helps us match your payment to your order.</p>
    </div>';
    
    // Add the payment button and info before the closing body tag
    $modified_content = str_replace('</body>', $payment_info . $venmo_button . '</body>', $email_content);
    
    return $modified_content;
}

// =========================================================================
// 7. DEBUG HELPER
// =========================================================================

/**
 * Debug helper for admin users
 */
function gatita_bakes_debug_shortcode() {
    // Only show to admins
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Only run on pages with the shortcode
    global $post;
    if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'gatita_bakes_order_form')) {
        return;
    }
    
    echo '<div style="background:#e8f4ff; border:1px solid #007bff; padding:15px; margin:20px 0; font-family:monospace;">';
    echo '<h3>Gatita Bakes Debug Info</h3>';
    
    // Check CSS files
    global $wp_styles;
    $required_styles = ['gatita-bakes-style', 'swiper-css'];
    echo '<h4>CSS Files</h4><ul>';
    foreach ($required_styles as $style) {
        if (isset($wp_styles->registered[$style])) {
            $file = $wp_styles->registered[$style]->src;
            echo '<li style="color:green;">✓ ' . $style . ' is registered - ' . $file . '</li>';
        } else {
            echo '<li style="color:red;">✗ ' . $style . ' is NOT registered</li>';
        }
    }
    echo '</ul>';
    
    // Check JS files
    global $wp_scripts;
    $required_scripts = ['jquery', 'swiper-js', 'gatita-bakes-cart'];
    echo '<h4>JavaScript Files</h4><ul>';
    foreach ($required_scripts as $script) {
        if (isset($wp_scripts->registered[$script])) {
            $file = $wp_scripts->registered[$script]->src;
            echo '<li style="color:green;">✓ ' . $script . ' is registered - ' . $file . '</li>';
        } else {
            echo '<li style="color:red;">✗ ' . $script . ' is NOT registered</li>';
        }
    }
    echo '</ul>';
    
    // Images directory check
    $images_dir = GATITA_BAKES_PLUGIN_DIR . 'assets/images/';
    echo '<h4>Images Directory</h4>';
    if (file_exists($images_dir) && is_dir($images_dir)) {
        echo '<p style="color:green;">✓ Images directory exists: ' . $images_dir . '</p>';
        // List image files
        $images = glob($images_dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        if (!empty($images)) {
            echo '<ul>';
            foreach ($images as $image) {
                $filename = basename($image);
                echo '<li>' . $filename . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p style="color:red;">✗ No image files found in directory</p>';
        }
    } else {
        echo '<p style="color:red;">✗ Images directory does not exist!</p>';
    }
    
    echo '<p><em>This debug info is only visible to site administrators.</em></p>';
    echo '</div>';
}
add_action('wp_footer', 'gatita_bakes_debug_shortcode');

// =========================================================================
// 8. PLUGIN ACTIVATION
// =========================================================================

/**
 * Plugin activation hook
 */
function gatita_bakes_activate() {
    // Create necessary database tables
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gatita_bakes_orders (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        order_number varchar(20) NOT NULL,
        customer_name varchar(100) NOT NULL,
        customer_email varchar(100) NOT NULL,
        customer_phone varchar(20) NOT NULL,
        order_type varchar(20) NOT NULL,
        pickup_location varchar(100),
        delivery_address text,
        order_items text NOT NULL,
        total_amount decimal(10,2) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY order_number (order_number)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Create assets directory if it doesn't exist
    $upload_dir = wp_upload_dir();
    $gatita_bakes_dir = $upload_dir['basedir'] . '/gatita-bakes';
    
    if (!file_exists($gatita_bakes_dir)) {
        wp_mkdir_p($gatita_bakes_dir);
    }
    
    // Set default options
    add_option('gatita_bakes_last_order_number', 1000);
    add_option('gatita_bakes_pickup_locations', array(
        'downtown' => 'Downtown Location',
        'uptown' => 'Uptown Location',
        'airport' => 'Airport Location'
    ));
}
register_activation_hook(__FILE__, 'gatita_bakes_activate');

/**
 * Plugin deactivation hook
 */
function gatita_bakes_deactivate() {
    // Clean up if needed
}
register_deactivation_hook(__FILE__, 'gatita_bakes_deactivate');

/**
 * Plugin uninstall hook
 */
function gatita_bakes_uninstall() {
    // Remove plugin data
    global $wpdb;
    
    // Drop tables
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}gatita_bakes_orders");
    
    // Remove options
    delete_option('gatita_bakes_last_order_number');
    delete_option('gatita_bakes_pickup_locations');
    
    // Remove uploaded files
    $upload_dir = wp_upload_dir();
    $gatita_bakes_dir = $upload_dir['basedir'] . '/gatita-bakes';
    
    if (file_exists($gatita_bakes_dir)) {
        array_map('unlink', glob("$gatita_bakes_dir/*.*"));
        rmdir($gatita_bakes_dir);
    }
}
register_uninstall_hook(__FILE__, 'gatita_bakes_uninstall');

// =========================================================================
// 9. STANDALONE ORDER PAGE
// =========================================================================

/**
 * Register custom page templates
 */
function gatita_bakes_register_templates($templates) {
    error_log('Registering Gatita Bakes templates');
    $templates['standalone-order.php'] = 'Order Form - Gatita Bakes';
    $templates['order-confirmation.php'] = 'Order Confirmation - Gatita Bakes';
    return $templates;
}
add_filter('theme_page_templates', 'gatita_bakes_register_templates');

/**
 * Override template hierarchy
 */
function gatita_bakes_template_hierarchy($template) {
    // Get the template slug
    $template_slug = basename($template);
    error_log('Template hierarchy check - Current template: ' . $template_slug);

    if (is_page()) {
        // Get the template name from page meta
        $page_template = get_page_template_slug();
        error_log('Page template slug: ' . $page_template);

        // Check if this is our standalone order template
        if ('standalone-order.php' === $page_template) {
            $plugin_template = plugin_dir_path(__FILE__) . 'templates/standalone-order.php';
            if (file_exists($plugin_template)) {
                error_log('Loading plugin standalone template: ' . $plugin_template);
                return $plugin_template;
            }
        }

        // Check if this is our confirmation template
        if ('order-confirmation.php' === $page_template) {
            $plugin_template = plugin_dir_path(__FILE__) . 'templates/order-confirmation.php';
            if (file_exists($plugin_template)) {
                error_log('Loading plugin confirmation template: ' . $plugin_template);
                return $plugin_template;
            }
        }
    }

    return $template;
}

// Add these filters to ensure our templates are loaded
add_filter('single_template', 'gatita_bakes_template_hierarchy', 999);
add_filter('page_template', 'gatita_bakes_template_hierarchy', 999);
add_filter('template_include', 'gatita_bakes_template_hierarchy', 999);

/**
 * Create templates directory and move templates on plugin activation
 */
function gatita_bakes_setup_templates() {
    // Create templates directory if it doesn't exist
    $templates_dir = plugin_dir_path(__FILE__) . 'templates';
    if (!file_exists($templates_dir)) {
        mkdir($templates_dir, 0755, true);
    }

    // Move standalone template
    $standalone_source = plugin_dir_path(__FILE__) . 'standalone-order.php';
    $standalone_dest = $templates_dir . '/standalone-order.php';
    if (file_exists($standalone_source)) {
        rename($standalone_source, $standalone_dest);
    }

    // Move confirmation template
    $confirmation_source = plugin_dir_path(__FILE__) . 'order-confirmation.php';
    $confirmation_dest = $templates_dir . '/order-confirmation.php';
    if (file_exists($confirmation_source)) {
        rename($confirmation_source, $confirmation_dest);
    }
}
register_activation_hook(__FILE__, 'gatita_bakes_setup_templates');

// =========================================================================
// END OF FILE
// =========================================================================

// Email Configuration - High priority filters
add_filter('wp_mail_from', 'gatita_bakes_sender_email', 99999);
add_filter('wp_mail_from_name', 'gatita_bakes_sender_name', 99999);
add_filter('pre_wp_mail', 'gatita_bakes_pre_wp_mail', 99999, 2);

function gatita_bakes_sender_email($original_email_address) {
    return 'orders@gatitabakes.com';
}

function gatita_bakes_sender_name($original_email_from) {
    return 'Gatita Bakes Orders';
}

function gatita_bakes_pre_wp_mail($null, $atts) {
    if (isset($atts['headers'])) {
        if (is_string($atts['headers'])) {
            $atts['headers'] = array($atts['headers']);
        }
        $atts['headers'][] = 'From: Gatita Bakes Orders <orders@gatitabakes.com>';
    } else {
        $atts['headers'] = array('From: Gatita Bakes Orders <orders@gatitabakes.com>');
    }
    return $atts;
}

function gatita_bakes_send_order_emails($order_data) {
    error_log('=== START: Gatita Bakes Email Sending ===');
    error_log('Order Number: ' . $order_data['order_number']);
    
    // Check for required configuration
    if (!defined('GATITA_SMTP_PASSWORD') || empty(GATITA_SMTP_PASSWORD)) {
        error_log('ERROR: SMTP password not configured. Please add GATITA_SMTP_PASSWORD to wp-config.php');
        return false;
    }

    // Remove any existing email filters to prevent conflicts
    remove_all_filters('wp_mail_from');
    remove_all_filters('wp_mail_from_name');
    remove_all_filters('phpmailer_init');
    
    // Add our high-priority filters
    add_filter('wp_mail_from', function($original) {
        return 'orders@gatitabakes.com';
    }, 99999);
    
    add_filter('wp_mail_from_name', function($original) {
        return 'Gatita Bakes Orders';
    }, 99999);

    // Configure PHPMailer
    add_filter('phpmailer_init', function($phpmailer) use ($order_data) {
        try {
            $phpmailer->isSMTP();
            $phpmailer->Host = 'smtp.gmail.com';
            $phpmailer->Port = 587;
            $phpmailer->SMTPAuth = true;
            $phpmailer->SMTPSecure = 'tls';
            $phpmailer->Username = 'orders@gatitabakes.com';
            $phpmailer->Password = GATITA_SMTP_PASSWORD;
            
            // Enable debug mode
            $phpmailer->SMTPDebug = 2;
            $phpmailer->Debugoutput = function($str, $level) {
                error_log("PHPMailer [$level]: $str");
            };

            // Set sender information
            $phpmailer->setFrom('orders@gatitabakes.com', 'Gatita Bakes Orders', true);
            
            // Add custom headers
            $phpmailer->addCustomHeader('X-Gatita-Order-ID', $order_data['order_number']);
            $phpmailer->addCustomHeader('X-Gatita-Environment', defined('WP_DEBUG') && WP_DEBUG ? 'Development' : 'Production');
            
            error_log('PHPMailer configured successfully');
            return $phpmailer;
            
        } catch (Exception $e) {
            error_log('PHPMailer configuration error: ' . $e->getMessage());
            return $phpmailer;
        }
    }, 99999);

    // Set up common headers
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: Gatita Bakes Orders <orders@gatitabakes.com>',
        'Reply-To: orders@gatitabakes.com'
    );

    // Send admin email
    $admin_email = 'gatitabakestest@bucketbranch.com';
    $admin_subject = 'New Order #' . $order_data['order_number'] . ' - Gatita Bakes';
    $admin_content = gatita_bakes_get_admin_email_content($order_data);
    
    error_log('Sending admin email to: ' . $admin_email);
    $admin_sent = wp_mail($admin_email, $admin_subject, $admin_content, $headers);
    error_log('Admin email ' . ($admin_sent ? 'sent successfully' : 'failed to send'));

    // Send customer email
    $customer_email = $order_data['customer_email'];
    $customer_subject = 'Order Confirmation #' . $order_data['order_number'] . ' - Gatita Bakes';
    $customer_content = gatita_bakes_get_customer_email_content($order_data);
    
    error_log('Sending customer email to: ' . $customer_email);
    $customer_sent = wp_mail($customer_email, $customer_subject, $customer_content, $headers);
    error_log('Customer email ' . ($customer_sent ? 'sent successfully' : 'failed to send'));

    error_log('=== END: Gatita Bakes Email Sending ===');
    return $admin_sent && $customer_sent;
}
?>