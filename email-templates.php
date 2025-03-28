<?php
/**
 * Gatita Bakes - Email Template Example
 *
 * Variables available:
 * $email_customer_name
 * $email_customer_email
 * $email_customer_phone
 * $email_order_type             (e.g., "Pickup" or "Delivery")
 * $email_pickup_location_text   (Text of selected location, or 'N/A')
 * $email_delivery_address_html  (Formatted HTML address, or 'N/A')
 * $email_order_items_html       (HTML <tr> elements for the items table)
 * $email_order_total            (Formatted total price string, e.g., "25.50")
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Set desired background colors, fonts etc.
$bg_color = '#fdfaf7';
$body_color = '#ffffff';
$text_color = '#5a4e46';
$brand_color = '#e5a98c';
$font_family = 'Arial, Helvetica, sans-serif';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Gatita Bakes Order</title>
    <style>
        /* Add basic responsive styles and resets */
        body { margin: 0; padding: 0; background-color: <?php echo $bg_color; ?>; font-family: <?php echo $font_family; ?>; }
        table { border-collapse: collapse; width: 100%; }
        td { padding: 0; }
        img { border: 0; display: block; outline: none; text-decoration: none; }
        .content-table { width: 100%; max-width: 600px; margin: 0 auto; background-color: <?php echo $body_color; ?>; border-radius: 8px; overflow: hidden; }
        .header { background-color: <?php echo $brand_color; ?>; padding: 20px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; }
        .body-content { padding: 30px; color: <?php echo $text_color; ?>; font-size: 16px; line-height: 1.6; }
        .body-content p { margin: 0 0 1em 0; }
        .order-details th, .order-details td { padding: 12px 15px; border: 1px solid #eee; text-align: left; }
        .order-details th { background-color: #f9f9f9; font-weight: bold; }
        .order-details .total-row td { border-top: 2px solid #ddd; font-weight: bold; font-size: 1.1em; }
        .order-details .item-name { /* styles for item name cell */ }
        .order-details .item-qty { text-align: center; }
        .order-details .item-price { text-align: right; }
        .venmo-button { display: inline-block; background-color: #0074de; /* Venmo Blue */ color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }
        .footer { background-color: #f2f2f2; padding: 20px; text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: <?php echo $bg_color; ?>;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <!-- Main Content Table -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="content-table">
                    <!-- Header -->
                    <tr>
                        <td class="header">
                            <h1>Gatita Bakes Order</h1>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td class="body-content">
                            <p>Hi <?php echo esc_html($email_customer_name); ?>,</p>
                            <p>Thank you for your order! Here are the details:</p>

                            <h2 style="color: <?php echo $brand_color; ?>; margin-top: 30px; margin-bottom: 15px;">Order Summary</h2>
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="order-details">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th style="text-align: center;">Qty</th>
                                        <th style="text-align: right;">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php echo $email_order_items_html; // Output the table rows generated by PHP ?>
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td colspan="2" style="text-align: right;"><strong>Total:</strong></td>
                                        <td style="text-align: right;"><strong>$<?php echo esc_html($email_order_total); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <h2 style="color: <?php echo $brand_color; ?>; margin-top: 30px; margin-bottom: 15px;">Your Information</h2>
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="padding: 5px 0;"><strong>Name:</strong></td>
                                    <td style="padding: 5px 0;"><?php echo esc_html($email_customer_name); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0;"><strong>Email:</strong></td>
                                    <td style="padding: 5px 0;"><?php echo esc_html($email_customer_email); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0;"><strong>Phone:</strong></td>
                                    <td style="padding: 5px 0;"><?php echo esc_html($email_customer_phone); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0;"><strong>Order Type:</strong></td>
                                    <td style="padding: 5px 0;"><?php echo esc_html($email_order_type); ?></td>
                                </tr>

                                <?php //!--- NEW: Conditional Pickup/Delivery Info --- ?>
                                <?php if ($email_order_type === 'Pickup' && $email_pickup_location_text !== 'N/A') : ?>
                                    <tr>
                                        <td style="padding: 5px 0;"><strong>Pickup Location:</strong></td>
                                        <td style="padding: 5px 0;"><?php echo $email_pickup_location_text; // Already escaped in PHP handler ?></td>
                                    </tr>
                                <?php elseif ($email_order_type === 'Delivery' && $email_delivery_address_html !== 'N/A') : ?>
                                    <tr>
                                        <td style="padding: 5px 0; vertical-align: top;"><strong>Delivery Address:</strong></td>
                                        <td style="padding: 5px 0;"><?php echo $email_delivery_address_html; // Contains HTML <br> tags, already escaped in handler ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php //!--- END: Conditional Pickup/Delivery Info --- ?>

                            </table>
                            
                    <?php // --- PAYMENT INSTRUCTIONS SECTION --- ?>
                    <tr>
                        <td class="body-content" style="padding-top: 30px;"> <?php // Added padding-top here if needed ?>
                            <h2 style="color: <?php echo $brand_color; ?>; margin-top: 0; margin-bottom: 15px;">Payment Instructions</h2>
                            <p>Please complete your payment of <strong>$<?php echo esc_html($email_order_total); ?></strong> via Venmo.</p>
                            <p>You can find us at Venmo username: <strong>@karlathesourdoughstarta</strong></p> <?php // Username inserted here ?>

                            <p style="text-align: center; margin-top: 20px; margin-bottom: 20px;">
                                <?php
                                // Prepare URL components - Using urlencode for robustness in parameters
                                $venmo_username = 'karlathesourdoughstarta'; // The actual username without @
                                $venmo_amount = $email_order_total; // Raw total, esc_attr applied in href
                                $venmo_note = 'GatitaBakesOrder ' . $email_customer_name; // Basic note
                                $venmo_url = sprintf(
                                    'https://venmo.com/%s?txn=pay&amount=%s¬e=%s',
                                    $venmo_username,
                                    esc_attr($venmo_amount), // Use esc_attr for amount in URL attribute
                                    urlencode($venmo_note) // Use urlencode for the note content
                                );
                                ?>
                                <a href="<?php echo $venmo_url; ?>" target="_blank" class="venmo-button" style="display: inline-block; background-color: #0074de; color: #ffffff !important; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                                    Pay $<?php echo esc_html($email_order_total); ?> on Venmo
                                </a>
                            </p>
                            <p>Please include your name or order details in the Venmo note if possible.</p>
                            <p>We'll confirm once payment is received. Thanks again for supporting Gatita Bakes!</p>
                            <p style="margin-top: 30px; font-style: italic; text-align: center;">"The smell of fresh bread is the best kind of welcome."</p>
                        </td>
                    </tr>
                    <?php // --- END PAYMENT INSTRUCTIONS SECTION --- ?>
                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            © <?php echo date('Y'); ?> Gatita Bakes. All rights reserved.<br>
                            <?php // Add address or contact info if desired ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
