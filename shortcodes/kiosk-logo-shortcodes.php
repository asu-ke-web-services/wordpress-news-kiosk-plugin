<?php

/**
 * Logo Shortcode functionality.
 *
 * Provides shortcodes for users to use in Wordpress
 *
 */
namespace Kiosk_WP;

// Avoid direct calls to this file
if ( ! defined( 'KIOSK_WP_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

class Kiosk_Logo_Shortcodes extends Base_Registrar {
  public function __construct() {
    $this->define_hooks();
  }

  public function define_hooks() {
    $this->add_shortcode( 'kiosk-logo', $this, 'kiosk_logo' );
  }

  /**
   * [kiosk_logo path='url']
   *
   * Generates a <div> tag with asu_logo from rss feed to display as asu_logo
   * @param $atts array
   */
  public function kiosk_logo( $atts, $content = null ) {
    $site_url = get_site_url();
    $atts     = shortcode_atts(
        array(
          'path' => "{$site_url}/wp-content/plugins/wordpress-news-kiosk-plugin/assets/images/sos-logo.png",
        ),
        $atts
    );
    $asu_logo  = <<<HTML
    <div class="kiosk-logo__logo">
      <img src="{$atts['path']}"
       class="kiosk-logo__image">
      </div>
HTML;
    $kiosk_logo_div = '<div class="kiosk-logo">' . $asu_logo . '</div>';
    return $kiosk_logo_div;
  }
}