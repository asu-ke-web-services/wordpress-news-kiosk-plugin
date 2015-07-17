<?php

namespace Kiosk_WP;

/**
 * Posts Administraion Class
 *
 * Creates and handles post_item and views for the admin panel
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
  public static $section_post_status         = 'kiosk_post_status';
  public static $kiosk_post_status           = 'any';
  public static $section_post_details        = 'Post Details';
  public static $kiosk_post_statuses         = array();

  protected $plugin_slug;
  protected $version;

  public function __construct( &$general_admin ) {
    $this->plugin_slug = 'kiosk-post-admin';
    $this->version     = '0.1';
    $this->css         = plugin_dir_url( __FILE__ ) . 'css/posts-admin-manager.css';
    self::$kiosk_post_statuses = get_post_statuses();
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
    wp_enqueue_style( $this->plugin_slug, $this->css, array(), $this->version, false );
    wp_enqueue_style( 'timeline-css',plugin_dir_url( __FILE__ ) . 'css/timeline.css', array(), $this->version, false );
  }

  public function admin_init() {
    // register settings for form
    register_setting(
        self::$options_group,
        self::$options_name,
        array( $this, 'form_submit' )
    );
    // register settings for post tags field
    register_setting(
        self::$options_group,
        self::$section_post_tags,
        array( $this, 'sanitize_post_tags_callback' )
    );
    // register settings for post status field
    register_setting(
        self::$options_group,
        self::$section_post_status,
        array( $this, 'sanitize_post_tags_callback' )
    );
    add_settings_section(
        self::$section_id,
        'Posts Details',
        array(
          $this,
          'print_section_info',
        ),
        self::$section_name
    );
    add_settings_field(
        self::$section_post_tags,
        'Post Tags',
        array(
          $this,
          'section_post_tags_callback',
        ), // Callback
        self::$section_name,
        self::$section_id,
        array( 'context' => self::$section_post_tags ) // custom arguments
    );
    add_settings_field(
        self::$section_post_status,
        'Post Status',
        array(
          $this,
          'section_post_tags_callback',
        ), // Callback
        self::$section_name,
        self::$section_id,
        array( 'context' => self::$section_post_status ) // custom arguments
    );
    add_settings_field(
        self::$section_post_details,
        '',
        array(
          $this,
          'post_categories_callback',
        ), // Callback
        self::$section_name,
        self::$section_id
    );
  }


  /**
   * Print the Section text
   */
  public function print_section_info() {
    // print 'Enter your settings below:';
  }
  /**
   * Print the form section for the post tags and post status
   */
  public function section_post_tags_callback( $args ) {
    if ( self::$section_post_tags === $args['context'] ) {

      $tags = get_option( self::$section_post_tags );
      printf(
          '<input type="text" id="%s" name="%s" value="%s"></input>',
          self::$section_post_tags,
          self::$section_post_tags,
          $tags
      );
      self::$kiosk_post_tags = $this->sanitize_post_tags_callback( $tags );

    } else if ( self::$section_post_status === $args['context'] ) {

      $status = get_option( self::$section_post_status );
      $post_status_drop_down = '<select id="%s" name="%s">';
      $selected = '';
      foreach ( self::$kiosk_post_statuses as $key => $value ) {
        if ( $key == $status ) {
          $selected = 'selected="selected"';
        } else {
          $selected = '';
        }
        $post_status_drop_down  .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
      }
      $post_status_drop_down .= '</select>';
      printf(
          $post_status_drop_down,
          self::$section_post_status,
          self::$section_post_status
      );
      self::$kiosk_post_status = $this->sanitize_post_tags_callback( $status );
    }
  }


  /**
   * Print the form section for the post categories
   */
  public function post_categories_callback( ) {
    $tags   = self::$kiosk_post_tags;
    $status = self::$kiosk_post_status;
    $limit                  = 20;
    if ( empty( $status ) ) {
      $status = 'any';
    }
    $query_options          = array(
        'post_type'         => array( 'attachment', 'page', 'post' ),
        'posts_per_page'    => $limit,
        'orderby'           => 'post_date',
        'order'             => 'DESC',
        'tag'               => $tags,
        'post_status'       => $status,
    );
    $posts          = Kiosk_Helper::get_posts_from_db( $query_options );
    $posts_list     = array();
    foreach ( $posts as $post ) {
      if ( Kiosk_Helper::has_post_expired( $post->ID ) ) {
        continue;
      }
      $post_item                   = array();
      $post_item['image']          = Kiosk_Helper::get_image(
          $post->ID,
          $post->post_content
      );
      $post_item['post-title']     = trim( $post->post_title );
      $post_item['kiosk-end-date'] = trim( get_post_meta(
          $post->ID,
          'kiosk-end-date',
          true
      ) );
      $post_item['post-status']    = trim( $post->post_status );
      $post_item['post-id']        = trim( $post->ID );
      $post_item['post-permalink'] = trim( get_permalink( $post->ID ) );
      $post_item['post-date']      = trim( $post->post_date );
      $posts_list[] = $post_item;
    }
    usort( $posts_list, array( 'Kiosk_WP\Kiosk_Helper', 'sort_by_date' ) );
    echo $this->posts_items_display( $posts_list );
  }
  /**
   *
   */
  public function posts_items_display( $posts_list ){
    $table_template = <<<HTML
      <div class="posts-admin-flex posts-admin">
        <div class="posts-admin-flex posts-admin__row posts-admin__head-text">
          <div class="posts-admin__image">Image</div>
          <div class="posts-admin__id">Post ID</div>
          <div class="posts-admin__title">Post Title</div>
          <div class="posts-admin__date">Kiosk End Date</div>
          <div class="posts-admin__status">Post Status</div>
          <div class="posts-admin__date">Post Date</div>
        </div>
        %s
      </div>
HTML;
    $row_template = <<<HTML
    <div class="posts-admin-flex posts-admin__row">
      <div class="posts-admin__image"><a href="%s" target="_blank"><img src="%s" class='posts-table__image' alt='No Image'/></a></div>
      <div class="posts-admin__id"><a href="%s" target="_blank">%s</a></div>
      <div class="posts-admin__title">%s</div>
      <div class="posts-admin__date">%s</div>
      <div class="posts-admin__status">%s</div>
      <div class="posts-admin__date">%s</div>
    </div>
HTML;
    $row_items = '';
    $timeline = null;
    foreach ( $posts_list as $item ) {
      $row_items .= sprintf(
          $row_template,
          $item['image'],
          $item['image'],
          $item['post-permalink'],
          $item['post-id'],
          $item['post-title'],
          $item['kiosk-end-date'],
          $item['post-status'],
          $item['post-date']
      );
      //prepare post_item for timeline
      $timeline[]    = array(
        'start-date' => $item['post-date'],
        'end-date'   => $item['kiosk-end-date'],
        'label'      => $item['post-title'],
        'status'     => $item['post-status'],
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
