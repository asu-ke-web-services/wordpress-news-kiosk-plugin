<?php

namespace Kiosk_WP;

// Avoid direct calls to this file
if ( ! defined( 'KIOSK_WP_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

/**
 * Client Helper
 *
 * Collection of helper functions for determining Client data.
 */
class Client_Helper {
  /**
   * Attempts to get the client's ip address
   *
   * @return String
   */
  // @codingStandardsIgnoreStart
  public static function client_ip() {
    $ipaddress = '';
    if ( array_key_exists( 'HTTP_CLIENT_IP', $_SERVER ) && $_SERVER['HTTP_CLIENT_IP'] ) {
      $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } else if ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER ) && $_SERVER['HTTP_X_FORWARDED_FOR'] ) {
      $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if ( array_key_exists( 'HTTP_X_FORWARDED', $_SERVER ) && $_SERVER['HTTP_X_FORWARDED'] ) {
      $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    } else if ( array_key_exists( 'HTTP_FORWARDED_FOR', $_SERVER ) && $_SERVER['HTTP_FORWARDED_FOR'] ) {
      $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } else if ( array_key_exists( 'HTTP_FORWARDED', $_SERVER ) && $_SERVER['HTTP_FORWARDED'] ) {
      $ipaddress = $_SERVER['HTTP_FORWARDED'];
    } else if ( array_key_exists( 'REMOTE_ADDR', $_SERVER ) && $_SERVER['REMOTE_ADDR'] ) {
      $ipaddress = $_SERVER['REMOTE_ADDR'];
    } else {
      $ipaddress = 'UNKNOWN';
    }
    return $ipaddress;
  }
  // @codingStandardsIgnoreEnd
}
