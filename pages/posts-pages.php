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
  const FRONT_PAGE                          = 'front_page';
  const EVENTS_PAGE                         = 'posts_page';
  const RSVP_PAGE                           = 'rsvp_page';
  const RSVP_SUBMIT_PAGE                    = 'rsvp_submit_page';
  const THANK_YOU_PAGE                      = 'thank_you_page';
  const NOT_FOUND                           = 'Not found.';

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
    $this->add_action( 'init', $this, 'setup_rewrites' );
    $this->add_action( 'wp_head', $this, 'wp_head' );
    $this->add_action( 'the_content', $this, 'the_content' );

    // Register Filters
    $this->add_filter( 'query_vars', $this, 'query_vars' );
    $this->add_filter( 'web_standards_hero_image', $this, 'web_standards_hero_image' );
    $this->add_filter( 'wpseo_canonical', $this, 'wpseo_canonical' );
    $this->add_filter( 'wpseo_title', $this, 'wpseo_title' );
    $this->add_filter( 'wpseo_metadesc', $this, 'wpseo_meta_description' );
    $this->add_filter( 'wpseo_opengraph_image', $this, 'opengraph_image' );
    $this->add_filter( 'wpseo_opengraph_type', $this, 'opengraph_type' );
    $this->add_filter( 'wpseo_breadcrumb_links', $this, 'wpseo_breadcrumb_links' );
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
   * Set up url rewrites. Maps pretty urls to page actions.
   */
  public function setup_rewrites() {
    /*
     * Wordpress requires that you tell it that you are using
     * additional parameters.
     *
     * Allow everything to come in through the rsvp_submit url
     * and transform the post so that url parameters do not
     * conflict with Wordpress's reserved parameters.
     */
    add_rewrite_tag( '%page_type%' , '([^&]+)' );
    add_rewrite_tag( '%' . Posts_Pages::$param_posts_category_slug . '%' , '([^&]+)' );
    add_rewrite_tag( '%' . Posts_Pages::$param_post_year . '%' , '([^&]+)' );
    add_rewrite_tag( '%' . Posts_Pages::$param_post_slug . '%' , '([^&]+)' );
    add_rewrite_tag( '%first_name%' , '([^&]+)' );
    add_rewrite_tag( '%last_name%' , '([^&]+)' );
    add_rewrite_tag( '%email%' , '([^&]+)' );
    add_rewrite_tag( '%extra_post_vars%' , '([^&]+)' );
    // Error is reserved, so we need to use e.
    add_rewrite_tag( '%e%' , '([^&]+)' );

    $this->transform_post();

    /*
     * Add the rewrite rules
     */
    // ======================================================
    // Rule: /{page}/rsvp/{post_slug}/thank-you => thank_you
    // ======================================================
    $from = Posts_Pages::$page_name . '/rsvp/([^/]*)/thank-you/?';
    $to   = 'index.php?pagename=' . Posts_Pages::$page_name . '&page_type=';
    $to  .= Posts_Pages::THANK_YOU_PAGE . '&' . Posts_Pages::$param_post_slug . '=$matches[1]';
    add_rewrite_rule( $from, $to, 'top' );

    // =======================================
    // Rule: /{page}/rsvp/{post_slug} => rsvp
    // =======================================
    // TODO need to pass through first_name, last_name, email, and error for
    // the rsvp page
    // Note: this TODO should not need any work to be resolved, but we
    // need to check that it works
    $from = Posts_Pages::$page_name . '/rsvp/([^/]*)/?';
    $to   = 'index.php?pagename=' . Posts_Pages::$page_name . '&page_type=';
    $to  .= Posts_Pages::RSVP_PAGE . '&' . Posts_Pages::$param_post_slug . '=$matches[1]';
    add_rewrite_rule( $from, $to, 'top' );

    // ================================================
    // Special Rule that redirects to a standalone page
    // Rule: /{page}/email/{post_slug} => email
    // ================================================
    $from = Posts_Pages::$page_name . '/email/([^/]*)/?$';
    $to   = 'wp-content/plugins/' . plugin_basename( dirname( __FILE__ ) );
    $to  .= '/views/email-posts-presenter.php' . '?' . Posts_Pages::$param_post_slug . '=$1';
    add_rewrite_rule( $from, $to, 'top' );

    // ======================
    // Rule: /{page} => front
    // ======================
    $from = Posts_Pages::$page_name . '/?$';
    $to   = 'index.php?pagename=' . Posts_Pages::$page_name . '&page_type=';
    $to  .= Posts_Pages::FRONT_PAGE;
    add_rewrite_rule( $from, $to, 'top' );

    // ============================================
    // Rule: /{page}/rsvp_submit => rsvp_submit
    // ============================================
    $from = Posts_Pages::$page_name . '/rsvp-submit/?';
    $to   = 'index.php?pagename=' . Posts_Pages::$page_name . '&page_type=';
    $to  .= Posts_Pages::RSVP_SUBMIT_PAGE;
    add_rewrite_rule( $from, $to, 'top' );

    // =========================================
    // Rule: /{page}/{category_slug} => posts
    // =========================================
    $from = Posts_Pages::$page_name . '/([^/]+)/?$';
    $to   = 'index.php?pagename=' . Posts_Pages::$page_name . '&page_type=';
    $to  .= Posts_Pages::EVENTS_PAGE . '&' . Posts_Pages::$param_posts_category_slug . '=$matches[1]';
    add_rewrite_rule( $from, $to, 'top' );

    // ==============================================
    // Rule: /{page}/{category_slug}/{year} => posts
    // ==============================================
    $from = Posts_Pages::$page_name . '/([^/]*)/([^/]*)/?';
    $to   = 'index.php?pagename=' . Posts_Pages::$page_name . '&page_type=';
    $to  .= Posts_Pages::EVENTS_PAGE . '&' . Posts_Pages::$param_posts_category_slug . '=$matches[1]&';
    $to  .= Posts_Pages::$param_post_year . '=$matches[2]';
    add_rewrite_rule( $from, $to, 'top' );

    // Flush them!
    // TODO move this to register_activation_hook when rules are set in stone
    flush_rewrite_rules();
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
   * Override the value for the hero image if provided by
   * the page_data
   *
   * @param String $hero_image_path
   * @return String
   */
  public function web_standards_hero_image( $hero_image_path ) {
    if ( $this->is_correct_page() ) {
      if ( ! empty( $this->page_data->extra ) ) {
        if ( array_key_exists( 'web_standards_hero_image' , $this->page_data->extra ) ) {
          return $this->page_data->extra['web_standards_hero_image'];
        }
      }
    }

    return $hero_image_path;
  }

  /**
   * Allows changing of the canonical URL.
   * Returning false will disable the canonical.
   *
   * @param String $canonical
   * @return String
   */
  public function wpseo_canonical( $canonical ) {
    if ( $this->is_correct_page() ) {
      if ( ! empty( $this->page_data->head ) ) {
        if ( array_key_exists( 'canonical', $this->page_data->head ) ) {
          return $this->page_data->head['canonical'];
        }
      }
    }
    return $canonical;
  }

  /**
   * This filter works a bit differently. It will <b>prepend</b>
   * the value given by the page data to the title.
   *
   * @param String $title
   * @return String
   */
  public function wpseo_title( $title ) {
    if ( $this->is_correct_page() ) {
      if ( ! empty( $this->page_data->head ) ) {
        if ( array_key_exists( 'title', $this->page_data->head ) ) {
          // TODO do we want to use the pipe to separate title parts?
          return $this->page_data->head['title'] . ' | ' . $title;
        }
      }
    }
    return $title;
  }

  /**
   * Overrides the description if the page data has one
   *
   * @param String $description
   * @return String
   */
  public function wpseo_meta_description( $description ) {
    if ( $this->is_correct_page() ) {
      if ( ! empty( $this->page_data->head ) ) {
        if ( array_key_exists( 'description', $this->page_data->head ) ) {
          return $this->page_data->head['description'];
        }
      }
    }
    return $description;
  }

  /**
   * Override the open graph image if it is set in the page_data
   *
   * @param String $open_graph_image
   * @return String
   */
  public function opengraph_image( $open_graph_image ) {
    if ( $this->is_correct_page() ) {
      if ( ! empty( $this->page_data->head ) ) {
        if ( array_key_exists( 'open_graph_image', $this->page_data->head ) ) {
          return $this->page_data->head['open_graph_image'];
        }
      }
    }
    return $open_graph_image;
  }

  /**
   * Overrides the description if the page data has one
   *
   * @param String $description
   * @return String
   */
  public function opengraph_type( $description ) {
    if ( $this->is_correct_page() ) {
      if ( ! empty( $this->page_data->head ) ) {
        if ( array_key_exists( 'og:type', $this->page_data->head ) ) {
          return $this->page_data->head['og:type'];
        }
      }
    }
    return $description;
  }

  /**
   * Breadcrumb ancestors.
   *
   * @param $ancestors array
   */
  public function wpseo_breadcrumb_links( $ancestors ) {
    $page = get_query_var( 'page_type' );

    switch ( $page ) {
      case Posts_Pages::THANK_YOU_PAGE:
        $post_slug = get_query_var( Posts_Pages::$param_post_slug );

        $presenter = new \Posts_Presenter();
        $post_name = $presenter->get_post_title(
            array( 'slug' => $post_slug )
        );

        // filter out html from title
        $post_name = strip_tags( $post_name );

        $ancestors[] = array(
          'text' => $post_name,
          'url' => Base_Path_Helper::base_path( array( 'posts', 'rsvp', $post_slug ) ),
          'allow_html' => 1,
        );

        $ancestors[] = array( 'text' => 'Thank You' );

        break;
      case Posts_Pages::RSVP_PAGE:
        $post_slug = get_query_var( Posts_Pages::$param_post_slug );

        $presenter = new \Posts_Presenter();
        $post_name = $presenter->get_post_title(
            array( 'slug' => $post_slug )
        );

        // filter out html from title
        $post_name = strip_tags( $post_name );

        $ancestors[] = array(
          'text' => $post_name,
          'url' => Base_Path_Helper::base_path( array( 'posts', 'rsvp', $post_slug ) ),
          'allow_html' => 1,
        );

        break;
      case Posts_Pages::EVENTS_PAGE:
        // TODO  support comma delimited category slugs
        $category_slug = get_query_var( Posts_Pages::$param_posts_category_slug );
        $category_slugs  = explode( ',', $category_slug );
        $year          = get_query_var( Posts_Pages::$param_post_year );

        $presenter     = new \Groups_Presenter();
        $category_name = array();

        foreach ( $category_slugs as $_ => $value ) {
          $category_name[] = $presenter->get_group_title(
              array( 'slug' => $value )
          );
        }

        $category_name = implode( ', ', $category_name );

        $ancestors[] = array(
          'text' => $category_name,
          'url' => Base_Path_Helper::base_path( array( 'posts', $category_slug ) ),
          'allow_html' => 1,
        );

        if ( $year ) {
          $ancestors[] = array(
            'text' => $year,
            'url' => Base_Path_Helper::base_path( array( 'posts', $category_slug, $year ) ),
            'allow_html' => 1,
          );
        }

        break;
      case Posts_Pages::FRONT_PAGE:
        // fall through
      default:
        // do not touch $ancestors
    }

    return $ancestors;
  }

  /**
   * Not a callback
   *
   * Used to determine the page type and what page data to use
   */
  protected function get_posts_data() {
    $page = get_query_var( 'page_type' );

    switch ( $page ) {
      case Posts_Pages::FRONT_PAGE:
        $result = $this->setup_posts();
        break;
      case Posts_Pages::EVENTS_PAGE:
        $category_slug = get_query_var( Posts_Pages::$param_posts_category_slug );
        $category_slugs = explode( ',', $category_slug );
        $result = $this->setup_posts( $category_slugs );
        break;
      case Posts_Pages::RSVP_PAGE:
        $result = $this->setup_rsvp();
        break;
      case Posts_Pages::RSVP_SUBMIT_PAGE:
        $result = $this->setup_rsvp_submit();
        break;
      case Posts_Pages::THANK_YOU_PAGE:
        $result = $this->setup_thank_you();
        break;
      default:
        // we should tell Wordpress to return a 404.
        return (object) array(
          'http_header' => array( 'HTTP/1.0 404 Not Found - Archive Empty' ),
          'content' => Posts_Pages::NOT_FOUND,
        );
    }

    return $result;
  }

  /**
   * Not a callback
   */
  protected function setup_posts( $category_slug = 'all' ) {
    if ( 'all' === $category_slug ||
         ( is_array( $category_slug ) && 'all' === $category_slug[0] ) ) {
      $options        = get_option( Posts_Admin::$options_name );
      $category_slugs = $options[ Posts_Admin::$section_post_categories_id ];
      $category_slugs = array_map( 'trim', explode( ',', $category_slugs ) );

      // TODO filter like in the shortcodes
    } else if ( ! is_array( $category_slug ) ) {
      $category_slugs = array( $category_slug );
    } else {
      $category_slugs = $category_slug;
    }

    $year = false;
    $year_param = get_query_var( Posts_Pages::$param_post_year );
    // TODO base path
    $base_path = Base_Path_Helper::base_path( array( 'posts' ) );

    if ( isset( $year_param ) && ! empty( $year_param ) ) {
      $year = $year_param;
    }

    $presenter = new \Posts_Presenter();
    $view      = $presenter->get_blurb_list( $category_slugs, $year, $base_path );

    return $view;
  }

  /**
   * Not a callback
   */
  protected function setup_rsvp() {
    $post_slug = get_query_var( Posts_Pages::$param_post_slug );
    $first_name = '';
    $last_name  = '';
    $email      = '';
    $name       = '';
    $error      = get_query_var( 'e' );
    $base_path  = Base_Path_Helper::base_path( ['posts'] );

    // =================
    // Decode First Name
    // =================
    $temp_first_name = get_query_var( 'first_name' );
    if ( $temp_first_name !== '' ) {
      // Decode twice
      $first_name = htmlentities( urldecode( urldecode( $temp_first_name ) ) );

      // Ignore mailchimp test data
      if ( $first_name === '&lt;&lt; Test First Name &gt;&gt;' ||
           $first_name === '*|FNAME|*' ) {
        $first_name = '';
      }
    }

    // ================
    // Decode Last Name
    // ================
    $temp_last_name = get_query_var( 'last_name' );
    if ( $temp_last_name !== '' ) {
      // Decode twice
      $last_name = htmlentities( urldecode( urldecode( $temp_last_name ) ) );

      // Ignore mailchimp test data
      if ( $last_name === '&lt;&lt; Test Last Name &gt;&gt;' ||
           $last_name === '*|LNAME|*' ) {
        $last_name = '';
      }
    }

    // ===========================================
    // Shove the first name and last name together
    // ===========================================
    if ( ! empty ( $first_name ) && ! empty( $last_name ) ) {
      $name = $first_name . ' ' . $last_name;
    } else {
      $name = $first_name . $last_name;
    }

    // ============
    // Decode Email
    // ============
    $temp_email = get_query_var( 'email' );
    if ( $temp_email !== '' ) {
      // Decode twice
      $email = htmlentities( urldecode( urldecode( $temp_email ) ) );

      // Ignore mailchimp test data
      if ( $email === '&lt;&lt; Test Email Address &gt;&gt;' ||
           $email === '*|EMAIL|*' ) {
        $email = '';
      }
    }

    // ==========
    // Check Slug
    // ==========
    if ( ! isset( $post_slug ) || empty( $post_slug ) ) {
      // Redirect to the main post page
      return array(
        'http_header' => array( 'Location: ' . get_site_url() . '/' . Posts_Pages::$page_name . '/' )
      );
    }

    // Make name and error false if they are empty
    if ( empty( $error )  ) {
      $error = false;
    }

    if ( empty( $name  ) ) {
      $name = false;
    }

    $presenter = new \Posts_Presenter();
    $view      = $presenter->get_rsvp(
        $post_slug,
        $name,
        $first_name,
        $last_name,
        $email,
        $error,
        $base_path
    );

    return $view;
  }

  /**
   * Not a callback
   */
  protected function setup_rsvp_submit() {
    $post_values     = $this->untransform_post();
    $post_id        = isset( $post_values['post_id'] ) ? $post_values['post_id'] : null;
    $name            = isset( $post_values['name'] ) ? $post_values['name'] : null;
    $email           = isset( $post_values['email'] ) ? $post_values['email'] : null;
    $organization    = isset( $post_values['organization'] ) ? $post_values['organization'] : null;
    $reg_form_fields = isset( $post_values['reg_form_fields'] ) ? $post_values['reg_form_fields'] : null;
    $honeypot        = isset( $post_values['honeypot'] ) ? $post_values['honeypot'] : null;
    $extra_post_vars = isset( $post_values['extra_post_vars'] ) ? $post_values['extra_post_vars'] : null;
    $source          = \Kiosk_Api\Web_Source::$school_of_sustainability;
    $ip_address      = Client_Helper::client_ip();
    $base_path       = Base_Path_Helper::base_path( ['posts'] );
    // TODO pull $source from the website settings or something

    $presenter = new \Posts_Presenter();
    $view      = $presenter->post_rsvp(
        $post_id,
        $name,
        $email,
        $organization,
        $reg_form_fields,
        $honeypot,
        $extra_post_vars,
        $source,
        $ip_address,
        $base_path
    );

    return $view;
  }

  protected function setup_thank_you() {
    $post_slug = get_query_var( Posts_Pages::$param_post_slug );
    $link_back  = Base_Path_Helper::base_path( [ 'posts', 'rsvp', $post_slug ] );
    $presenter  = new \Posts_Presenter();
    $view       = $presenter->get_thank_you(
        $post_slug,
        $link_back
    );

    return $view;
  }

  /**
   * Take the parameters in the POST and transform
   * them to remove the prepended namespace
   */
  protected function untransform_post() {
    $post_values = array();

    foreach ( $_POST as $key => $value ) {
      $nKey                 = str_replace( 'kiosk-', '', $key );
      $post_values[ $nKey ] = $value;
    }
    return $post_values;
  }

  protected function transform_post() {
    $needle = Posts_Pages::$page_name . '/rsvp-submit';

    if ( substr( $_SERVER['REQUEST_URI'], - strlen( $needle ) ) === $needle ) {
      foreach ( $_POST as $key => $value ) {
        $_POST[ $key ]              = '';
        $_REQUEST[ $key ]           = '';
        $_REQUEST[ 'kiosk-' . $key ] = $value;
        $_POST[ 'kiosk-' . $key ]    = $value;
        add_rewrite_tag( "%kiosk-$key%", '([^&]+)' );
      }
    }
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