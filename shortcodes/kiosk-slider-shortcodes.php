<?php

/**
 * Slider Shortcode functionality.
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

class Kiosk_Slider_Shortcodes extends Base_Registrar {
  protected $feed_helper;

  public function __construct( $feed_helper ) {
    $this->load_dependencies();
    $this->define_hooks();
    $this->feed_helper = $feed_helper;
  }

  public function define_hooks() {
    $this->add_shortcode( 'kiosk-slider', $this, 'kiosk_slider' );
  }

  /**
   * [kiosk_slider limit='20']
   *
   * @param $atts array
   * Generates a <div> tag with slider from rss feed to display as slider
   * To add feed from other urls update $feed_urls we can improve by
   * accepting associative array
   */
  public function kiosk_slider( $atts, $content = null ) {
    $atts               = shortcode_atts(
        array(
          'limit'       => '20',
          'feed_urls'   => array(
              'https://api.flickr.com/services/feeds/photos_public.gne?id=55424394@N03&lang=en-us&format=rss_200',
          )
        ),
        $atts
    );
    $feed_urls    = $atts['feed_urls'];
    if ( ! is_array( $feed_urls ) ) {
      $feed_urls  = explode( ',', $feed_urls );
    }
    $limit           = $atts['limit'];
    $list_items      = array();
    $items           = $this->feed_helper->get_feed_data( $feed_urls );
    $carousel_slider = '<div class="kiosk-gallery__no-data">Gallery Not Available</div>';
    if ( ! empty( $items ) ) {
      usort( $items, array( 'Kiosk_WP\Feed_Helper', 'rss_sort_date_dsc' ) );
      $list_items      = Feed_Helper::extract_data_from_flickr_feed( $items, $limit );
      $carousel_slider = $this->get_gallery_carousel_slider( $list_items );
    }
    $kiosk_slider_div   = '<div class="kiosk-gallery">' . $carousel_slider . '</div>';
    return $kiosk_slider_div;
  }
  /**
   * Creates required template and invokes helper function to create carousel
   * @param array
   * @return string
   */
  private function get_gallery_carousel_slider( $list_items ) {
    $prefix          = 'kiosk-gallery';
    $carousel_slider = '';
    $layout_template = <<<HTML
    <img src="%s" alt="%s">
    <div  class="kiosk-gallery__slider-caption">
       %s
    </div>
HTML;
    if ( count( $list_items ) > 0 ) {
      $carousel_slider   = Carosuel_Slider_Helper::generate_carousel_slider(
          $prefix,
          $layout_template,
          $list_items
      );
    }
    return $carousel_slider;
  }

}