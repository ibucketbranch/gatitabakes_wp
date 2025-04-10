<?php
/**
 * Template Name: Order Form - Gatita Bakes
 * Description: A standalone template for the Gatita Bakes order form
 */

// Force the template to be loaded from our plugin
if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="gatita-page-wrapper">
    <div class="gatita-page-content">
        <?php echo do_shortcode('[gatita_bakes_order_form]'); ?>
    </div>
</div>

<?php get_footer(); ?>

<style>
/* Page Wrapper */
.gatita-page-wrapper {
    min-height: 100vh;
    background: #fdfaf7;
    padding: 40px 0;
}

/* Page Content Styles */
.gatita-page-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .gatita-page-wrapper {
        padding: 20px 0;
    }
    .gatita-page-content {
        padding: 20px;
    }
}
</style> 