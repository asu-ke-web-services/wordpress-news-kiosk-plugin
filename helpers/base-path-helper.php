<?php

/**
 * Base Path Helper
 *
 * WordPress only file
 */

namespace Kiosk_WP;

// Avoid direct calls to this file
if ( ! defined( 'KIOSK_WP_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  trigger_error( 'Error: This file should be accessed directly', E_USER_ERROR );
}

/**
 * Collection of helper functions for determining base paths
 */
class Base_Path_Helper {
  /**
   * Returns a base path to the current WordPress site and appends the given array
   * joined together with forward slases.
   *
   * Always returns a trailing slash.
   *
   * @param Array $path_parts An array of string that should be joined together at the end of the base path.
   * @return String
   * @static
   */
  public static function base_path( $path_parts = array() ) {
    $extra = '';

    $extra = implode( '/', $path_parts );

    $trailing_slash = ( empty( $extra ) ? '' : '/' );

    return get_site_url() . '/' . $extra . $trailing_slash;
  }
}
