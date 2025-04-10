<?php
/**
 * Template Name: Order Confirmation
 * Description: Template for displaying order confirmation
 */

get_header();

// Get order number from URL
$order_number = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : '';
$order_data = get_option('gatita_bakes_order_' . $order_number);

// Check if order exists and is valid
if (!$order_data || $order_data['status'] !== 'success') {
    wp_die('Invalid or missing order. Please contact support if you believe this is an error.');
}
?>

<div class="order-confirmation">
    <div class="order-header">
        <h1>Thank You for Your Order!</h1>
    </div>

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

<style>
    .order-confirmation {
        max-width: 800px;
        margin: 40px auto;
        padding: 30px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .order-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e5a98c;
    }
    
    .order-header h1 {
        color: #5a4e46;
        margin: 0;
        font-size: 28px;
    }
    
    .order-details {
        margin-bottom: 30px;
    }
    
    .order-items {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    
    .order-items th {
        text-align: left;
        padding: 10px;
        background: #fdfaf7;
        border-bottom: 2px solid #e5a98c;
    }
    
    .order-items td {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .order-items .qty {
        text-align: center;
    }
    
    .order-items .price {
        text-align: right;
    }
    
    .total {
        text-align: right;
        font-weight: bold;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid #e5a98c;
        font-size: 1.2em;
    }
    
    .customer-info,
    .payment-info {
        background: #fdfaf7;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
        border: 1px solid #e5a98c;
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
    
    .venmo-button:hover {
        background-color: #0074de;
        color: white;
        text-decoration: none;
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

<?php get_footer(); ?> 