<?php

/**
 * Title Shortcode functionality.
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
  protected $plugin_slug;
  protected $version;

  public function __construct() {
    $this->plugin_slug = 'kiosk-logo-shortcodes';
    $this->version     = '0.1';

    $this->load_dependencies();
    $this->define_hooks();
  }

  /**
   * @override
   */
  public function load_dependencies() {
  }

  public function define_hooks() {
    $this->add_shortcode( 'kiosk-logo', $this, 'kiosk_logo' );
  }

  /**
   * [kiosk_logo path='url']
   *
   * Generates a <div> tag with asu_title from rss feed to display as asu_title
   * @param $atts array
   */
  public function kiosk_logo( $atts, $content = null ) {
    $site_url   = get_site_url();
    $atts                   = shortcode_atts(
        array(
          'path'            => "{$site_url}/wp-content/plugins/wordpress-news-kiosk-plugin/assets/images/sos-logo.png",
        ),
        $atts
    );
    $asu_title  = <<<HTML
    <div class="kiosk-title__logo">
      <img src="{$atts['path']}" class="img-responsive">
      </div>
HTML;
    $kiosk_title_div = '<div class="kiosk-title">' . $asu_title . '</div>';
    return $kiosk_title_div;
  }
}