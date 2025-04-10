<?php
/**
 * Template Name: Gatita Bakes Order Confirmation
 * 
 * This template displays the order confirmation page for Gatita Bakes orders.
 */

get_header(); // Add WordPress header

// Start session if not already started
if (!session_id()) {
    session_start();
}

// Get order details from URL parameters and session
$order_number = isset($_GET['order_number']) ? sanitize_text_field($_GET['order_number']) : '';
$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

// Get order data from session or database
$order_data = isset($_SESSION['gatita_order_data']) ? $_SESSION['gatita_order_data'] : get_option('gatita_bakes_order_' . $order_number);

// Clear the session data after retrieving it
if (isset($_SESSION['gatita_order_data'])) {
    unset($_SESSION['gatita_order_data']);
}
?>

<div class="gatita-bakes-container">
    <div class="gatita-confirmation-wrapper">
        <?php if ($status === 'success' && !empty($order_number) && $order_data): ?>
            <div class="gatita-notice gatita-notice-success">
                <h1>Thank you for your order!</h1>
                <p>Order #<?php echo esc_html($order_number); ?> has been received.</p>
            </div>

            <div class="gatita-confirmation-details">
                <h2>Order Details</h2>
                
                <div class="gatita-order-items">
                    <?php foreach ($order_data['cart_items'] as $item): 
                        $item_total = $item['price'] * $item['quantity'];
                    ?>
                        <div class="gatita-order-item">
                            <span class="item-name"><?php echo esc_html($item['name']); ?></span>
                            <span class="item-quantity">×<?php echo esc_html($item['quantity']); ?></span>
                            <span class="item-price">$<?php echo number_format($item_total, 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="gatita-order-total">
                        <strong>Total:</strong>
                        <span>$<?php echo number_format($order_data['order_total'], 2); ?></span>
                    </div>
                </div>

                <div class="gatita-customer-info">
                    <h3>Customer Information</h3>
                    <p><strong>Name:</strong> <?php echo esc_html($order_data['customer_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo esc_html($order_data['customer_email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo esc_html($order_data['customer_phone']); ?></p>
                    
                    <h4><?php echo esc_html(ucfirst($order_data['order_type'])); ?> Details:</h4>
                    <?php if ($order_data['order_type'] === 'pickup'): ?>
                        <p><strong>Location:</strong> <?php echo esc_html($order_data['pickup_location']); ?></p>
                        <?php if (!empty($order_data['pickup_notes'])): ?>
                            <p><strong>Notes:</strong> <?php echo esc_html($order_data['pickup_notes']); ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p><strong>Delivery Address:</strong><br>
                        <?php echo esc_html($order_data['delivery_street']); ?><br>
                        <?php echo esc_html($order_data['delivery_city']); ?>, <?php echo esc_html($order_data['delivery_zip']); ?></p>
                        <?php if (!empty($order_data['delivery_notes'])): ?>
                            <p><strong>Notes:</strong> <?php echo esc_html($order_data['delivery_notes']); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="gatita-payment-info">
                    <h3>Payment Instructions</h3>
                    <p>Please send payment via Venmo to: <strong>@katvalderrama</strong></p>
                    <p>Include your order number (#<?php echo esc_html($order_data['order_number']); ?>) in the payment note.</p>
                    <p><strong>Important:</strong> Your order is not confirmed until payment is received and verified.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="gatita-notice gatita-notice-error">
                <h1>Order Not Found</h1>
                <p>We couldn't find your order details. Please contact us if you need assistance.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); // Add WordPress footer ?> 