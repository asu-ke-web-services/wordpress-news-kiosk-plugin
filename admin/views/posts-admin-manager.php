<?php

/**
 * Posts Admin Manager
 */

require_once( plugin_dir_path( __FILE__ ) . '/../posts-admin.php' );
?>

<!-- <h2>Posts Settings</h2> -->

<div class="wrap" id="kiosk-posts-display-data">
  <form method="post" action="options.php">
    <?php
        // This prints out all hidden setting fields
        settings_fields( \Kiosk_WP\Posts_Admin::$options_group );
        do_settings_sections( \Kiosk_WP\Posts_Admin::$section_name );
        submit_button();
    ?>
  </form>
</div>