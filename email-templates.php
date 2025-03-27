<?php
// Email HTML Template for Gatita Bakes

$order_number = $order_number ?? rand(1001, 9999);
$selected_items = $selected_items ?? [];
$total = $total ?? 0;
$name = $name ?? '';
$email = $email ?? '';
$mobile = $mobile ?? '';
$pickup_delivery = $pickup_delivery ?? 'pickup';
$address = $address ?? '';
$pickup_location = $pickup_location ?? '';
$need_by = $need_by ?? '';
$venmo_link = 'https://account.venmo.com/u/katvalderrama';
?>

<html>
<head>
  <style>
    body {
      font-family: 'Georgia', serif;
      background-color: #fffaf6;
      color: #333;
      padding: 2em;
    }
    .wrapper {
      max-width: 700px;
      margin: 0 auto;
      background: white;
      padding: 2em;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    h2 {
      color: #d57c4a;
    }
    .summary-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1em;
    }
    .summary-table th, .summary-table td {
      padding: 10px;
      border: 1px solid #ddd;
    }
    .summary-table th {
      background-color: #f3e7df;
      text-align: left;
    }
    .footer {
      margin-top: 2em;
      font-size: 0.9em;
      color: #555;
    }
    .venmo-link {
      display: inline-block;
      margin-top: 1em;
      padding: 0.75em 1.5em;
      background: #3d95ce;
      color: white;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <h2>Thank you for your order, <?= esc_html($name) ?>!</h2>
    <p>Your order number is <strong>#<?= $order_number ?></strong></p>
    <p>Below is a summary of your order, which will be ready by <strong><?= esc_html($need_by) ?></strong>.</p>

    <table class="summary-table">
      <tr><th>Item</th><th>Quantity</th><th>Price</th></tr>
      <?php foreach ($selected_items as $item): ?>
        <tr>
          <td><?= esc_html($item['name']) ?></td>
          <td><?= esc_html($item['quantity']) ?></td>
          <td>$<?= number_format($item['quantity'] * 12, 2) ?></td>
        </tr>
      <?php endforeach; ?>
      <tr><td colspan="2"><strong>Total</strong></td><td><strong>$<?= number_format($total, 2) ?></strong></td></tr>
    </table>

    <p>
      Delivery Method: <strong><?= esc_html(ucfirst($pickup_delivery)) ?></strong><br>
      <?php if ($pickup_delivery === 'delivery'): ?>
        Delivery Address: <?= esc_html($address) ?><br>
      <?php else: ?>
        Pickup Location: <?= esc_html($pickup_location) ?><br>
      <?php endif; ?>
      Contact: <?= esc_html($email) ?> / <?= esc_html($mobile) ?>
    </p>

    <p>Please complete payment using Venmo by clicking the button below:</p>
    <a href="<?= esc_url($venmo_link) ?>" class="venmo-link" target="_blank">Pay on Venmo</a>

    <div class="footer">
      <p>We'll review your order and confirm once payment has been received. If you have any questions, just reply to this email.</p>
      <p><em>"The smell of fresh bread is the best kind of welcome."</em><br>
      &mdash; Gatita Bakes</p>
    </div>
  </div>
</body>
</html>
