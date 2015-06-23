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
  protected $plugin_slug;
  protected $version;
  protected $feed_helper;

  public function __construct( $feed_helper ) {
    $this->plugin_slug = 'kiosk-slider-shortcodes';
    $this->version     = '0.1';
    $this->load_dependencies();
    $this->define_hooks();
    //$this->feed_helper = new Feed_Helper();
    $this->feed_helper = $feed_helper;
  }

  /**
   * @override
   */
  public function load_dependencies() {
  }

  public function define_hooks() {
    $this->add_shortcode( 'kiosk-slider', $this, 'kiosk_slider' );
  }

  /**
   * [kiosk_slider limit='20']
   *
   * @param $atts array
   * Generates a <div> tag with slider from rss feed to display as slider
   * To add feed from other urls update $feed_urls_array we can improve by accepting associative array
   *
   */
  public function kiosk_slider( $atts, $content = null ) {
    $total_feed_count   = 0;
    $atts               = shortcode_atts(
        array(
          'limit'       => '20',
          'feed_urls'   => array(
                            'https://api.flickr.com/services/feeds/photos_public.gne?id=55424394@N03&lang=en-us&format=rss_200',
                          )
        ),
        $atts
    );
    $feed_urls_array    = $atts['feed_urls'];
    if ( ! is_array( $feed_urls_array ) ) {
      $feed_urls_array  = explode( ',', $feed_urls_array );
    }
    $limit              = $atts['limit'];
    $list_items         = array();
    $items              = $this->feed_helper->get_feed_data( $feed_urls_array );
    usort( $items, array( 'Kiosk_WP\Feed_Helper', 'rss_sort_date_dsc' ) );
    $list_items         = Feed_Helper::extract_images_from_flicker_feed( $items, $limit );
    $carousel_slider    = $this->get_gallery_carousel_slider( $list_items );
    $kiosk_slider_div   = '<div class="kiosk-gallery">' . $carousel_slider . '</div>';
    return $kiosk_slider_div;
  }

  private function get_gallery_carousel_slider( $list_items ){
    $prefix          = 'kiosk-gallery';
    $carousel_slider = '';
    $layout_template = <<<HTML
    <img src="%s" alt="%s">
      <div class="kiosk-gallery__slider-caption carousel-caption">
       <h3><strong>%s</strong></h3>
      </div>
HTML;
    if ( count( $list_items ) > 0 ){
      $carousel_slider   = Carosuel_Slider_Helper::generate_carousel_slider( $prefix, $layout_template, $list_items );
    }
    return $carousel_slider;
  }

}