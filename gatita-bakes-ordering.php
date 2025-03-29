<?php
/**
 ** Plugin Name:      Gatita Bakes Ordering       ** 
 * Filename:          gatita-bakes-ordering.php
 * Plugin URI:        https://www.gatitabakes.com/
 * Description:       Custom ordering system for Gatita Bakes artisan bread and bagels
 * Version:           1.5.2
 * Author:            Bucketbranch
 * Author URI:        https://www.gatitabakes.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gatita-bakes-ordering
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('GATITA_BAKES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GATITA_BAKES_PLUGIN_URL', plugin_dir_url(__FILE__));

// =========================================================================
// 1. ENQUEUE STYLES & SCRIPTS
// =========================================================================
function gatita_bakes_enqueue_assets() {
    // --- Swiper JS ---
    wp_enqueue_style('swiper-css', 'https://unpkg.com/swiper@8/swiper-bundle.min.css', array(), '8.4.7');
    wp_enqueue_script('swiper-js', 'https://unpkg.com/swiper@8/swiper-bundle.min.js', array(), '8.4.7', true);

    // --- Plugin's Custom CSS ---
    $css_file_path = GATITA_BAKES_PLUGIN_DIR . 'assets/gatita-bakes.css';
    if (file_exists($css_file_path)) {
        $css_version = filemtime($css_file_path);
        wp_enqueue_style('gatita-bakes-style', GATITA_BAKES_PLUGIN_URL . 'assets/gatita-bakes.css', array('swiper-css'), $css_version);
    }

    // --- Plugin's Custom JS (Slider + Form Logic) ---
    $form_js_path = GATITA_BAKES_PLUGIN_DIR . 'assets/gatita-bakes-slider.js';
    if (file_exists($form_js_path)) {
         $js_version = filemtime($form_js_path);
         wp_enqueue_script(
            'gatita-bakes-form-logic', // Unique handle
            GATITA_BAKES_PLUGIN_URL . 'assets/gatita-bakes-slider.js',
            array('jquery', 'swiper-js'), // Depends on jQuery and Swiper JS
            $js_version,
            true // Load in footer
         );
    }
}
add_action('wp_enqueue_scripts', 'gatita_bakes_enqueue_assets');

// =========================================================================
// 2. DEFINE PRODUCTS & PICKUP LOCATIONS
// =========================================================================
function gatita_bakes_get_products() {
    // ** IMPORTANT **: Using TEMP image for last item - Remember to change back if image exists
    return array(
        'plain-sourdough' => array('name' => 'Plain Sourdough Loaf', 'description' => 'Classic tangy sourdough with a chewy crust.', 'price' => 8.00, 'image' => 'Plain-Sourdough-Loaf.jpg'),
        'rosemary-sourdough' => array('name' => 'Rosemary Sourdough Loaf', 'description' => 'Infused with fresh rosemary for an aromatic flavor.', 'price' => 9.00, 'image' => 'Rosemary-Sourdough-Loaf.png'),
        'everything-sourdough' => array('name' => 'Everything Sourdough Loaf', 'description' => 'Coated with a savory everything bagel seasoning.', 'price' => 9.50, 'image' => 'Everything-Sourdough-Loaf.jpg'),
        'other-sourdough' => array('name' => 'Specialty Sourdough', 'description' => 'Ask about our rotating weekly special flavor!', 'price' => 10.00, 'image' => 'Other-Sourdough-Loaf.jpg'),
        'plain-bagels' => array('name' => 'Plain Bagels (Set of 4)', 'description' => 'Traditional chewy bagels, perfect for toasting.', 'price' => 6.00, 'image' => 'Plain-Bagels.png'),
        'cheese-jalapeno-bagels' => array( 'name' => 'Cheese Jalapeño Bagels (Set of 4)', 'description' => 'Spicy jalapeños and melted cheese baked right in.', 'price' => 7.50, 'image' => 'Plain-Sourdough-Loaf.jpg' /* TEMP */ ),
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
add_shortcode('gatita_bakes_landing', 'gatita_bakes_landing_page_shortcode');

