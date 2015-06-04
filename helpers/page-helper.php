<?php

namespace Kiosk_WP;

// Avoid direct calls to this file
if ( ! defined( 'KIOSK_WP_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

public class Page_Helper {
  // Send header data if we have it
  public static function include_headers( $header_data = array() ) {
    if ( ! headers_sent() ) {
      if ( isset( $header_data ) ) {
        if ( is_array( $header_data ) ) {
          foreach ( $header_data as $_ => $value ) {
            // add headers
            header( $value );
          }
        }
      }
    }
  }
}
