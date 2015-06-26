<?php
  /**
   * JSON Decode helper
   *
   */
namespace Kiosk_WP;
class Json_Decode_Helper {

  /*
    http://stackoverflow.com/questions/17219916/
    json-decode-returns-json-error-syntax-but-online-formatter-says-
    the-json-is-ok */
  public static function remove_unwanted_chars( $json ) {
    // This will remove unwanted characters.
    for ( $i = 0; $i <= 31; ++$i ) {
      $json = str_replace( chr( $i ), '', $json );
    }
    $json = str_replace( chr( 127 ), '', $json );

    // This is the most common part
    // Some file begins with 'efbbbf' to mark the beginning of the file.
    // (binary level) here we detect it and we remove it, basically
    // it's the first 3 characters
    if ( 0 === strpos( bin2hex( $json ), 'efbbbf' ) ) {
      $json = substr( $json, 3 );
    }
    return $json;
  }
}