// =========================================================================
// 4. ORDER FORM SHORTCODE [gatita_bakes_order_form] - SHOPPING CART STYLE
// =========================================================================
function gatita_bakes_order_form_shortcode() {
    // --- Display Status Messages ---
    $output = '';
    if (isset($_GET['order_status'])) {
        if ($_GET['order_status'] === 'success') { 
            $output .= "<div class='gatita-notice gatita-notice-success'>Thank you for your order! Please check your email for confirmation and payment instructions.</div>"; 
        }
        elseif (strpos($_GET['order_status'], 'error') === 0) { 
            $error_message = 'Sorry, there was an error processing your order. Please check your details and try again or contact us directly.'; 
            $output .= "<div class='gatita-notice gatita-notice-error'>" . esc_html($error_message) . "</div>"; 
        }
    }

    $products = gatita_bakes_get_products();
    $pickup_locations = gatita_bakes_get_pickup_locations();

    // --- Start Form Output ---
    ob_start();
    ?>
    <form id="gatita-order-form" method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="gatita_bakes_submit_order">
        <?php wp_nonce_field('gatita_bakes_order_nonce', 'gatita_bakes_nonce'); ?>

        <h1 class="gatita-cart-title">YOUR BAG (<span id="cart-item-count">0</span> UNIT)</h1>
        
        <!-- Shipping notification -->
        <div class="gatita-shipping-notification">
            <div class="gatita-notification-icon">i</div>
            <div class="gatita-notification-text">
                <strong>ALMOST THERE!</strong>
                <p>You're only $20.00 away from free shipping.</p>
            </div>
        </div>
        
        <!-- Shipping information -->
        <div class="gatita-shipping-info">
            <div class="gatita-truck-icon">🚚</div>
            <p>Orders submitted by 11am PT are typically processed within 2 business days (M-F, excluding major holidays). Please factor in this processing time when selecting a shipping method.</p>
        </div>
        
        <!-- Cart items -->
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
                        <img src="<?php echo esc_url(GATITA_BAKES_PLUGIN_URL . 'assets/images/' . $product['image']); ?>" 
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

        <div class="gatita-order-details-wrapper">
            <!-- Right column: Order summary -->
            <div class="gatita-order-summary-area">
                <div class="gatita-summary-sticky-content">
                    <h2>Summary</h2>
                    
                    <!-- Promo Code -->
                    <div class="gatita-promo-code">
                        <label for="promo_code">PROMO CODE</label>
                        <div class="gatita-promo-input">
                            <input type="text" id="promo_code" name="promo_code" placeholder="Enter it Here">
                            <button type="button" id="apply-promo" class="gatita-apply-promo">APPLY</button>
                        </div>
                    </div>
                    
                    <!-- Order Totals -->
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
                    
                    <!-- Payment Options -->
                    <div class="gatita-payment-options">
                        <div class="gatita-venmo-option">
                            <p>Payment via Venmo: @karlathesourdoughstarta</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Left column: Customer details -->
            <div class="gatita-order-main-details">
                <!-- Product Selection -->
                <div class="gatita-product-selection">
                    <h2>Choose Your Baked Goods</h2>
                    <div class="swiper gatita-product-slider">
                        <div class="swiper-wrapper">
                            <?php foreach ($products as $slug => $product) : ?>
                                <div class="swiper-slide">
                                    <div class="gatita-product-card">
                                        <img src="<?php echo esc_url(GATITA_BAKES_PLUGIN_URL . 'assets/images/' . $product['image']); ?>" 
                                             alt="<?php echo esc_attr($product['name']); ?>">
                                        <h3><?php echo esc_html($product['name']); ?></h3>
                                        <p><?php echo isset($product['description']) ? esc_html($product['description']) : ''; ?></p>
                                        <p class="price">$<?php echo esc_html(number_format((float)$product['price'], 2)); ?></p>
                                        <div class="gatita-product-order-controls">
                                            <button type="button" class="gatita-add-to-cart" 
                                                    data-product="<?php echo esc_attr($slug); ?>">Add to Cart</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                </div>
            
                <div class="gatita-details-section">
                    <h2>Your Details</h2>
                    <div class="gatita-form-row">
                        <label for="customer_first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="customer_first_name" name="customer_first_name" required>
                    </div>
                    <div class="gatita-form-row">
                        <label for="customer_last_name">Last Name <span class="required">*</span></label>
                        <input type="text" id="customer_last_name" name="customer_last_name" required>
                    </div>
                    <div class="gatita-form-row">
                        <label for="customer_email">Email <span class="required">*</span></label>
                        <input type="email" id="customer_email" name="customer_email" required>
                    </div>
                    <div class="gatita-form-row">
                        <label for="customer_phone">Phone</label>
                        <input type="tel" id="customer_phone" name="customer_phone">
                    </div>
                </div>

                <div class="gatita-details-section">
                    <h2>Order Method <span class="required">*</span></h2>
                    <div class="gatita-form-row gatita-radio-group">
                        <label><input type="radio" name="order_type" value="pickup" checked required> Pickup</label>
                        <label><input type="radio" name="order_type" value="delivery" required> Delivery</label>
                    </div>
                    
                    <!-- Pickup Location Fields -->
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
                    
                    <!-- Delivery Address Fields -->
                    <div id="delivery-address-fields" class="gatita-form-section-dynamic">
                        <h3>Delivery Address <span class="required">*</span></h3>
                        <p><small>(Required for delivery orders)</small></p>
                        <div class="gatita-form-row">
                            <label for="delivery_street">Street Address <span class="required">*</span></label>
                            <input type="text" id="delivery_street" name="delivery_street">
                        </div>
                        <div class="gatita-form-row">
                            <label for="delivery_city">City <span class="required">*</span></label>
                            <input type="text" id="delivery_city" name="delivery_city" value="">
                        </div>
                        <div class="gatita-form-row">
                            <label for="delivery_zip">ZIP Code <span class="required">*</span></label>
                            <input type="text" id="delivery_zip" name="delivery_zip">
                        </div>
                        <div class="gatita-form-row">
                            <label for="delivery_notes">Delivery Notes (optional)</label>
                            <textarea id="delivery_notes" name="delivery_notes"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php
    $output .= ob_get_clean();
    return $output;
}
add_shortcode('gatita_bakes_order_form', 'gatita_bakes_order_form_shortcode');

