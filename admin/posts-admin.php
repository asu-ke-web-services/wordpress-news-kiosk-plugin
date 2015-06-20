<?php

namespace Kiosk_WP;

/**
 * Posts Administraion Class
 *
 * Creates and handles data and views for the admin panel
 * in Wordpress
 */
class Posts_Admin extends Base_Registrar {
  public static $options_name                = 'posts_options';
  public static $options_group               = 'posts_options_group';
  public static $options_categories_name     = 'posts_categories_slugs';
  public static $section_name                = 'kiosk_posts_display_admin';
  public static $section_id                  = 'kiosk_posts_display_admin_id';
  public static $section_post_tags           = 'kiosk_post_tags';
  public static $kiosk_post_tags             = '';

  protected $plugin_slug;
  protected $version;

  public function __construct( &$general_admin ) {
    $this->plugin_slug = 'kiosk-post-admin';
    $this->version     = '0.1';
    $this->css         = plugin_dir_url( __FILE__ ) . 'css/posts-admin-manager.css';
    $this->load_dependencies();
    $this->define_hooks();
    $general_admin->enqueue_panel(
        plugin_dir_path( __FILE__ ) . 'views/posts-admin-manager.php'
    );
  }

  /**
   * Load the kiosk api
   *
   * @override
   */
  public function load_dependencies() {
    // No dependencies for now
  }

  /**
   * Add filters and action
   *
   * The majority of the work happend in the action 'wp'
   */
  public function define_hooks() {
    $this->add_action( 'admin_enqueue_scripts', $this, 'admin_enqueue_scripts' );
    $this->add_action( 'admin_init', $this, 'admin_init' );
  }

  /**
   * Enqueue styles so that Wordpress caches them.
   */
  public function admin_enqueue_scripts() {
    $plugin_dir_url = plugin_dir_url( dirname( __FILE__ ) );
    wp_enqueue_style( $this->plugin_slug, $this->css, array(), $this->version, false );
    wp_register_script( 'jquery', $plugin_dir_url . '/assets/js/jquery-1.11.2.min.js', array(), '1.11.2', true );
    wp_register_script( 'bootstrap-js', $plugin_dir_url . '/assets/bootstrap-3.1.1-dist/js/bootstrap.min.js', array( 'jquery' ), '3.1.1', true );
    wp_register_style( 'bootstrap-css', $plugin_dir_url . '/assets/bootstrap-3.1.1-dist/css/bootstrap.min.css', array(), '3.1.1', 'all' );
    wp_enqueue_script( 'jquery', $plugin_dir_url . '/assets/js/jquery-1.11.2.min.js', array(), '1.11.2', true );
    wp_enqueue_script( 'bootstrap-js', $plugin_dir_url . '/assets/bootstrap-3.1.1-dist/js/bootstrap.min.js', array( 'jquery' ), '3.1.1', true );
    wp_enqueue_style( 'bootstrap-css', $plugin_dir_url . '/assets/bootstrap-3.1.1-dist/css/bootstrap.min.css', array(), '3.1.1', 'all' );
  }

  public function admin_init() {
    // register settings
    register_setting(
        Posts_Admin::$options_group,
        Posts_Admin::$options_name,
        array( $this, 'form_submit' )
    );
    // register settings
    register_setting(
        Posts_Admin::$options_group,
        Posts_Admin::$section_post_tags,
        array( $this, 'sanitize_post_tags_callback' )
    );

    add_settings_section(
        Posts_Admin::$section_id,
        'Posts Details',
        array(
          $this,
          'print_section_info',
        ),
        Posts_Admin::$section_name
    );
    add_settings_field(
        Posts_Admin::$section_post_tags,
        'Post Tags',
        array(
          $this,
          'section_post_tags_callback',
        ), // Callback
        Posts_Admin::$section_name,
        Posts_Admin::$section_id
    );

  }


   /**
   * Print the Section text
   */
  public function print_section_info() {
    // print 'Enter your settings below:';
  }
  /**
   * Print the form section for the post tags
   */
  public function section_post_tags_callback() {
    $value = get_option( Posts_Admin::$section_post_tags );

    printf(
        '<input type="text" id="%s" name="%s" value="%s"></input>',
        Posts_Admin::$section_post_tags,
        Posts_Admin::$section_post_tags,
        $value
    );
    $kiosk_post_tags = $this->sanitize_post_tags_callback( $value );
    echo $this->post_categories_callback( $kiosk_post_tags );
  }


  /**
   * Print the form section for the post categories
   */
  public function post_categories_callback( $tags ) {
    $limit                  = 20;
    $query_post_options     = array(
        'post_type'         => array( 'attachment', 'page', 'post' ),
        'posts_per_page'    => $limit,
        'orderby'           => 'post_date',
        'order'             => 'DESC',
        'tag'               => $tags,
        'post_status'       => 'any',
    );
    $list_items             = Kiosk_Helper::get_posts_items_from_db( $query_post_options );
    usort( $list_items, array( 'Kiosk_WP\Kiosk_Helper', 'sort_by_date' ) );
    return $this->posts_table_display( $list_items );
  }
  public function posts_table_display( $list_items ){
    $table_template = <<<HTML
      <div class="table-responsive">          
        <table class="table table-striped table-condensed">
          <thead>
            <tr>
              <th>Post ID</th>
              <th>Post Title</th>
              <th>Image</th>
              <th>Kiosk End Date</th>
              <th>Post Status</th>
              <th>Post Date</th>
            </tr>
          </thead>
          <tbody>
            %s
          </tbody>
        </table>
        </div>
      </div>
HTML;
    $row_template = <<<HTML
    <tr>
    <td>%s</td>
    <td>%s</td>
    <td><a href="%s">%s</td>
    <td>%s</td>
    <td>%s</td>
    <td>%s</td>
    </tr>
HTML;
    $row_items = '';
    $timeline = null;
    foreach ( $list_items as $item ) {
      $row_items .= sprintf(
          $row_template,
          $item['post-id'],
          $item['post-title'],
          $item['image'],
          $item['image'],
          $item['kiosk-end-date'],
          $item['post-status'],
          $item['post-date']
      );
      //prepare data for timeline
      $timeline[]    = array(
        'start-date' => $item['post-date'],
        'end-date'   => $item['kiosk-end-date'],
        'label'      => $item['post-title'],
        );
    }
    if ( ! empty( $timeline ) ) {
      print_r( TimeLine_Helper::create_timeline( $timeline ) );
    }
    return sprintf( $table_template, $row_items );
  }

  /**
   * Handle form submissions
   */
  public function form_submit( $input ) {
    // TODO filter and make sure that post categories are valid
    return $input;
  }
  /**
   * Handle form submissions for post tags variable by removing
   *  whitespace and escape any html junk.
   */
  public function sanitize_post_tags_callback( $input ) {
    $input = rtrim( trim( $input ) );

    return esc_html( $input );
  }
}
