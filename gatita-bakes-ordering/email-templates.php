<?php
/**
 * Gatita Bakes - Email Template Example
 * Filename: email-templates.php
 * Version: 1.8.0 (Corrected conditional logic, uses order number)
 * Author: Bucketbranch
 *
 * This template receives the following variables from gatita-bakes-ordering.php:
 * - $email_customer_name
 * - $email_customer_email
 * - $email_customer_phone
 * - $email_order_type             (e.g., "Pickup" or "Delivery")
 * - $email_pickup_location_text   (Text of selected location, or 'N/A')
 * - $email_delivery_address_html  (Formatted HTML address, or 'N/A')
 * - $email_order_number           (A unique ID like 'GB-xxxxxxxx')
 * - $email_order_items_html       (HTML <tr> elements for the items table)
 * - $email_order_total            (Formatted total price string, e.g., "25.50")
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// --- Basic Email Styling (Adapt to your design) ---
$bg_color = '#fdfaf7';      // Light beige background
$body_color = '#ffffff';    // White content area
$text_color = '#5a4e46';    // Main text dark brown/grey
$light_text = '#666666';   // Lighter text
$brand_color = '#e5a98c';   // Peach accent
$venmo_blue = '#0074de';   // Standard Venmo blue
$font_family = 'Arial, Helvetica, sans-serif'; // Basic fallback font

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Gatita Bakes Order (#<?php echo isset($email_order_number) ? esc_html($email_order_number) : ''; ?>)</title> <?php // Add order number to title ?>
    <style>
        body { margin: 0; padding: 0; background-color: <?php echo $bg_color; ?>; font-family: <?php echo $font_family; ?>; -webkit-font-smoothing: antialiased; font-size: 16px; line-height: 1.6; color: <?php echo $text_color; ?>; }
        table { border-collapse: collapse; width: 100%; max-width: 100%;} /* Ensure table doesn't overflow */
        td, th { padding: 0; vertical-align: top; text-align: left;} /* Basic reset */
        img { border: 0; display: block; outline: none; text-decoration: none; max-width: 100%; height: auto; }
        a { color: <?php echo $brand_color; ?>; text-decoration: underline; }
        h1, h2, h3 { margin: 0 0 1em 0; color: <?php echo $text_color; ?>; font-weight: 700; line-height: 1.3; }
        h1 { font-size: 26px; }
        h2 { font-size: 20px; margin-top: 1.5em; border-bottom: 1px solid #eee; padding-bottom: 0.5em; }
        p { margin: 0 0 1em 0; color: <?php echo $text_color; ?>; }
        p:last-child { margin-bottom: 0; } /* Remove margin from last paragraph in a block */
        small { font-size: 0.85em; color: <?php echo $light_text; ?>; }
        strong { font-weight: bold; }

        .email-wrapper { padding: 20px 0; }
        .content-table { width: 100%; max-width: 600px; margin: 0 auto; background-color: <?php echo $body_color; ?>; border-radius: 8px; overflow: hidden; border: 1px solid #eee; }
        .header { background-color: <?php echo $brand_color; ?>; padding: 25px 20px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; }
        .body-content { padding: 30px 35px; color: <?php echo $text_color; ?>; font-size: 15px; line-height: 1.6; }

        /* Order Details Table */
        .order-details { width: 100%; margin: 20px 0; border: 1px solid #ddd; }
        .order-details th, .order-details td { padding: 10px 12px; border-bottom: 1px solid #eee; text-align: left; font-size: 14px; }
        .order-details th { background-color: #f9f9f9; font-weight: bold; color: <?php echo $text_color; ?>; }
        .order-details td { color: <?php echo $light_text; ?>; }
        .order-details tr:last-child td { border-bottom: none; }
        .order-details .item-qty { text-align: center; }
        .order-details .item-price { text-align: right; }
        .order-details tfoot td { border-top: 2px solid #ccc; font-weight: bold; font-size: 1.1em; color: <?php echo $text_color; ?>; }

        /* Customer Info Table */
        .customer-info { width: 100%; margin-bottom: 20px; } /* Added table class */
        .customer-info td { padding: 4px 0; font-size: 14px; color: <?php echo $light_text; ?>; }
        .customer-info td.label { font-weight: bold; width: 130px; /* Fixed width for label */ color: <?php echo $text_color; ?>; padding-right: 10px;}

        /* Venmo Button */
        .venmo-button { display: inline-block; background-color: <?php echo $venmo_blue; ?>; color: #ffffff !important; padding: 12px 25px; text-decoration: none !important; border-radius: 5px; font-weight: bold; margin-top: 15px; font-size: 15px; }

        /* Footer */
        .footer { background-color: #f2f2f2; padding: 25px; text-align: center; font-size: 12px; color: #888; }
        .footer a { color: #888; text-decoration: underline; }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: <?php echo $bg_color; ?>; font-family: <?php echo $font_family; ?>;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="email-wrapper">
        <tr>
            <td align="center">
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
                            <p>Hi <?php echo esc_html(isset($email_customer_name) ? $email_customer_name : 'Valued Customer'); ?>,</p>
                            <p>Thank you for your order<?php echo isset($email_order_number) ? ' (<strong>#' . esc_html($email_order_number) . '</strong>)' : ''; ?>! Here are the details:</p>

                            <h2 style="margin-top: 30px; border-bottom: 1px solid #eee; padding-bottom: 0.5em;">Order Summary</h2>
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="order-details">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="item-qty">Qty</th>
                                        <th class="item-price">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php echo isset($email_order_items_html) ? $email_order_items_html : '<tr><td colspan="3">Error loading items.</td></tr>'; // Check variable ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" style="text-align: right;"><strong>Total:</strong></td>
                                        <td class="item-price"><strong>$<?php echo isset($email_order_total) ? esc_html($email_order_total) : '0.00'; ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <h2 style="margin-top: 30px; border-bottom: 1px solid #eee; padding-bottom: 0.5em;">Your Information</h2>
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="customer-info">
                                <tr>
                                    <td class="label">Name:</td>
                                    <td><?php echo esc_html(isset($email_customer_name) ? $email_customer_name : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td class="label">Email:</td>
                                    <td><?php echo esc_html(isset($email_customer_email) ? $email_customer_email : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td class="label">Phone:</td>
                                    <td><?php echo esc_html(isset($email_customer_phone) ? $email_customer_phone : 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td class="label">Order Type:</td>
                                    <td><?php echo esc_html(isset($email_order_type) ? $email_order_type : 'N/A'); ?></td>
                                </tr>

                                <?php //!--- Conditional Pickup/Delivery Info - Check Variables --- ?>
                                <?php // THIS BLOCK MUST BE SYNTACTICALLY CORRECT ?>
                                <?php if (isset($email_order_type) && $email_order_type === 'Pickup' && isset($email_pickup_location_text) && $email_pickup_location_text !== 'N/A') : ?>
                                    <tr>
                                        <td class="label" style="vertical-align: top;">Pickup Location:</td>
                                        <td><?php echo $email_pickup_location_text; // Already escaped ?></td>
                                    </tr>
                                <?php elseif (isset($email_order_type) && $email_order_type === 'Delivery' && isset($email_delivery_address_html) && $email_delivery_address_html !== 'N/A') : ?>
                                    <tr>
                                        <td class="label" style="vertical-align: top;">Delivery Address:</td>
                                        <td><?php echo $email_delivery_address_html; // Contains HTML <br>, already escaped ?></td>
                                    </tr>
                                <?php endif; // *** End the conditional block *** ?>
                                <?php //!--- END Conditional Info --- ?>

                            </table> <?php // Close customer info table ?>

                            <h2 style="margin-top: 30px; border-bottom: 1px solid #eee; padding-bottom: 0.5em;">Payment Instructions</h2>
                            <p>Please complete your payment of <strong>$<?php echo isset($email_order_total) ? esc_html($email_order_total) : '0.00'; ?></strong> via Venmo.</p>
                            <p>You can find us at Venmo username: <strong>@karlathesourdoughstarta</strong></p> <?php // Correct Username ?>

                            <p style="text-align: center; margin-top: 20px; margin-bottom: 20px;">
                                <?php
                                // Prepare URL components
                                $venmo_username = 'karlathesourdoughstarta';
                                $venmo_amount = isset($email_order_total) ? $email_order_total : '0.00';
                                $venmo_note = 'GatitaBakes Order ' . (isset($email_order_number) ? '#' . $email_order_number : '') . ' - ' . (isset($email_customer_name) ? $email_customer_name : '');
                                $venmo_url = sprintf(
                                    'https://venmo.com/%s?txn=pay&amount=%s¬e=%s',
                                    $venmo_username,
                                    esc_attr($venmo_amount),
                                    urlencode($venmo_note)
                                );
                                ?>
                                <a href="<?php echo $venmo_url; ?>" target="_blank" class="venmo-button" style="display: inline-block; background-color: <?php echo $venmo_blue; ?>; color: #ffffff !important; padding: 12px 25px; text-decoration: none !important; border-radius: 5px; font-weight: bold;">
                                    Pay $<?php echo esc_html($venmo_amount); ?> on Venmo
                                </a>
                            </p>
                            <p>Please include your name or order number<?php echo isset($email_order_number) ? ' (#' . esc_html($email_order_number) . ')' : ''; ?> in the Venmo note if possible.</p>
                            <p>We'll confirm once payment is received. Thanks again!</p>
                            <p style="margin-top: 30px; font-style: italic; text-align: center;">"The smell of fresh bread is the best kind of welcome."</p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            © <?php echo date('Y'); ?> Gatita Bakes. All rights reserved.<br>
                            <a href="<?php echo esc_url(home_url('/')); ?>" style="color: #888; text-decoration: underline;">www.gatitabakes.com</a>
                        </td>
                    </tr>
                </table> <!-- End Content Table -->
            </td>
        </tr>
    </table> <!-- End Email Wrapper -->
</body>
</html>
<?php // --- END OF FILE --- Ensure nothing follows this line ?>
