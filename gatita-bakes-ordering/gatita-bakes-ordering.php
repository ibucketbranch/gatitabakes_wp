<?php
/**
 ** Plugin Name:      Gatita Bakes Ordering       **
 * Filename:          gatita-bakes-ordering.php
 * Plugin URI:        https://www.gatitabakes.com/
 * Description:       Custom ordering system for Gatita Bakes artisan bread and bagels
 * Version:           1.7.9 // Restored Form Shortcode Logic
 * Author:            Bucketbranch
 * Author URI:        https://www.gatitabakes.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gatita-bakes-ordering
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'GATITA_BAKES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GATITA_BAKES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// =========================================================================
// 1. ENQUEUE STYLES & SCRIPTS
// =========================================================================
function gatita_bakes_enqueue_assets() {
    // --- Swiper JS ---
    wp_enqueue_style('swiper-css', 'https://unpkg.com/swiper@8/swiper-bundle.min.css', array(), '8.4.7');
    wp_enqueue_script('swiper-js', 'https://unpkg.com/swiper@8/swiper-bundle.min.js', array(), '8.4.7', true);

    // --- Plugin's Custom CSS ---
    $css_file_path = GATITA_BAKES_PLUGIN_DIR . 'assets/gatita-bakes.css';
    if ( file_exists( $css_file_path ) ) {
        $css_version = filemtime( $css_file_path ); // Cache bust during dev
        wp_enqueue_style('gatita-bakes-style', GATITA_BAKES_PLUGIN_URL . 'assets/gatita-bakes.css', array('swiper-css'), $css_version);
    }

    // --- Plugin's Custom JS (Slider + Form Logic) ---
    $form_js_path = GATITA_BAKES_PLUGIN_DIR . 'assets/gatita-bakes-slider.js';
    if ( file_exists( $form_js_path ) ) {
         $js_version = filemtime( $form_js_path ); // Cache bust during dev
         wp_enqueue_script(
            'gatita-bakes-form-logic', // Unique handle
            GATITA_BAKES_PLUGIN_URL . 'assets/gatita-bakes-slider.js',
            array('swiper-js'), // Depends on Swiper JS
            $js_version,
            true // Load in footer
         );
    }
} // End gatita_bakes_enqueue_assets() function
add_action( 'wp_enqueue_scripts', 'gatita_bakes_enqueue_assets' );

// =========================================================================
// 2. DEFINE PRODUCTS & PICKUP LOCATIONS
// =========================================================================
function gatita_bakes_get_products() {
    // ** Ensure image filenames EXACTLY match files in /assets/images/ folder (CASE-SENSITIVE) **
    return array(
        'plain-sourdough' => array('name' => 'Plain Sourdough Loaf','description' => 'Classic tangy sourdough with a chewy crust.','price' => 8.00,'image' => 'Plain-Sourdough-Loaf.jpg'),
        'rosemary-sourdough' => array('name' => 'Rosemary Sourdough Loaf','description' => 'Infused with fresh rosemary for an aromatic flavor.','price' => 9.00,'image' => 'Rosemary-Sourdough-Loaf.png'),
        'everything-sourdough' => array('name' => 'Everything Sourdough Loaf','description' => 'Coated with a savory everything bagel seasoning.','price' => 9.50,'image' => 'Everything-Sourdough-Loaf.jpg'),
        'other-sourdough' => array('name' => 'Specialty Sourdough','description' => 'Ask about our rotating weekly special flavor!','price' => 10.00,'image' => 'Other-Sourdough-Loaf.jpg'),
        'plain-bagels' => array('name' => 'Plain Bagels (Set of 4)','description' => 'Traditional chewy bagels, perfect for toasting.','price' => 6.00,'image' => 'Plain-Bagels.png'),
        'cheese-jalapeno-bagels' => array('name' => 'Cheese Jalapeño Bagels (Set of 4)','description' => 'Spicy jalapeños and melted cheese baked right in.','price' => 7.50,'image' => 'Cheese-Jalapeño-Bagels.png') // Assuming corrected filename
    );
} // End gatita_bakes_get_products() function

function gatita_bakes_get_pickup_locations() {
    return array(
        'main_bakery' => 'Main Bakery (123 Bread Lane, Mon-Fri 10am-4pm)',
        'farmers_market' => 'Saturday Farmers Market (Downtown Park, 8am-12pm)',
        'eastside_cafe' => 'Eastside Cafe Partnership (456 Coffee St, Wed ONLY 11am-2pm)',
    );
} // End gatita_bakes_get_pickup_locations() function

// =========================================================================
// 3. LANDING PAGE SHORTCODE [gatita_bakes_landing]
// =========================================================================
// CSS hides this if theme banner is used on homepage
function gatita_bakes_landing_page_shortcode() {
    ob_start();
    $tagline = "The smell of fresh bread is the best kind of welcome.";
    ?>
    <div class="gatita-hero-section">
        <div class="gatita-hero-content">
            <h1>Gatita Bakes</h1>
            <p><?php echo esc_html($tagline); ?></p>
            <a href="<?php echo esc_url(home_url('/order')); ?>" class="gatita-button">Order Now</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
} // End gatita_bakes_landing_page_shortcode() function
add_shortcode('gatita_bakes_landing', 'gatita_bakes_landing_page_shortcode');


// =========================================================================
// 4. ORDER FORM SHORTCODE [gatita_bakes_order_form] - RESTORED ORIGINAL CODE
// =========================================================================
function gatita_bakes_order_form_shortcode() {
    // --- START: Restore Original Function Code ---
    $output = '';
    if ( isset( $_GET['order_status'] ) ) { if ( $_GET['order_status'] === 'success' ) { $output .= "<div class='gatita-notice gatita-notice-success'>Thank you! Check email for confirmation/payment.</div>"; } elseif ( strpos($_GET['order_status'], 'error') === 0 ) { $error_message = 'Sorry, an error occurred. Please check details and try again.'; $output .= "<div class='gatita-notice gatita-notice-error'>" . esc_html($error_message) . "</div>"; } }

    $products = gatita_bakes_get_products();
    $pickup_locations = gatita_bakes_get_pickup_locations();

    ob_start();
    ?>
    <form id="gatita-order-form" method="POST" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
        <input type="hidden" name="action" value="gatita_bakes_submit_order">
        <?php wp_nonce_field( 'gatita_bakes_order_nonce', 'gatita_bakes_nonce' ); ?>

        <div class="gatita-order-product-section">
            <h2>Our Bakes</h2>
            <div class="swiper gatita-product-slider">
                <div class="swiper-wrapper">
                    <?php if ( !empty($products) && is_array($products) ) :
                        foreach ($products as $slug => $product) :
                            // Check essential keys within the loop
                            if (isset($product['name']) && isset($product['image']) && isset($product['price'])) :
                                $image_url = GATITA_BAKES_PLUGIN_URL . 'assets/images/' . esc_attr($product['image']); // Corrected path
                                $product_id_base = 'product_' . esc_attr($slug);
                                $product_name = esc_html($product['name']);
                                $product_price = esc_html(number_format((float)$product['price'], 2));
                                $product_description = isset($product['description']) ? esc_html($product['description']) : '';
                    ?>
                                <div class="swiper-slide"> <div class="gatita-product-card" data-product-slug="<?php echo esc_attr($slug); ?>" data-product-name="<?php echo esc_attr($product['name']); ?>" data-product-price="<?php echo esc_attr((float)$product['price']); ?>">
                                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo $product_name; ?>">
                                    <h3><?php echo $product_name; ?></h3>
                                    <?php if (!empty($product_description)) : ?><p><?php echo $product_description; ?></p><?php endif; ?>
                                    <p class="price">$<?php echo $product_price; ?></p>
                                    <div class="gatita-product-order-controls"> <input type="checkbox" id="<?php echo $product_id_base . '_check'; ?>" name="products[<?php echo esc_attr($slug); ?>]" value="1" class="product-select-checkbox"> <label for="<?php echo $product_id_base . '_check'; ?>"> Add to Order</label> <label for="<?php echo $product_id_base . '_qty'; ?>" class="quantity-label">Qty:</label> <input type="number" id="<?php echo $product_id_base . '_qty'; ?>" name="quantity[<?php echo esc_attr($slug); ?>]" value="1" min="1" max="10" class="quantity-input product-quantity-input" style="width: 60px;" aria-label="Quantity for <?php echo $product_name; ?>"> </div>
                                </div> </div>
                    <?php
                            endif; // end valid product data check
                        endforeach; // end product loop
                    else: // $products array empty/invalid
                         ?> <p style="text-align: center;">No products available at this time.</p> <?php
                    endif; // end check products array
                    ?>
                </div><?php // end swiper-wrapper ?>
                <div class="swiper-button-prev"></div> <div class="swiper-button-next"></div>
            </div><?php // end swiper ?>
        </div> <?php // END Product Section ?>


        <?php // Section 2: Details and Summary Area Wrapper - Only show if products exist ?>
        <?php if ( !empty($products) && is_array($products) ) : ?>
            <div class="gatita-order-details-wrapper">

                <div class="gatita-order-main-details"> <?php // Column 1: Main Details ?>
                    <div class="gatita-details-section">
                        <h2>Your Details</h2>
                        <div class="gatita-form-row"><label for="customer_name">Name <span class="required">*</span></label><input type="text" id="customer_name" name="customer_name" required></div>
                        <div class="gatita-form-row"><label for="customer_email">Email <span class="required">*</span></label><input type="email" id="customer_email" name="customer_email" required></div>
                        <div class="gatita-form-row"><label for="customer_phone">Phone <span class="required">*</span></label><input type="tel" id="customer_phone" name="customer_phone" required></div>
                    </div>
                    <div class="gatita-details-section">
                        <h2>Order Method <span class="required">*</span></h2>
                        <div class="gatita-form-row gatita-radio-group">
                            <label><input type="radio" name="order_type" value="pickup" checked required> Pickup</label>
                            <label><input type="radio" name="order_type" value="delivery" required> Delivery</label>
                        </div>
                        <div id="pickup-location-fields" class="gatita-form-section-dynamic">
                             <div class="gatita-form-row"> <label for="pickup_location">Pickup Location <span class="required">*</span></label> <select id="pickup_location" name="pickup_location" required> <option value="">-- Select --</option> <?php foreach ($pickup_locations as $key => $display_text) : ?> <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($display_text); ?></option> <?php endforeach; ?> </select> </div>
                             <p><small>Please adhere...</small></p>
                        </div>
                        <div id="delivery-address-fields" class="gatita-form-section-dynamic">
                            <h3>Delivery Address <span class="required">*</span></h3> <p><small>(Required...)</small></p>
                            <div class="gatita-form-row"><label for="delivery_street">Street Address</label><input type="text" id="delivery_street" name="delivery_street"></div>
                            <div class="gatita-form-row"><label for="delivery_city">City</label><input type="text" id="delivery_city" name="delivery_city" value=""></div>
                            <div class="gatita-form-row"><label for="delivery_zip">ZIP Code</label><input type="text" id="delivery_zip" name="delivery_zip"></div>
                            <div class="gatita-form-row"><label for="delivery_notes">Delivery Notes (optional)</label><textarea id="delivery_notes" name="delivery_notes"></textarea></div>
                        </div>
                    </div>
                </div> <?php // END Left Column ?>

                <div class="gatita-order-summary-area"> <?php // Column 2: Summary ?>
                    <div class="gatita-summary-sticky-content">
                        <h2>Order Summary</h2>
                         <div id="gatita-cart-summary">
                            <ul id="gatita-cart-items" class="gatita-cart-items-list"> <li class="cart-empty-msg">Your cart is empty.</li> </ul>
                            <div id="gatita-cart-totals" class="gatita-cart-totals-container"> <p class="cart-total-placeholder">Select items to see total.</p> </div>
                        </div>
                         <div class="gatita-submit-section">
                            <div class="gatita-form-row gatita-submit-row"> <button type="submit" class="gatita-button gatita-place-order-button">Place Order</button> </div>
                            <p class="gatita-payment-note"><small>Payment instructions...</small></p>
                        </div>
                    </div>
                </div> <?php // END Right Column ?>
            </div> <?php // END Details Wrapper ?>
        <?php endif; // End check if products displayed ?>

    </form>
    <?php
    $output .= ob_get_clean();
    return $output; // <<<<< RESTORED ORIGINAL RETURN >>>>>

} // End gatita_bakes_order_form_shortcode() function
add_shortcode('gatita_bakes_order_form', 'gatita_bakes_order_form_shortcode');


// =========================================================================
// 5. FORM SUBMISSION HANDLER (v1.7.2 / v1.4.15)
// =========================================================================
function gatita_bakes_handle_form_submission() {
    // Verify nonce
    if ( ! isset( $_POST['gatita_bakes_nonce'] ) || ! wp_verify_nonce( $_POST['gatita_bakes_nonce'], 'gatita_bakes_order_nonce' ) ) { wp_safe_redirect(add_query_arg('order_status', 'error_nonce', home_url('/order'))); exit; }
    // Sanitize basic info
    $customer_name = isset( $_POST['customer_name'] ) ? sanitize_text_field( trim($_POST['customer_name']) ) : ''; $customer_email = isset( $_POST['customer_email'] ) ? sanitize_email( $_POST['customer_email'] ) : ''; $customer_phone = isset( $_POST['customer_phone'] ) ? sanitize_text_field( trim($_POST['customer_phone']) ) : ''; $order_type = isset( $_POST['order_type'] ) ? sanitize_key( $_POST['order_type'] ) : 'pickup';
    // Initialize ALL potential email variables + Generate Order Number
    $delivery_address_html = 'N/A'; $pickup_location_text = 'N/A'; $all_locations = gatita_bakes_get_pickup_locations(); $email_order_number = uniqid('GB-');
    // Validate required basic info
    if ( empty($customer_name) || !is_email($customer_email) || empty($customer_phone) ) { wp_safe_redirect(add_query_arg('order_status', 'error_required', home_url('/order'))); exit; }
    // --- Validate and Process Based on Order Type ---
    if ($order_type === 'delivery') { $delivery_street = isset( $_POST['delivery_street'] ) ? sanitize_text_field( trim($_POST['delivery_street']) ) : ''; $delivery_city = isset( $_POST['delivery_city'] ) ? sanitize_text_field( trim($_POST['delivery_city']) ) : ''; $delivery_zip = isset( $_POST['delivery_zip'] ) ? sanitize_text_field( trim($_POST['delivery_zip']) ) : ''; $delivery_notes = isset( $_POST['delivery_notes'] ) ? sanitize_textarea_field( $_POST['delivery_notes'] ) : ''; if ( empty($delivery_street) || empty($delivery_city) || empty($delivery_zip) ) { wp_safe_redirect(add_query_arg('order_status', 'error_address', home_url('/order'))); exit; } $delivery_address_html = nl2br(esc_html( implode("\n", array_filter([$delivery_street, $delivery_city . ", " . $delivery_zip])) )); if(!empty($delivery_notes)) { $delivery_address_html .= "<br><small>Notes: " . nl2br(esc_html($delivery_notes)) . "</small>"; }
    } elseif ($order_type === 'pickup') { $selected_location_key = isset( $_POST['pickup_location'] ) ? sanitize_key( $_POST['pickup_location'] ) : ''; if ( empty($selected_location_key) || !isset($all_locations[$selected_location_key]) ) { wp_safe_redirect(add_query_arg('order_status', 'error_pickup_loc', home_url('/order'))); exit; } $pickup_location_text = esc_html($all_locations[$selected_location_key]); }
    // Process Ordered Products
    $products_available = gatita_bakes_get_products(); $ordered_items_data = array(); $order_items_html = ''; $order_total = 0.00; if (isset($_POST['products']) && is_array($_POST['products']) && !empty($products_available)) { foreach ($_POST['products'] as $slug => $selected) { if ($selected == '1' && isset($products_available[$slug])) { $quantity = isset($_POST['quantity'][$slug]) ? intval($_POST['quantity'][$slug]) : 1; if ($quantity < 1) $quantity = 1; $product = $products_available[$slug]; if ( isset($product['price']) && is_numeric($product['price']) ) { $item_total = (float)$product['price'] * $quantity; $order_total += $item_total; $item_name = isset($product['name']) ? $product['name'] : $slug; $ordered_items_data[] = array( 'name' => $item_name, 'quantity' => $quantity, 'price_per_item' => (float)$product['price'], 'item_total' => $item_total ); $order_items_html .= "<tr><td style='padding: 8px; border: 1px solid #ddd;'>" . esc_html($item_name) . "</td><td style='padding: 8px; border: 1px solid #ddd; text-align: center;'>" . esc_html($quantity) . "</td><td style='padding: 8px; border: 1px solid #ddd; text-align: right;'>$" . esc_html(number_format($item_total, 2)) . "</td></tr>"; } } } }
    if (empty($ordered_items_data)) { wp_safe_redirect(add_query_arg('order_status', 'error_noitems', home_url('/order'))); exit; }
    // Prepare Email
    $admin_email = get_option('admin_email'); $email_subject = 'New Gatita Bakes Order (#'. $email_order_number .') - ' . $customer_name; $customer_email_subject = 'Your Gatita Bakes Order Confirmation (#'. $email_order_number .')';
    $email_template_path = GATITA_BAKES_PLUGIN_DIR . 'email-templates.php';
    if ( file_exists( $email_template_path ) ) {
        ob_start();
        // Define ALL variables needed by the email template
        $email_customer_name = $customer_name; $email_customer_email = $customer_email; $email_customer_phone = $customer_phone; $email_order_type = ucfirst($order_type);
        $email_delivery_address_html = $delivery_address_html; $email_pickup_location_text = $pickup_location_text;
        $email_order_number = $email_order_number; // Make available to template
        $email_order_items_html = $order_items_html; $email_order_total = number_format($order_total, 2);
        // *** INCLUDE EMAIL TEMPLATE (CRITICAL: Ensure syntax error fixed!) ***
        include $email_template_path;
        $email_body = ob_get_clean();
        // Send Emails
        $headers = array('Content-Type: text/html; charset=UTF-8'); $from_name = 'Gatita Bakes'; $from_email = $admin_email; $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
        @wp_mail( $admin_email, $email_subject, $email_body, $headers );
        @wp_mail( $customer_email, $customer_email_subject, $email_body, $headers );
    } else { error_log('Gatita Bakes ERROR: Email template file not found at: ' . $email_template_path); }

    // Redirect after successful submission
    wp_safe_redirect( add_query_arg('order_status', 'success', home_url('/order')) ); exit;

} // End gatita_bakes_handle_form_submission() function
add_action( 'admin_post_nopriv_gatita_bakes_submit_order', 'gatita_bakes_handle_form_submission' );
add_action( 'admin_post_gatita_bakes_submit_order', 'gatita_bakes_handle_form_submission' );

?>