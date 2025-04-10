<?php
/**
 * Template Name: Standalone Order Form
 * Description: A standalone template for the Gatita Bakes order form
 */

// Get header
get_header();
?>

<div class="gatita-page-wrapper">
    <div class="gatita-page-content">
        <?php 
        // Output the order form shortcode
        echo do_shortcode('[gatita_bakes_order_form]');
        ?>
    </div>
</div>

<?php 
// Get footer
get_footer();
?>

<style>
/* Page Wrapper */
.gatita-page-wrapper {
    min-height: 100vh;
    background: #fdfaf7;
}

/* Page Content Styles */
.gatita-page-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .gatita-page-content {
        padding: 20px 15px;
    }
}

/* Override any theme styles that might interfere */
.gatita-page-content .swiper {
    margin: 0 auto;
    position: relative;
    overflow: hidden;
    list-style: none;
    padding: 0;
    z-index: 1;
}

.gatita-page-content .swiper-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    z-index: 1;
    display: flex;
    transition-property: transform;
    box-sizing: content-box;
}

.gatita-page-content .swiper-slide {
    flex-shrink: 0;
    width: 100%;
    height: 100%;
    position: relative;
    transition-property: transform;
}

/* Additional Swiper Styles */
.gatita-page-content .swiper-button-next,
.gatita-page-content .swiper-button-prev {
    position: absolute;
    top: 50%;
    width: 30px;
    height: 30px;
    margin-top: -15px;
    z-index: 100;
    cursor: pointer;
    color: #fff;
    background-color: var(--gatita-brand-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.gatita-page-content .swiper-button-next {
    right: -35px;
}

.gatita-page-content .swiper-button-prev {
    left: -35px;
}

.gatita-page-content .swiper-button-next:hover,
.gatita-page-content .swiper-button-prev:hover {
    background-color: var(--gatita-brand-secondary);
    transform: scale(1.1);
}

.gatita-page-content .swiper-button-disabled {
    opacity: 0.35;
    cursor: not-allowed;
    pointer-events: none;
}

@media (max-width: 768px) {
    .gatita-page-content .swiper-button-next,
    .gatita-page-content .swiper-button-prev {
        display: none !important;
    }
}
</style> 