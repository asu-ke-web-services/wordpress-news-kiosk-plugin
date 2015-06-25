<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
  $_tests_dir = '/tmp/wordpress-tests-lib'; }

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
  require dirname( dirname( __FILE__ ) ) . '/kiosk-plugin.php';
}


// Add the kiosk-php and the plugin root to the include path:
$api_path         = dirname( dirname( __FILE__ ) ).'/.standards/kiosk-php';
$plugin_path      = dirname( dirname( __FILE__ ) );
$new_include_path = get_include_path(). PATH_SEPARATOR . $plugin_path
    . PATH_SEPARATOR . $api_path;

// @codingStandardsIgnoreStart
// echo 'setting php include path to '.$new_include_path;
// @codingStandardsIgnoreEnd


// Set the current enviorment
set_include_path( $new_include_path );
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require_once $_tests_dir . '/includes/bootstrap.php';
require_once dirname( __FILE__ ) . '/data-loader.php';
require_once ABSPATH . 'wp-includes/class-simplepie.php';
// include the required file to pull feed
require_once ABSPATH . 'wp-includes/feed.php';


