<?php
/**
 * Gatita Bakes Child Theme functions
 * Author: Bucketbranch
 */

// Enqueue parent theme styles
add_action('wp_enqueue_scripts', 'gatita_bakes_enqueue_parent_styles');
function gatita_bakes_enqueue_parent_styles() {
    wp_enqueue_style('astra-style', get_template_directory_uri() . '/style.css');
}
