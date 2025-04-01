<?php
/**
 ** Plugin Name:      Gatita Bakes Ordering       **
 * Filename:          gatita-bakes-ordering.php
 * Plugin URI:        https://www.gatitabakes.com/
 * Description:       Custom ordering system for Gatita Bakes artisan bread and bagels
 * Version:           1.8.1 // "Fix order form display in Twenty Twenty-Four theme"
 * Author:            Bucketbranch
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

// =========================================================================
// 1. ENQUEUE STYLES & SCRIPTS
// =========================================================================
function gatita_bakes_enqueue_assets() {
    // --- Swiper JS ---
    wp_enqueue_style('swiper-css', 'https://unpkg.com/swiper@8/swiper-bundle.min.css', array(), '8.4.7');
    wp_enqueue_script('swiper-js', 'https://unpkg.com/swiper@8/swiper-bundle.min.js', array('jquery'), '8.4.7', true);

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
    
    // --- Cart JS ---
    $cart_js_path = GATITA_BAKES_PLUGIN_DIR . 'assets/gatita-bakes-cart.js';
    if (file_exists($cart_js_path)) {
        $cart_js_version = filemtime($cart_js_path);
        wp_enqueue_script(
            'gatita-bakes-cart',
            GATITA_BAKES_PLUGIN_URL . 'assets/gatita-bakes-cart.js',
            array('jquery'),
            $cart_js_version,
            true
        );
    } else {
        // If the cart JS file doesn't exist, add inline cart functionality
        wp_add_inline_script('jquery', "
            jQuery(document).ready(function($) {
                // Basic cart functionality
                $('.gatita-add-to-cart').on('click', function() {
                    var productId = $(this).data('product');
                    $('#cart-item-' + productId).show();
                    
                    // Update items count and totals
                    updateCartCounts();
                    updateCartTotals();
                });
                
                $('.gatita-remove-item').on('click', function() {
                    $(this).closest('.gatita-cart-item').hide();
                    
                    // Update items count and totals
                    updateCartCounts();
                    updateCartTotals();
                });
                
                $('.gatita-quantity-input').on('change', function() {
                    updateCartTotals();
                });
                
                function updateCartCounts() {
                    var visibleItems = $('.gatita-cart-item:visible').length;
                    $('#cart-item-count').text(visibleItems);
                }
                
                function updateCartTotals() {
                    var subtotal = 0;
                    
                    $('.gatita-cart-item:visible').each(function() {
                        var price = parseFloat($(this).data('price'));
                        var quantity = parseInt($(this).find('.gatita-quantity-input').val());
                        subtotal += price * quantity;
                    });
                    
                    $('.gatita-subtotal-amount').text('$' + subtotal.toFixed(2));
                    $('.gatita-total-amount').text('$' + subtotal.toFixed(2));
                }
                
                // Order type toggle (pickup/delivery)
                $('input[name=\"order_type\"]').on('change', function() {
                    var selectedType = $('input[name=\"order_type\"]:checked').val();
                    
                    if (selectedType === 'pickup') {
                        $('#pickup-location-fields').addClass('visible');
                        $('#delivery-address-fields').removeClass('visible');
                    } else if (selectedType === 'delivery') {
                        $('#pickup-location-fields').removeClass('visible');
                        $('#delivery-address-fields').addClass('visible');
                    }
                });
                
                // Initialize order type fields
                $('input[name=\"order_type\"]:checked').trigger('change');
            });
        ");
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
    // Make sure scripts are properly loaded for this page
    gatita_bakes_enqueue_assets();
    
    // Add basic inline CSS to ensure form displays correctly
    $inline_css = "
        .gatita-form-section-dynamic { display: none; }
        .gatita-form-section-dynamic.visible { display: block; }
        .gatita-order-details-wrapper { display: flex; flex-wrap: wrap; gap: 20px; }
        .gatita-order-main-details { flex: 2; min-width: 300px; }
        .gatita-order-summary-area { flex: 1; min-width: 250px; background: #f9f9f9; padding: 15px; border-radius: 5px; }
        .gatita-cart-item { display: flex; padding: 15px; margin-bottom: 15px; border-bottom: 1px solid #eee; }
        .gatita-product-image { width: 80px; margin-right: 15px; }
        .gatita-product-image img { max-width: 100%; height: auto; }
        .gatita-product-details { flex: 1; }
        .gatita-product-card { padding: 15px; border: 1px solid #eee; margin-bottom: 20px; text-align: center; }
        .gatita-product-card img { max-width: 100%; height: auto; margin-bottom: 10px; }
        .gatita-form-row { margin-bottom: 15px; }
        .gatita-form-row label { display: block; margin-bottom: 5px; }
        .gatita-form-row input, .gatita-form-row select, .gatita-form-row textarea { width: 100%; padding: 8px; }
        .required { color: red; }
        .gatita-place-order-button { display: block; width: 100%; background: #e5a98c; color: white; border: none; padding: 10px; border-radius: 3px; cursor: pointer; }
    ";
    wp_add_inline_style('gatita-bakes-style', $inline_css);
    
    // Display status messages
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

    // Get product data and pickup locations
    $products = gatita_bakes_get_products();
    $pickup_locations = gatita_bakes_get_pickup_locations();

    // Start building the form HTML
    ob_start();
    ?>
    <div class="gatita-order-form-container">
        <form id="gatita-order-form" method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="gatita_bakes_submit_order">
            <?php wp_nonce_field('gatita_bakes_order_nonce', 'gatita_bakes_nonce'); ?>

            <h2 class="gatita-cart-title">YOUR BAG (<span id="cart-item-count">0</span> ITEMS)</h2>
            
            <!-- Cart items -->
            <div class="gatita-cart-items">
                <?php 
                // Loop through all available products to create hidden cart item rows
                foreach ($products as $slug => $product) : 
                ?>
                    <!-- Each cart item - initially hidden -->
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
                                       class="gatita-quantity-input" value="1" min="1" max="10" style="width: 50px;">
                                <button type="button" class="gatita-quantity-increase">+</button>
                                <button type="button" class="gatita-remove-item">Remove</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="gatita-order-details-wrapper">
                <!-- Left column: Customer details -->
                <div class="gatita-order-main-details">
                    <!-- Product Selection -->
                    <div class="gatita-product-selection">
                        <h2>Choose Your Baked Goods</h2>
                        
                        <!-- Simple product grid -->
                        <div class="gatita-products-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
                            <?php foreach ($products as $slug => $product) : ?>
                                <div class="gatita-product-card">
                                    <img src="<?php echo esc_url(GATITA_BAKES_PLUGIN_URL . 'assets/images/' . $product['image']); ?>" 
                                         alt="<?php echo esc_attr($product['name']); ?>">
                                    <h3><?php echo esc_html($product['name']); ?></h3>
                                    <p><?php echo isset($product['description']) ? esc_html($product['description']) : ''; ?></p>
                                    <p class="price">$<?php echo esc_html(number_format((float)$product['price'], 2)); ?></p>
                                    <button type="button" class="gatita-add-to-cart" 
                                            data-product="<?php echo esc_attr($slug); ?>">Add to Cart</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                
                    <div class="gatita-details-section" style="margin-top: 30px;">
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

                    <div class="gatita-details-section" style="margin-top: 30px;">
                        <h2>Order Method <span class="required">*</span></h2>
                        <div class="gatita-form-row gatita-radio-group">
                            <label style="display: inline-block; margin-right: 20px;">
                                <input type="radio" name="order_type" value="pickup" checked required> Pickup
                            </label>
                            <label style="display: inline-block;">
                                <input type="radio" name="order_type" value="delivery" required> Delivery
                            </label>
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
                
                <!-- Right column: Order summary -->
                <div class="gatita-order-summary-area">
                    <div class="gatita-summary-sticky-content">
                        <h2>Order Summary</h2>
                        
                        <!-- Order Totals -->
                        <div class="gatita-order-totals">
                            <div class="gatita-total-row" style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span>SUBTOTAL</span>
                                <span class="gatita-subtotal-amount">$0.00</span>
                            </div>
                            <div class="gatita-total-row" style="display: flex; justify-content: space-between; margin-bottom: 20px; border-top: 1px solid #ddd; padding-top: 10px;">
                                <span>ORDER TOTAL</span>
                                <span class="gatita-total-amount">$0.00</span>
                            </div>
                        </div>
                        
                        <!-- Checkout Button -->
                        <button type="submit" class="gatita-place-order-button">PLACE ORDER</button>
                        
                        <!-- Payment Options -->
                        <div class="gatita-payment-options" style="margin-top: 20px; font-size: 14px;">
                            <div class="gatita-venmo-option">
                                <p>Payment via Venmo: @karlathesourdoughstarta</p>
                                <p>Payment instructions will be sent with your order confirmation.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
    // Get the buffered content and return it
    $form_html = ob_get_clean();
    
    // Return the complete output
    return $output . $form_html;
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
        
        // Enhance email with Venmo payment details
        $email_body = gatita_bakes_add_venmo_to_email($email_body, array(
            'order_number' => $order_number,
            'order_total' => $email_order_total,
            'customer_name' => $customer_name
        ));
        
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
    $venmo_username = 'karlathesourdoughstarta'; // Update if needed
    $venmo_amount = $order_data['order_total'];
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
    $required_scripts = ['jquery', 'swiper-js', 'gatita-bakes-form-logic', 'gatita-bakes-cart'];
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
 * Actions performed on plugin activation
 */
