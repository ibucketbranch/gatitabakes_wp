<?php
/**
 * Template Name: Gatita Bakes Order Form
 */

get_header();
?>

<div class="gatita-bakes-container">
    <h1 class="gatita-main-title">GATITA BAKES</h1>
    <h2 class="gatita-subtitle">Sourdough Goods</h2>
    <h3 class="gatita-page-title">Order (Standalone)</h3>
    
    <div class="gatita-order-details-wrapper">
        <div class="gatita-main-content">
            <!-- Product Slider Section -->
            <div class="gatita-product-slider swiper">
                <div class="swiper-wrapper">
                    <?php
                    $products = array(
                        array(
                            'name' => 'Plain Sourdough Loaf',
                            'description' => 'Classic tangy sourdough with a chewy crust',
                            'price' => 8.00,
                            'image' => get_template_directory_uri() . '/assets/images/plain-sourdough.jpg'
                        ),
                        array(
                            'name' => 'Rosemary Sourdough Loaf',
                            'description' => 'Infused with fresh rosemary for an aromatic flavor',
                            'price' => 9.00,
                            'image' => get_template_directory_uri() . '/assets/images/rosemary-sourdough.jpg'
                        ),
                        array(
                            'name' => 'Everything Sourdough Loaf',
                            'description' => 'Coated with a savory everything bagel seasoning',
                            'price' => 9.50,
                            'image' => get_template_directory_uri() . '/assets/images/everything-sourdough.jpg'
                        ),
                        array(
                            'name' => 'Specialty Sourdough',
                            'description' => 'Ask about our rotating weekly special flavor',
                            'price' => 10.00,
                            'image' => get_template_directory_uri() . '/assets/images/specialty-sourdough.jpg'
                        ),
                        array(
                            'name' => 'Plain Bagels (Set of 4)',
                            'description' => 'Traditional chewy bagels, perfect for toasting',
                            'price' => 6.00,
                            'image' => get_template_directory_uri() . '/assets/images/plain-bagels.jpg'
                        ),
                        array(
                            'name' => 'Cheese Jalapeño Bagels (Set of 4)',
                            'description' => 'Spicy jalapeños and melted cheese baked right in',
                            'price' => 7.50,
                            'image' => get_template_directory_uri() . '/assets/images/jalapeno-bagels.jpg'
                        )
                    );

                    foreach ($products as $product) : ?>
                        <div class="swiper-slide">
                            <div class="gatita-product-card">
                                <img src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['name']); ?>" class="gatita-product-image">
                                <div class="gatita-product-info">
                                    <h3><?php echo esc_html($product['name']); ?></h3>
                                    <p class="gatita-product-description"><?php echo esc_html($product['description']); ?></p>
                                    <p class="gatita-product-price">$<?php echo number_format($product['price'], 2); ?></p>
                                    <input type="number" class="gatita-quantity-input" value="0" min="0" max="99" data-product="<?php echo esc_attr($product['name']); ?>" data-price="<?php echo esc_attr($product['price']); ?>">
                                    <button class="gatita-add-to-cart">Add to Cart</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>

            <!-- Contact Information Section -->
            <div class="gatita-details-section">
                <h2>Contact Information</h2>
                <div class="gatita-name-row">
                    <div class="gatita-form-row">
                        <label for="first-name">First Name<span class="required">*</span></label>
                        <input type="text" id="first-name" name="first-name" required>
                    </div>
                    <div class="gatita-form-row">
                        <label for="last-name">Last Name<span class="required">*</span></label>
                        <input type="text" id="last-name" name="last-name" required>
                    </div>
                </div>
                <div class="gatita-form-row">
                    <label for="email">Email<span class="required">*</span></label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>
        </div>

        <!-- Order Summary Section -->
        <div class="gatita-order-summary-area">
            <h2>Order Summary</h2>
            <div id="gatita-cart-items">
                <!-- Cart items will be dynamically added here -->
            </div>
            <button type="submit" class="gatita-submit-order">Place Order</button>
        </div>
    </div>
</div>

<?php get_footer(); ?> 