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

class Kiosk_Title_Shortcodes extends Base_Registrar {
  protected $plugin_slug;
  protected $version;

  public function __construct()  {
    $this->plugin_slug = 'kiosk-title-shortcodes';
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
    $this->add_shortcode( 'kiosk-title', $this, 'kiosk_title' );
  }

  /**
   * [kiosk_title]
   *
   * @param $atts array
   * Generates a <div> tag with asu_title from rss feed to display as asu_title
   *
   */
  public function kiosk_title( $atts, $content = null ) {
    $site_url = get_site_url();
    $asu_title = <<<HTML
    <div class="kiosk-title__logo">
      <img src="{$site_url}/wp-content/plugins/wordpress-news-kiosk-plugin/assets/images/sos-logo.png" class="img-responsive">
      </div>
HTML;
    $kiosk_title_div = '<div class="kiosk-title">' . $asu_title . '</div>';
    return $kiosk_title_div;
  }
}