// =========================================================================
// 5. FORM SUBMISSION HANDLER
// =========================================================================
function gatita_bakes_handle_form_submission() {
    // Get next order number
    $order_number = get_option('gatita_bakes_last_order_number', 1000);
    $order_number++; // Increment order number
    update_option('gatita_bakes_last_order_number', $order_number);
    
    // Verify nonce
    if (!isset($_POST['gatita_bakes_nonce']) || !wp_verify_nonce($_POST['gatita_bakes_nonce'], 'gatita_bakes_order_nonce')) {
        wp_safe_redirect(add_query_arg('order_status', 'error_nonce', home_url('/order')));
        exit;
    }
    
    // Sanitize basic info
    $customer_first_name = isset($_POST['customer_first_name']) ? sanitize_text_field(trim($_POST['customer_first_name'])) : '';
    $customer_last_name = isset($_POST['customer_last_name']) ? sanitize_text_field(trim($_POST['customer_last_name'])) : '';
    $customer_name = $customer_first_name . ' ' . $customer_last_name;
    $customer_email = isset($_POST['customer_email']) ? sanitize_email($_POST['customer_email']) : '';
    $customer_phone = isset($_POST['customer_phone']) ? sanitize_text_field(trim($_POST['customer_phone'])) : '';
    $order_type = isset($_POST['order_type']) ? sanitize_key($_POST['order_type']) : 'pickup';
    
    // Initialize variables
    $delivery_address_html = 'N/A';
    $pickup_location_text = 'N/A';
    $all_locations = gatita_bakes_get_pickup_locations();
    
    // Validate required basic info
    if (empty($customer_first_name) || empty($customer_last_name) || !is_email($customer_email)) {
        wp_safe_redirect(add_query_arg('order_status', 'error_required', home_url('/order')));
        exit;
    }
    
    // --- Validate and Process Based on Order Type ---
    if ($order_type === 'delivery') {
        $delivery_street = isset($_POST['delivery_street']) ? sanitize_text_field(trim($_POST['delivery_street'])) : '';
        $delivery_city = isset($_POST['delivery_city']) ? sanitize_text_field(trim($_POST['delivery_city'])) : '';
        $delivery_zip = isset($_POST['delivery_zip']) ? sanitize_text_field(trim($_POST['delivery_zip'])) : '';
        $delivery_notes = isset($_POST['delivery_notes']) ? sanitize_textarea_field($_POST['delivery_notes']) : '';
        
        if (empty($delivery_street) || empty($delivery_city) || empty($delivery_zip)) {
            wp_safe_redirect(add_query_arg('order_status', 'error_address', home_url('/order')));
            exit;
        }
        
        $delivery_address_html = nl2br(esc_html(implode("\n", array_filter([$delivery_street, $delivery_city . ", " . $delivery_zip]))));
        if (!empty($delivery_notes)) {
            $delivery_address_html .= "<br><small>Notes: " . nl2br(esc_html($delivery_notes)) . "</small>";
        }
    } elseif ($order_type === 'pickup') {
        $selected_location_key = isset($_POST['pickup_location']) ? sanitize_key($_POST['pickup_location']) : '';
        if (empty($selected_location_key) || !isset($all_locations[$selected_location_key])) {
            wp_safe_redirect(add_query_arg('order_status', 'error_pickup_loc', home_url('/order')));
            exit;
        }
        $pickup_location_text = esc_html($all_locations[$selected_location_key]);
    }
    
    // Process Ordered Products
    $products_available = gatita_bakes_get_products();
    $ordered_items_data = array();
    $order_items_html = '';
    $order_total = 0.00;
    
    if (isset($_POST['quantity']) && is_array($_POST['quantity']) && !empty($products_available)) {
        foreach ($_POST['quantity'] as $slug => $quantity) {
            // Only process products with quantity > 0
            $quantity = intval($quantity);
            if ($quantity > 0 && isset($products_available[$slug])) {
                $product = $products_available[$slug];
                if (isset($product['price']) && is_numeric($product['price'])) {
                    $item_total = (float)$product['price'] * $quantity;
                    $order_total += $item_total;
                    $item_name = isset($product['name']) ? $product['name'] : $slug;
                    
                    $ordered_items_data[] = array(
                        'name' => $item_name,
                        'quantity' => $quantity,
                        'price_per_item' => (float)$product['price'],
                        'item_total' => $item_total
                    );
                    
                    $order_items_html .= "<tr><td style='padding: 8px; border: 1px solid #ddd;'>" . esc_html($item_name) . 
                                         "</td><td style='padding: 8px; border: 1px solid #ddd; text-align: center;'>" . 
                                         esc_html($quantity) . "</td><td style='padding: 8px; border: 1px solid #ddd; text-align: right;'>$" . 
                                         esc_html(number_format($item_total, 2)) . "</td></tr>";
                }
            }
        }
    }
    
    if (empty($ordered_items_data)) {
        wp_safe_redirect(add_query_arg('order_status', 'error_noitems', home_url('/order')));
        exit;
    }
    
    // Prepare Email
    $admin_email = get_option('admin_email');
    $email_subject = 'New Gatita Bakes Order #' . $order_number . ' - ' . $customer_name;
    $customer_email_subject = 'Your Gatita Bakes Order #' . $order_number . ' Confirmation';
    $email_template_path = GATITA_BAKES_PLUGIN_DIR . 'email-templates.php';
    
    if (file_exists($email_template_path)) {
        ob_start();
        // Variables available to email template
        $email_order_number = $order_number;
        $email_customer_name = $customer_name;
        $email_customer_email = $customer_email;
        $email_customer_phone = $customer_phone;
        $email_order_type = ucfirst($order_type);
        $email_delivery_address_html = ($order_type === 'delivery') ? $delivery_address_html : 'N/A';
        $email_pickup_location_text = ($order_type === 'pickup') ? $pickup_location_text : 'N/A';
        $email_order_items_html = $order_items_html;
        $email_order_total = number_format($order_total, 2);
        
        include $email_template_path;
        $email_body = ob_get_clean();
        
        // Send Emails
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $from_name = 'Gatita Bakes';
        $from_email = $admin_email;
        $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
        
        wp_mail($admin_email, $email_subject, $email_body, $headers);
        wp_mail($customer_email, $customer_email_subject, $email_body, $headers);
    } else {
        wp_safe_redirect(add_query_arg('order_status', 'error_email_template', home_url('/order')));
        exit;
    }
    
    // Redirect after successful submission
    wp_safe_redirect(add_query_arg('order_status', 'success', home_url('/order')));
    exit;
}
add_action('admin_post_nopriv_gatita_bakes_submit_order', 'gatita_bakes_handle_form_submission');
add_action('admin_post_gatita_bakes_submit_order', 'gatita_bakes_handle_form_submission');
?>