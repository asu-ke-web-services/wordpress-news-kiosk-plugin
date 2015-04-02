<?php
/**
 * @see http://www.wpexplorer.com/wordpress-page-templates-plugin/
 */

namespace Kiosk_WP;

// Avoid direct calls to this file
if ( ! defined( 'KIOSK_WP_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

class Posts_Page_Templates extends Base_Registrar {
  protected $plugin_slug;
  protected $version;
  protected $templates;


  public function __construct() {
    $this->plugin_slug = 'kiosk-post-page-template';
    $this->version = '0.1';
    $this->templates = array('posts-default-template.php' => 'KIOSK');

    $this->load_dependencies();
    $this->define_hooks();
  }

  public function load_dependencies() {
    // No dependencies for now
  }

  public function define_hooks() {
    $this->add_filter( 'page_attributes_dropdown_pages_args', $this, 'page_attributes_dropdown_pages_args' );
    $this->add_filter( 'wp_insert_post_data', $this, 'wp_insert_post_data' );
    $this->add_filter( 'template_include', $this, 'template_include' );
  }

  public function page_attributes_dropdown_pages_args( $atts ) {
    $this->register_project_templates();

    return $atts;
  }

  public function wp_insert_post_data( $atts ) {
    $this->register_project_templates();

    return $atts;
  }

  public function template_include( $template ) {
    global $post;

    if ( $post === null ) {
      return $template;
    }

    if ( ! isset( $this->templates[get_post_meta( $post->ID, '_wp_page_template', true )] ) ) {
      return $template;
    }

    if ( is_search() ) {
      return $template;
    }

    $file = plugin_dir_path( __FILE__ ) . 'views/' . get_post_meta( $post->ID, '_wp_page_template', true );

    if ( file_exists( $file ) ) {
      return $file;
    } else {
      echo $file;
    }

    return $template;
  }

  /**
   * Register project templates
   */
  protected function register_project_templates () {
    // Create the key used for the themes cache
    $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

    // Retrieve the cache list
    // If it doesn't exist, or it's emty prepare an array
    $templates = wp_get_theme()->get_page_templates();
    if ( empty( $templates ) ) {
      $templates = array();
    }

    // New cache, therefore remove the old one
    wp_cache_delete( $cache_key, 'themes' );

    // Now add our template to the list of templates by merging our templates
    // with the existing templates array frmo the cache.
    $templates = array_merge( $templates, $this->templates );

    // Add the modified cache to allow WordPress to pick it up for listing
    // available templates
    wp_cache_add( $cache_key, $templates, 'themes', 1800 );
  }

}