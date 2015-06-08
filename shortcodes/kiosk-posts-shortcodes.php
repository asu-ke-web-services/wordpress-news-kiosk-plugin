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
  exit();
}

class Kiosk_Posts_Shortcodes extends Base_Registrar {
  protected $plugin_slug;
  protected $version;

  public function __construct() {
    $this->plugin_slug = 'kiosk-post-shortcodes';
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
    $this->add_shortcode( 'kiosk-posts', $this, 'kiosk_posts' );
  }

  /**
   * [kiosk_posts tags="t,a,g,s" default_image="http://www.example1.jpg,http://www.example2.jpg,http://www.example3.jpg"]
   *
   * @param $atts array
   * Generates a <div> tag with images from post to display as slider
   * Query the featured images which are attachments by thumbnail ID by passing post ID.
   * Store current post item  and parse it the check if post has any images in the body.
   * Regex string to search for images
   * Run preg_match_all to grab all the images and save the results in $pics
   * Query any custom fields for page_feature_image
   * Query any custom fields for kiosk-end-date
   * Show posts which are having either image as attachment or images in body of post
   * or image with custom field and not expired
   */
  public function kiosk_posts( $atts, $content = null ) {
    $current_post_count     = 0;
    $limit                  = 20;
    $cureent_offset_posts   = 0;
    $atts                   = shortcode_atts(
        array(
          'tags'            => '',
          'default_image'   => '',
        ),
        $atts
    );
    $default_image          = $atts['default_image'];
    $div_list_items          = $this->get_posts_block_tags( 'carousel_div_start' ) . $this->get_posts_block_tags( 'carousel_ol_tag_start' );
    $carousel_li_tag        = $this->get_posts_block_tags( 'carousel_li_tag' );
    $carousel_div_item      = $this->get_posts_block_tags( 'carousel_div_item' );
    // Prepare carousel
    $div_sliders            = $this->get_posts_block_tags( 'carousel_div_sliders_start' );
    $exit_while             = false;
    while ( ! $exit_while ) {
      $query_post_options   = array(
        'post_type'         => array( 'attachment', 'page', 'post' ),
        'posts_per_page'    => $limit,
        'orderby'           => 'post_date',
        'order'             => 'DESC',
        'tag'               => $atts['tags'],
        'offset'            => $cureent_offset_posts,
        'post_status'       => 'publish',
      );
      $cureent_offset_posts = $cureent_offset_posts + $limit;
      $posts                = get_posts( $query_post_options );
      if ( $posts ) {
        foreach ( $posts as $post ) {
          $image_attributes   = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ) ); // returns an array
          $content            = $post->post_content;
          // Take the image tag src attribute from the content and store it in pics variable
          //(?<!_)negative lookbehind  [\'"] match either ' or " (abc)capture group \1 backreference to group #1
          preg_match_all( '/(?<!_)src=([\'"])?(.*?)\\1/', $content, $pics );
          $page_feature_image = get_post_meta( $post->ID, 'page_feature_image', true );
          $kiosk_end_date     = get_post_meta( $post->ID, 'kiosk-end-date', true );
          $today              = strtotime( date( 'd-m-Y' ) );
          $expiration_date    = strtotime( $kiosk_end_date );
          //Do not show posts which are expired or doesn't have expiration date specified
          if ( empty( $expiration_date ) || $expiration_date < $today ) { // if expiration date is in the past
            continue;
          }
          if ( 0 == $current_post_count ) {
            $div_li_active        = ' class = "active" ';
            $div_slider_active    = ' active ';
          }else {
            $div_li_active        = '';
            $div_slider_active    = '';
          }
          //Check if featured image is present or not
          if ( $image_attributes ) {
            // Append new li item carousel
            $div_list_items .= sprintf(
                $carousel_li_tag,
                $div_li_active,
                $current_post_count
            );
            // Append new div item to carousel realted to li
            $div_sliders   .= sprintf(
                $carousel_div_item,
                $div_slider_active,
                $image_attributes[0],
                $post->post_title
                //apply_filters( 'the_title', $post->post_title )
            );
            $current_post_count++;
            //Check if posts had images in its body
          }else if ( ! empty($pics[2]) ) {
            // Append new li item carousel
            $div_list_items .= sprintf(
                $carousel_li_tag,
                $div_li_active,
                $current_post_count
            );
            if ( parse_url( $pics[2][0], PHP_URL_SCHEME ) == '' ) {
              $pics[2][0] = home_url( $pics[2][0] );
            }
            // Append new div item to carousel realted to li
            $div_sliders   .= sprintf(
                $carousel_div_item,
                $div_slider_active,
                $pics[2][0],
                $post->post_title
            );
            $current_post_count++;
            //Check if page_feature_image custom field has image and if it absolute else make absolute url from relative url //TO DO
          }else if ( ! empty( $page_feature_image ) ) {
            if ( parse_url( $page_feature_image, PHP_URL_SCHEME ) == '' ) {
              $page_feature_image = home_url( $page_feature_image );
            }
            // Append new li item carousel
            $div_list_items .= sprintf(
                $carousel_li_tag,
                $div_li_active,
                $current_post_count
            );
            // Append new div item to carousel realted to li
            $div_sliders   .= sprintf(
                $carousel_div_item,
                $div_slider_active,
                $page_feature_image,
                $post->post_title
            );
            $current_post_count++;
          }
        }
      }else {
        //No posts or reached end of posts query by offset
        $exit_while = true;
      }
    }
    if ( 0 == $current_post_count && ! empty( $default_image ) ) {
      $default_image_array = explode( ',', $default_image );
      for ( $k = 0 ; $k < count( $default_image_array ); $k++ ) {
        if ( 0 == $k ) {
          $div_li_active        = ' class = "active" ';
          $div_slider_active    = ' active ';
        }else {
          $div_li_active        = '';
          $div_slider_active    = '';
        }
        // Append new li item carousel
        $div_list_items .= sprintf(
            $carousel_li_tag,
            $div_li_active,
            $k
        );
        if ( parse_url( $default_image_array[ $k ], PHP_URL_SCHEME ) == '' ) {
          $default_image_array[ $k ] = home_url( $default_image_array[ $k ] );
        }
        // Append new div item to carousel realted to li
        $div_sliders   .= sprintf(
            $carousel_div_item,
            $div_slider_active,
            trim( $default_image_array[ $k ] ),
            ''
        );
        $current_post_count++;
      }
    }
    // Close the ol tag
    $div_list_items    .= $this->get_posts_block_tags( 'carousel_ol_end_tag' );
    // Append all the div sliders for each li item in ol tag
    $div_list_items    .= $div_sliders;
    // Close div sliders
    $div_list_items    .= $this->get_posts_block_tags( 'carousel_div_slider_end' );
    // Close the main carousel div
    $div_list_items    .= $this->get_posts_block_tags( 'carousel_div_end' );
    $kiosk_events_div  = '<div class="kiosk-events">' . $div_list_items . '</div>';
    return ( 0 == $current_post_count ? '' : $kiosk_events_div );
  }
  /**
   * get_posts_block_tags( $tag_name ) returns the tags requested to create the events
   * div block using carousel effect
   * @param string
   * @return string
   */
  function get_posts_block_tags( $tag_name ){
    switch ( $tag_name ){
      case 'carousel_div_start':
        $carousel_template = '<div id="kiosk_events_slider" class="kiosk-events__slider carousel slide" data-ride="carousel">';
        break;
      case 'carousel_ol_tag_start':
        $carousel_template = '<ol class="kiosk-events__slider__carousel-indicators carousel-indicators">';
        break;
      case 'carousel_ol_end_tag':
        $carousel_template = '</ol>';
        break;
      case 'carousel_li_tag':
        $carousel_template = '<li %s data-target="#kiosk_events_slider" data-slide-to="%d"></li>';
        break;
      case 'carousel_div_sliders_start':
        $carousel_template        = '<div class="carousel-inner" role="listbox">';
        break;
      case 'carousel_div_item':
        $carousel_template = <<<HTML
          <div class="item %s center-block kiosk-events__slider__image">
            <span class="helper"></span>
            <img src="%s" class="img-responsive center-block" alt="%s">
          </div>
HTML;
  break;
      case 'carousel_div_slider_end':
        $carousel_template = '</div>';
        break;
      case 'carousel_div_end':
        $carousel_template = '</div>';
        break;
      default:
        $carousel_template = '';
        break;
    }
    return $carousel_template;
  }
}