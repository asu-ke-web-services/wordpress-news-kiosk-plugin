<?php

/**
 * Posts Shortcode functionality.
 *
 * Provides shortcodes for users to use in Wordpress
 *
 */
namespace Kiosk_WP;

// Avoid direct calls to this file
if ( ! defined( 'KIOSK_WP_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  trigger_error( 'Error: This file should be accessed directly', E_USER_ERROR );
}

class Kiosk_Posts_Shortcodes extends Base_Registrar {
  protected $plugin_slug;
  protected $version;

  public function __construct()  {
    $this->plugin_slug = 'kiosk-post-shortcodes';
    $this->version     = '0.1';

    $this->load_dependencies();
    $this->define_hooks();
  }

  /**
   * @override
   */
  public function load_dependencies() {
    require_once( plugin_dir_path( __FILE__ ) . '../helpers/base-path-helper.php' );
  }

  public function define_hooks() {
    $this->add_shortcode( 'kiosk-posts', $this, 'kiosk_posts' );
  }

  /**
   * [kiosk_posts tags="t,a,g,s"]
   *
   * @param $atts array
   * Generates a <div> tag with images from post to display as slider
   */
  public function kiosk_posts( $atts, $content = null ) {
    $image_regex           = '/(?<!_)src=([\'"])?(.*?)\\1/';
    $current_post_count    = 0;
    $limit                 = 20;
    $cureent_offset_posts  = 0;
    $exit_while            = false;
    $atts                  = shortcode_atts(
        array(
          'tags'  => '',
        ),
        $atts
    );
    $kiosk_template      = '<li %s data-target="#kiosk-slider" data-slide-to="%d"></li>';
    $kiosk_item_template = <<<HTML
    <div class="item %s">
      <img src="%s" class="img-responsive img-thumbnail" alt="%s"/>
      <div class="kiosk-slider-caption carousel-caption">
       <h3>%s</h3>
      </div>
    </div>
HTML;
    // Prepare carousel
    $div_listitems = <<<HTML
      <div id="kiosk-slider" class="carousel slide" data-ride="carousel">
         <ol class="kiosk-slider carousel-indicators">
HTML;

    $div_sliders        = '<div class="carousel-inner" role="listbox">';

    while ( ! $exit_while ) {
      $query_post_options  = array(
        'post_type'        => array( 'attachment', 'page', 'post' ),
        'posts_per_page'   => $limit,
        'orderby'          => 'post_date',
        'order'            => 'DESC',
        'tag'              => $atts['tags'],
        'offset'           => $cureent_offset_posts,
        'post_status'      => 'publish',
      );

      $cureent_offset_posts       = $cureent_offset_posts + $limit;
      $posts              = get_posts( $query_post_options );
      if ( $posts ) {
        /* Query the featured images which are attachments by thumbnail ID by passing post ID.
        * Store current post item  and parse it the check if post has any images in the body.
        * Regex string to search for images
        * Run preg_match_all to grab all the images and save the results in $pics
        * Query any custom fields for page_feature_image
        * Query any custom fields for kiosk-end-date
        * Show posts which are having either image as attachment or images in body of post
        * or image with custom field and not expired
        */
        foreach ( $posts as $post ){
          $image_attributes   = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ) ); // returns an array
          $content            = $post->post_content;
          preg_match_all( $image_regex, $content, $pics );
          $page_feature_image = get_post_meta( $post->ID, 'page_feature_image', true );
          $kiosk_end_date     = get_post_meta( $post->ID, 'kiosk-end-date', true );
          $today              = strtotime( date( 'd-m-Y' ) );
          $expiration_date    = strtotime( $kiosk_end_date );

          //Do not show posts which are expired or doesn't have expiration date specified
          if ( empty($expiration_date) || $expiration_date < $today ) { // if expiration date is in the past
            continue;
          }

          if ( 0 == $current_post_count ){
            $div_listitems_active = ' class = "active" ';
            $div_slider_active    = ' active ';
          }else {
            $div_listitems_active = '';
            $div_slider_active    = '';
          }

          //Check if featured image is present or not
          if ( $image_attributes ){
            $div_listitems .= sprintf(
                $kiosk_template,
                $div_listitems_active,
                $current_post_count
            );
            $div_sliders   .= sprintf(
                $kiosk_item_template,
                $div_slider_active,
                $image_attributes[0],
                $post->post_title,
                apply_filters( 'the_title', $post->post_title )
            );
            $current_post_count++;

            //Check if posts had images in its body
          }else if ( ! empty($pics[2]) ) {
            $div_listitems .= sprintf(
                $kiosk_template,
                $div_listitems_active,
                $current_post_count
            );
            $div_sliders   .= sprintf(
                $kiosk_item_template,
                $div_slider_active,
                $pics[2][0],
                $post->post_title,
                apply_filters( 'the_title', $post->post_title )
            );
            $current_post_count++;
            //Check if page_feature_image custom field has image and if it absolute else make absolute url from relative url //TO DO
          }else if ( ! empty($page_feature_image) ){
            if ( parse_url( $page_feature_image, PHP_URL_SCHEME ) == '' ) {
              $page_feature_image = home_url( $page_feature_image );
            }
             $div_listitems .= sprintf(
                 $kiosk_template,
                 $div_listitems_active,
                 $current_post_count
             );
             $div_sliders   .= sprintf(
                 $kiosk_item_template,
                 $div_slider_active,
                 $page_feature_image,
                 $post->post_title,
                 apply_filters( 'the_title', $post->post_title )
             );
             $current_post_count++;
          }
        }
      }else {
        //No posts or reached end of posts query by offset
        $exit_while = true;
      }
    }
     $div_listitems .= '</ol>';
     $div_listitems .= $div_sliders;
     $div_listitems .= '</div>';
     $div_listitems .= '</div>';
    return ( 0 == $current_post_count ? '' : $div_listitems );
  }
}