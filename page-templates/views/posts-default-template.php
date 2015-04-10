<?php
/*
Template Name: Posts Default Template
Description: Simple scaffolding to display the posts content and the sidebar.
*/

// Avoid direct calls to this file
if ( ! defined( 'KIOSK_WP_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  trigger_error( 'Error: This file should be accessed directly', E_USER_ERROR );

}
wp_head(); ?>

<div id="kisok-template" class="widecolumn">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
    the_content();
endwhile; endif; ?>
</div>
<?php wp_footer();