function gatita_bakes_plugin_activation() {
    // Create assets directory if it doesn't exist
    $assets_dir = GATITA_BAKES_PLUGIN_DIR . 'assets';
    if (!file_exists($assets_dir)) {
        wp_mkdir_p($assets_dir);
    }
    
    // Create images directory if it doesn't exist
    $images_dir = $assets_dir . '/images';
    if (!file_exists($images_dir)) {
        wp_mkdir_p($images_dir);
    }
    
    // Create CSS file if it doesn't exist
    $css_file = $assets_dir . '/gatita-bakes.css';
    if (!file_exists($css_file)) {
        $css_content = "

/* Base styles for Gatita Bakes ordering system */
.gatita-form-section-dynamic {
    display: none;
}
.gatita-form-section-dynamic.visible {
    display: block;
}
.gatita-order-details-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 30px;
}
.gatita-order-main-details {
    flex: 2;
    min-width: 300px;
}
.gatita-order-summary-area {
    flex: 1;
    min-width: 250px;
    background: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
}
.gatita-cart-item {
    display: flex;
    padding: 15px;
    margin-bottom: 15px;
    border-bottom: 1px solid #eee;
}
.gatita-product-image {
    width: 80px;
    margin-right: 15px;
}
.gatita-product-image img {
    max-width: 100%;
    height: auto;
}
.gatita-product-details {
    flex: 1;
}
.gatita-product-card {
    padding: 15px;
    border: 1px solid #eee;
    margin-bottom: 20px;
    text-align: center;
}
.gatita-product-card img {
    max-width: 100%;
    height: auto;
    margin-bottom: 10px;
}
.gatita-form-row {
    margin-bottom: 15px;
}
.gatita-form-row label {
    display: block;
    margin-bottom: 5px;
}
.gatita-form-row input, 
.gatita-form-row select, 
.gatita-form-row textarea {
    width: 100%;
    padding: 8px;
}
.required {
    color: red;
}
.gatita-place-order-button {
    display: block;
    width: 100%;
    background: #e5a98c;
    color: white;
    border: none;
    padding: 10px;
    border-radius: 3px;
    cursor: pointer;
}

/* Notice styles */
.gatita-notice {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
}
.gatita-notice-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.gatita-notice-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .gatita-order-details-wrapper {
        flex-direction: column;
    }
    .gatita-order-main-details {
        order: 2;
    }
    .gatita-order-summary-area {
        order: 1;
        margin-bottom: 20px;
    }
}";
        file_put_contents($css_file, $css_content);
    }
    
    // Create Cart JS file if it doesn't exist
    $cart_js_file = $assets_dir . '/gatita-bakes-cart.js';
    if (!file_exists($cart_js_file)) {
        $cart_js_content = "

jQuery(document).ready(function($) {
    // Initialize cart functionality
    const cart = {
        items: {},
        
        // Add item to cart
        addItem: function(productId) {
            // Check if item already exists in cart
            if (this.items[productId]) {
                this.items[productId].quantity++;
            } else {
                const productCard = $(`.gatita-product-card button[data-product=\"${productId}\"]`).closest('.gatita-product-card');
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
            $('.gatita-subtotal-amount').text('$' + subtotal.toFixed(2));
            $('.gatita-tax-amount').text('$' + tax.toFixed(2));
            $('.gatita-total-amount').text('$' + total.toFixed(2));
        }
    };
    
    // Event Listeners
    
    // Add to cart buttons
    $('.gatita-add-to-cart').click(function() {
        const productId = $(this).data('product');
        cart.addItem(productId);
    });
    
    // Quantity decrease in cart
    $(document).on('click', '.gatita-quantity-decrease', function() {
        const cartItem = $(this).closest('.gatita-cart-item');
        const productId = cartItem.data('product-id');
        const quantityInput = cartItem.find('.gatita-quantity-input');
        const currentQty = parseInt(quantityInput.val());
        
        if (currentQty > 1) {
            cart.updateQuantity(productId, currentQty - 1);
        }
    });
    
    // Quantity increase in cart
    $(document).on('click', '.gatita-quantity-increase', function() {
        const cartItem = $(this).closest('.gatita-cart-item');
        const productId = cartItem.data('product-id');
        const quantityInput = cartItem.find('.gatita-quantity-input');
        const currentQty = parseInt(quantityInput.val());
        
        cart.updateQuantity(productId, currentQty + 1);
    });
    
    // Quantity input changes
    $(document).on('change', '.gatita-quantity-input', function() {
        const cartItem = $(this).closest('.gatita-cart-item');
        const productId = cartItem.data('product-id');
        cart.updateQuantity(productId, $(this).val());
    });
    
    // Remove item from cart
    $(document).on('click', '.gatita-remove-item', function() {
        const cartItem = $(this).closest('.gatita-cart-item');
        const productId = cartItem.data('product-id');
        cart.removeItem(productId);
    });
    
    // Order type toggle (pickup/delivery)
    $('input[name=\"order_type\"]').on('change', function() {
        const selectedType = $('input[name=\"order_type\"]:checked').val();
        
        if (selectedType === 'pickup') {
            $('#pickup-location-fields').addClass('visible');
            $('#delivery-address-fields').removeClass('visible');
            
            // Toggle required attributes
            $('#pickup_location').prop('required', true);
            $('#delivery_street, #delivery_city, #delivery_zip').prop('required', false);
        } else if (selectedType === 'delivery') {
            $('#pickup-location-fields').removeClass('visible');
            $('#delivery-address-fields').addClass('visible');
            
            // Toggle required attributes
            $('#pickup_location').prop('required', false);
            $('#delivery_street, #delivery_city, #delivery_zip').prop('required', true);
        }
    });
    
    // Initialize order type fields
    $('input[name=\"order_type\"]:checked').trigger('change');
    
    // Form validation before submission
    $('#gatita-order-form').on('submit', function(e) {
        const itemCount = Object.keys(cart.items).length;
        if (itemCount === 0) {
            e.preventDefault();
            alert('Please add at least one item to your cart before proceeding.');
        }
    });
});";
        file_put_contents($cart_js_file, $cart_js_content);
    }
    
    // Create email template file if it doesn't exist
    $email_template_file = GATITA_BAKES_PLUGIN_DIR . 'email-templates.php';
    if (!file_exists($email_template_file)) {
        $email_template_content = "

/**
 * Variables available:
 * \$email_customer_name
 * \$email_customer_email
 * \$email_customer_phone
 * \$email_order_type             (e.g., \"Pickup\" or \"Delivery\")
 * \$email_pickup_location_text   (Text of selected location, or 'N/A')
 * \$email_delivery_address_html  (Formatted HTML address, or 'N/A')
 * \$email_order_items_html       (HTML <tr> elements for the items table)
 * \$email_order_total            (Formatted total price string, e.g., \"25.50\")
 * \$email_order_number           (Order number)
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Set colors and styles
\$bg_color = '#fdfaf7';
\$body_color = '#ffffff';
\$text_color = '#5a4e46';
\$brand_color = '#e5a98c';
\$font_family = 'Arial, Helvetica, sans-serif';
?>
<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Your Gatita Bakes Order</title>
    <style>
        /* Basic styles */
        body { margin: 0; padding: 0; background-color: <?php echo \$bg_color; ?>; font-family: <?php echo \$font_family; ?>; }
        table { border-collapse: collapse; width: 100%; }
        td { padding: 0; }
        img { border: 0; display: block; outline: none; text-decoration: none; }
        
        /* Layout */
        .content-table { width: 100%; max-width: 600px; margin: 0 auto; background-color: <?php echo \$body_color; ?>; border-radius: 8px; overflow: hidden; }
        .header { background-color: <?php echo \$brand_color; ?>; padding: 20px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; }
        .body-content { padding: 30px; color: <?php echo \$text_color; ?>; font-size: 16px; line-height: 1.6; }
        .body-content p { margin: 0 0 1em 0; }
        
        /* Order details table */
        .order-details th, .order-details td { padding: 12px 15px; border: 1px solid #eee; text-align: left; }
        .order-details th { background-color: #f9f9f9; font-weight: bold; }
        .order-details .total-row td { border-top: 2px solid #ddd; font-weight: bold; font-size: 1.1em; }
        .order-details .item-qty { text-align: center; }
        .order-details .item-price { text-align: right; }
        
        /* Venmo button */
        .venmo-button { display: inline-block; background-color: #0074de; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }
        
        /* Footer */
        .footer { background-color: #f2f2f2; padding: 20px; text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <table role=\"presentation\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"background-color: <?php echo \$bg_color; ?>;\">
        <tr>
            <td align=\"center\" style=\"padding: 20px 0;\">
                <!-- Main Content Table -->
                <table role=\"presentation\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"content-table\">
                    <!-- Header -->
                    <tr>
                        <td class=\"header\">
                            <h1>Gatita Bakes Order #<?php echo esc_html(\$email_order_number); ?></h1>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td class=\"body-content\">
                            <p>Hi <?php echo esc_html(\$email_customer_name); ?>,</p>
                            <p>Thank you for your order! Here are the details:</p>

                            <h2 style=\"color: <?php echo \$brand_color; ?>; margin-top: 30px; margin-bottom: 15px;\">Order Summary</h2>
                            <table role=\"presentation\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" class=\"order-details\">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th style=\"text-align: center;\">Qty</th>
                                        <th style=\"text-align: right;\">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php echo \$email_order_items_html; // Output the table rows generated by PHP ?>
                                </tbody>
                                <tfoot>
                                    <tr class=\"total-row\">
                                        <td colspan=\"2\" style=\"text-align: right;\"><strong>Total:</strong></td>
                                        <td style=\"text-align: right;\"><strong>\$<?php echo esc_html(\$email_order_total); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <h2 style=\"color: <?php echo \$brand_color; ?>; margin-top: 30px; margin-bottom: 15px;\">Your Information</h2>
                            <table role=\"presentation\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
                                <tr>
                                    <td style=\"padding: 5px 0;\"><strong>Name:</strong></td>
                                    <td style=\"padding: 5px 0;\"><?php echo esc_html(\$email_customer_name); ?></td>
                                </tr>
                                <tr>
                                    <td style=\"padding: 5px 0;\"><strong>Email:</strong></td>
                                    <td style=\"padding: 5px 0;\"><?php echo esc_html(\$email_customer_email); ?></td>
                                </tr>
                                <tr>
                                    <td style=\"padding: 5px 0;\"><strong>Phone:</strong></td>
                                    <td style=\"padding: 5px 0;\"><?php echo esc_html(\$email_customer_phone); ?></td>
                                </tr>
                                <tr>
                                    <td style=\"padding: 5px 0;\"><strong>Order Type:</strong></td>
                                    <td style=\"padding: 5px 0;\"><?php echo esc_html(\$email_order_type); ?></td>
                                </tr>

                                <?php if (\$email_order_type === 'Pickup' && \$email_pickup_location_text !== 'N/A') : ?>
                                    <tr>
                                        <td style=\"padding: 5px 0;\"><strong>Pickup Location:</strong></td>
                                        <td style=\"padding: 5px 0;\"><?php echo \$email_pickup_location_text; // Already escaped in PHP handler ?></td>
                                    </tr>
                                <?php elseif (\$email_order_type === 'Delivery' && \$email_delivery_address_html !== 'N/A') : ?>
                                    <tr>
                                        <td style=\"padding: 5px 0; vertical-align: top;\"><strong>Delivery Address:</strong></td>
                                        <td style=\"padding: 5px 0;\"><?php echo \$email_delivery_address_html; // Contains HTML <br> tags, already escaped in handler ?></td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                            
                            <h2 style=\"color: <?php echo \$brand_color; ?>; margin-top: 30px; margin-bottom: 15px;\">Payment Instructions</h2>
                            <p>Please complete your payment of <strong>\$<?php echo esc_html(\$email_order_total); ?></strong> via Venmo.</p>
                            <p>You can find us at Venmo username: <strong>@karlathesourdoughstarta</strong></p>
                            <p>Please include your order number <strong>#<?php echo esc_html(\$email_order_number); ?></strong> in the Venmo payment note.</p>
                            
                            <p>We'll confirm once payment is received. Thanks again for supporting Gatita Bakes!</p>
                            <p>If you have any questions, please contact us at <a href=\"mailto:gatitabakestest@bucketbranch.com\">gatitabakestest@bucketbranch.com</a>.</p>
                            <p style=\"margin-top: 30px; font-style: italic; text-align: center;\">'The smell of fresh bread is the best kind of welcome.';</p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td class=\"footer\">
                            © <?php echo date('Y'); ?> Gatita Bakes. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>";
        file_put_contents($email_template_file, $email_template_content);
    }
    
    // Create necessary option for order numbers
    if (!get_option('gatita_bakes_last_order_number')) {
        add_option('gatita_bakes_last_order_number', 1000);
    }
}
register_activation_hook(__FILE__, 'gatita_bakes_plugin_activation');
