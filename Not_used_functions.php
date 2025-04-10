// Handle Gatita Bakes order submission
add_action('wp_ajax_submit_gatita_order', 'handle_gatita_order_submission');
add_action('wp_ajax_nopriv_submit_gatita_order', 'handle_gatita_order_submission');

function handle_gatita_order_submission() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'gatita_order_nonce')) {
        wp_send_json_error('Invalid security token');
    }

    // Get and decode order data
    $order_data = json_decode(stripslashes($_POST['order_data']), true);
    
    if (!$order_data) {
        wp_send_json_error('Invalid order data');
    }

    // Generate order number
    $order_number = 'GB' . date('Ymd') . rand(100, 999);

    // Store order in database (implement your storage logic here)
    // save_order_to_database($order_number, $order_data);

    // Send confirmation email
    send_order_confirmation_email($order_number, $order_data);

    // Get confirmation page URL
    $confirmation_page = get_page_by_path('order-confirmation');
    $redirect_url = add_query_arg(
        array(
            'order_number' => $order_number,
            'status' => 'success'
        ),
        get_permalink($confirmation_page->ID)
    );

    wp_send_json_success(array(
        'message' => 'Order submitted successfully',
        'redirect_url' => $redirect_url
    ));
}

function send_order_confirmation_email($order_number, $order_data) {
    $to = $order_data['customer']['email'];
    $subject = "Order Confirmation #{$order_number} - Gatita Bakes";
    
    // Calculate total
    $total = 0;
    foreach ($order_data['items'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Build email content
    $message = "Thank you for your order!\n\n";
    $message .= "Order Number: #{$order_number}\n\n";
    
    $message .= "Order Details:\n";
    foreach ($order_data['items'] as $item) {
        $item_total = $item['price'] * $item['quantity'];
        $message .= "{$item['product']} x {$item['quantity']} - \${$item_total}\n";
    }
    $message .= "\nTotal: \${$total}\n\n";
    
    $message .= "Delivery Details:\n";
    $message .= "Date: {$order_data['delivery']['date']}\n";
    $message .= "Time: {$order_data['delivery']['time']}\n";
    $message .= "Address: {$order_data['delivery']['address']}\n\n";
    
    $message .= "Payment Instructions:\n";
    $message .= "Please send payment via Venmo to: @katvalderrama\n";
    $message .= "Include your order number (#{$order_number}) in the payment note.\n\n";
    
    $message .= "Thank you for choosing Gatita Bakes!";

    // Send email
    wp_mail($to, $subject, $message);

    // Send admin notification
    $admin_email = get_option('admin_email');
    wp_mail($admin_email, "New Order #{$order_number}", $message);
}

// Add nonce to page
add_action('wp_enqueue_scripts', 'add_gatita_order_nonce');
function add_gatita_order_nonce() {
    wp_localize_script('gatita-bakes', 'gatitaBakesSettings', array(
        'nonce' => wp_create_nonce('gatita_order_nonce')
    ));
} 