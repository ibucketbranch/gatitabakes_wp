<?php
/**
 * The footer for our theme
 */
?>
    <footer id="colophon" class="site-footer">
        <div class="site-info">
            <?php
            printf(
                esc_html__('© %d %s', 'gatita-bakes'),
                date('Y'),
                get_bloginfo('name')
            );
            ?>
        </div>
    </footer>
</div><!-- #page -->
<?php wp_footer(); ?>
</body>
</html> 