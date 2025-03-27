<?php
/*
Plugin Name: Gatita Bakes Online Ordering
Description: Custom order form with categorized products, images, quantity, delivery/pickup, and Venmo link.
Version: 1.0
Author: Bucketbranch
*/

defined('ABSPATH') || exit;

// Enqueue styles
function gatita_bakes_enqueue_styles() {
    wp_enqueue_style(
        'gatita-bakes-css',
        plugins_url('assets/gatita-bakes.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/gatita-bakes.css') // bust cache if CSS file is edited
    );
}
add_action('wp_enqueue_scripts', 'gatita_bakes_enqueue_styles');

// Landing Page Shortcode
function gatita_bakes_landing_page() {
    ob_start(); ?>
    <div class="hero" style="background-image: url('<?php echo plugin_dir_url(__FILE__) . 'images/hero-page-fullpage.png'; ?>');">
        <div class="hero-text">
            <h1>Artisan Bakery Delights</h1>
            <p>Freshly baked goods!</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('gatita_bakes_landing', 'gatita_bakes_landing_page');

// Get Product Data
function gatita_bakes_get_products() {
    $dir = plugin_dir_path(__FILE__) . 'images/';
    $url = plugin_dir_url(__FILE__) . 'images/';
    $products = [];

    foreach (glob($dir . '*.{jpg,jpeg,png,webp}', GLOB_BRACE) as $file) {
        $filename = basename($file);
        $name = ucwords(str_replace(['-', '_'], ' ', pathinfo($filename, PATHINFO_FILENAME)));
        $category = 'Other';

        if (stripos($name, 'bagel') !== false) {
            $category = 'Bagels';
        } elseif (stripos($name, 'loaf') !== false || stripos($name, 'sourdough') !== false) {
            $category = 'Sourdough Loaves';
        }

        $products[] = [
            'name' => $name,
            'image' => $url . $filename,
            'category' => $category,
        ];
    }
    return $products;
}

// Order Form Shortcode
function gatita_bakes_order_form() {
    ob_start();
    $products = gatita_bakes_get_products();
    $grouped = [];
    foreach ($products as $product) {
        $grouped[$product['category']][] = $product;
    }

    include plugin_dir_path(__FILE__) . 'template-order-form.php';

    return ob_get_clean();
}
add_shortcode('gatita_bakes_order_form', 'gatita_bakes_order_form');
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gatita_order_submit'])) {
        $selected_items = $_POST['product'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $mobile = sanitize_text_field($_POST['mobile']);
        $pickup_delivery = sanitize_text_field($_POST['pickup_delivery']);
        $address = sanitize_text_field($_POST['address']);
        $pickup_location = sanitize_text_field($_POST['pickup_location']);
        $need_by = sanitize_text_field($_POST['need_by']);

        $minDate = date('Y-m-d', strtotime('+3 days'));
        $errors = [];
        if ($need_by < $minDate) {
            $errors[] = 'Need-by date must be at least 3 days from today.';
        }

        if (empty($errors)) {
            $order_number = rand(1001, 9999);
            $selected_data = [];
            $total = 0;

            foreach ($selected_items as $index => $name_item) {
                $qty = intval($quantities[$index] ?? 1);
                $selected_data[] = ['name' => $name_item, 'quantity' => $qty];
                $total += $qty * 12;
            }

            include plugin_dir_path(__FILE__) . 'email-templates.php';

            $headers = ['Content-Type: text/html; charset=UTF-8'];
            wp_mail($email, "Your Gatita Bakes Order #$order_number", $email_content, $headers);
            wp_mail('gatitabakes@bucketbranch.com', "New Order #$order_number", $email_content, $headers);

            echo '<div class=\"confirmation\"><h2>Thank you for your order!</h2><p>We\'ll confirm once payment is received via Venmo.</p></div>';
        }
    }
