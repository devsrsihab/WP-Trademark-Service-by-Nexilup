<?php
if ( ! defined('ABSPATH') ) exit;

get_header();

// Ensure main frontend CSS is loaded
wp_enqueue_style('tm-frontend-css');
?>

<div class="tm-order-page tm-step1-page">
  <div class="tm-order-container">
    <?php echo do_shortcode('[tm_service_form]'); ?>
  </div>
</div>

<?php get_footer(); ?>
