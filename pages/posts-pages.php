<?php

/**
 * Posts Pages
 */
namespace Kiosk_WP;

// Avoid direct calls to this file
if ( ! defined( 'KIOSK_WP_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

class Posts_Pages extends Base_Registrar {
  public static $page_name                  = 'posts';
  public static $page_name_readible         = 'Posts';
  public static $param_post_slug           = 'post_slug';
  public static $param_post_year           = 'post_year';
  public static $param_posts_category_slug = 'post_category_slug';
  protected $plugin_slug;
  protected $version;
  protected $page_data = null;

  /**
   * Static function!
   */
  public static function filter_param( $atts ) {
    if ( array_key_exists( Posts_Pages::$param_posts_category_slug,  $atts ) ) {
      // TODO make sure the param_posts_category_slug is a real slug
      // or ALL

      if ( empty( $atts[ Posts_Pages::$param_posts_category_slug ] ) ) {
        $atts[ Posts_Pages::$param_posts_category_slug ] = 'all';
      }
    }

    return $atts;
  }

  public function __construct()  {
    $this->plugin_slug = 'kiosk-post-page';
    $this->version     = '0.1';

    $this->load_dependencies();
    $this->define_hooks();
  }

  /**
   * @override
   */
  public function load_dependencies() {
    require_once( plugin_dir_path( __FILE__ ) . '../helpers/client-helper.php' );
    require_once( plugin_dir_path( __FILE__ ) . '../helpers/page-helper.php' );
  }

  /**
   * Add filters and action
   *
   * The majority of the work happend in the action 'wp'
   */
  public function define_hooks() {
    // Register Actions
    $this->add_action( 'wp', $this, 'setup' );
    $this->add_action( 'wp_head', $this, 'wp_head' );
    $this->add_action( 'the_content', $this, 'the_content' );

    // Register Filters
    $this->add_filter( 'query_vars', $this, 'query_vars' );
  }

  /**
   * Preload all of the data needed for the page
   */
  public function setup() {
    if ( $this->is_correct_page() ) {
      $this->page_data = $this->get_posts_data();

      if ( isset( $this->page_data->http_header ) ) {
        Page_Helper::include_headers( $this->page_data->http_header );
      }
    }
  }

  /**
   * Echo out special head markup.
   */
  public function wp_head() {
    if ( ! $this->is_correct_page() ) {
      return;
    }

    // No cache
    if ( ! empty( $this->page_data->head ) ) {
      if ( array_key_exists( 'no_cache', $this->page_data->head ) ) {
        if ( $this->page_data->head['no_cache'] ) {
          echo '<meta http-equiv="Pragma" content="no-cache"/>';
          echo '<meta http-equiv="Expires" content="-1"/>';
          echo '<meta http-equiv="Cache-Control" content="no-store,no-cache" />';
        }
      }
    }
  }

  /**
   * Displays the appropriates posts (0, 1, or many).
   * The content for this is populated by the head!
   *
   * @param String $content The HTML passed by Wordpress
   * @return String $content
   */
  public function the_content( $content ) {
    if ( $this->is_correct_page() &&
         isset( $this->page_data->content ) ) {
      return $this->page_data->content;
    }

    return $content;
  }

  /**
   * Passes through for now.  We may need to
   * override this in the future
   */
  public function query_vars( $vars ) {
    return $vars;
  }

  /**
   * Not a callback
   *
   * Used to determine if we are on the correct url that the posts
   * page overrides.
   */
  public function is_correct_page() {
    return strcmp( get_query_var( 'pagename' ), Posts_Pages::$page_name ) === 0;
  }
}