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
   */
  
  public function kiosk_posts( $atts, $content = null ) {
    $html  = '';
    $count = 0;
    $limit = 20;
    $atts = shortcode_atts( array(
      'tags'  => '',
      ), $atts );
    $args  = array(
      'post_type'      => array ('attachment','page','post'),
      'posts_per_page' => $limit+1,
      'category_name'  => $atts['tags'],
      'offset'         => 0,
      );
    $active = ' class = "active" '; 
    $attachments = get_posts( $args );
    if ( $attachments ) {
      $html .= '<div id="kiosk-slider" class="carousel slide" data-ride="carousel">';
      $html .=   '<ol class="kiosk-slider carousel-indicators">';
      foreach ( $attachments as $post ){
        $image_attributes = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID) ); // returns an array
        $content = $post->post_content;
        $searchimages = '/(?<!_)src=([\'"])?(.*?)\\1/';
        /*Run preg_match_all to grab all the images and save the results in $pics*/
        preg_match_all( $searchimages, $content, $pics );
        $page_feature_image = get_post_meta($post->ID, 'page_feature_image', true);
        if ( $count < $limit ){
          if( $image_attributes ){
            $html .= '<li '.$active.' data-target="#kiosk-slider" data-slide-to="'.$count.'"></li>';
            $count++;    
            $active = '';  
          }else if ( !empty($pics[2]) ) {       
            // preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $row->introtext, $matches);
            // Check to see if we have at least 1 image
           $iNumberOfPics = count($pics[2]);
           if ( $iNumberOfPics > 0 ) {
            $html .= '<li '.$active.' data-target="#kiosk-slider" data-slide-to="'.$count.'"></li>';
            $count++; 
            $active = ''; 
          }
        }else if ( !empty($page_feature_image) && parse_url($page_feature_image, PHP_URL_SCHEME) != '' ){
         $html .= '<li '.$active.' data-target="#kiosk-slider" data-slide-to="'.$count.'"></li>';
         $count++; 
         $active = ''; 
       }
     }else{
      break;
    }
  }
  $html .= '</ol>';
  $html .= '<div class="carousel-inner" role="listbox">';
  $count2 = 0;
  $active = ' active'; 
  foreach ( $attachments as $post ){
        $image_attributes = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID) ); // returns an array
        $content = $post->post_content;
        $searchimages = '/(?<!_)src=([\'"])?(.*?)\\1/';
        /*Run preg_match_all to grab all the images and save the results in $pics*/
        preg_match_all( $searchimages, $content, $pics );
        $page_feature_image = get_post_meta($post->ID, 'page_feature_image', true);
        if ( $count2 < $limit ){
          if( $image_attributes ){
           $html  .= '<div class="item'.$active.'">';
           $html  .=  '<img src="'.$image_attributes[0].'" class="img-responsive img-rounded" alt="'.$post->post_title.'"/>';
           $html  .=  '<div class="carousel-caption">';
           $html  .=    '<h3>'.apply_filters( 'the_title', $post->post_title ).'</h3>';
           $html  .=  '</div>';
           $html  .= '</div>';
           $active = '';   
           $count2++;        
         }else if ( !empty($pics[2]) ){
            // Check to see if we have at least 1 image
          $iNumberOfPics = count($pics[2]);
          if ( $iNumberOfPics > 0  ) {
           $html  .= '<div class="item'.$active.'">';
           $html  .=  '<img src="'.$pics[2][0].'" class="img-responsive img-rounded" alt="'.$post->post_title.'"/>';
           $html  .=  '<div class="carousel-caption">';
           $html  .=    '<h3>'.apply_filters( 'the_title', $post->post_title ).'</h3>';
           $html  .=  '</div>';
           $html  .= '</div>';
           $active = '';  
           $count2++;      
         }
       }else if ( !empty($page_feature_image) && parse_url($page_feature_image, PHP_URL_SCHEME) != '' ){ 
         $html  .= '<div class="item'.$active.'">';
         $html  .=  '<img src="'.$page_feature_image.'" class="img-responsive img-rounded" alt="'.$post->post_title.'"/>';
         $html  .=  '<div class="carousel-caption">';
         $html  .=    '<h3>'.apply_filters( 'the_title', $post->post_title ).'</h3>';
         $html  .=  '</div>';
         $html  .= '</div>';
         $active = '';  
         $count2++;    
       }
     }else{
      break;
    }  
  }
  $html .= '</div>';
  $html .= '</div>';
}
return $html;
}
}