<?php
// Template: Order Form Layout
?>

<form method="POST" class="gatita-order-form">
  <h2>Choose Your Baked Goods</h2>

  <?php foreach ($grouped as $category => $items): ?>
    <h3><?= esc_html($category) ?></h3>
    <div class="product-grid">
      <?php foreach ($items as $index => $product): ?>
        <div class="product-card">
          <img src="<?= esc_url($product['image']) ?>" alt="<?= esc_attr($product['name']) ?>">
          <h4><?= esc_html($product['name']) ?></h4>
          <label>Qty:
            <input type="number" name="quantity[]" value="1" min="1">
            <input type="hidden" name="product[]" value="<?= esc_attr($product['name']) ?>">
          </label>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>

  <h2>Contact Info</h2>
  <label>Name
    <input type="text" name="name" value="<?= esc_attr($_POST['name'] ?? '') ?>" required>
  </label>
  <label>Email
    <input type="email" name="email" value="<?= esc_attr($_POST['email'] ?? '') ?>" required>
  </label>
  <label>Mobile
    <input type="text" name="mobile" value="<?= esc_attr($_POST['mobile'] ?? '') ?>">
  </label>

  <h2>Pickup or Delivery</h2>
  <label><input type="radio" name="pickup_delivery" value="pickup" <?= (($_POST['pickup_delivery'] ?? '') === 'pickup') ? 'checked' : '' ?>> Pickup</label>
  <label><input type="radio" name="pickup_delivery" value="delivery" <?= (($_POST['pickup_delivery'] ?? '') === 'delivery') ? 'checked' : '' ?>> Delivery</label>

  <div class="conditional-fields">
    <div class="delivery-only">
      <label>Delivery Address
        <input type="text" name="address" value="<?= esc_attr($_POST['address'] ?? '') ?>">
      </label>
    </div>

    <label>Pickup Location
      <select name="pickup_location">
        <option value="West Sac" <?= ($_POST['pickup_location'] ?? '') === 'West Sac' ? 'selected' : '' ?>>West Sacramento</option>
        <option value="Downtown Sac" <?= ($_POST['pickup_location'] ?? '') === 'Downtown Sac' ? 'selected' : '' ?>>Downtown Sacramento</option>
      </select>
    </label>
  </div>

  <label>Need by date:
    <input type="date" name="need_by" value="<?= esc_attr($_POST['need_by'] ?? '') ?>" required>
    <small>(Minimum 3 days from today)</small>
  </label>

  <?php if (!empty($errors)): ?>
    <div class="errors">
      <?php foreach ($errors as $error): ?>
        <p class="error"><?= esc_html($error) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <button type="submit" name="gatita_order_submit">Submit Order</button>
</form>
