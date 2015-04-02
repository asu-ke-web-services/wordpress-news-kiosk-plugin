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
  public static $section_post_categories_id  = 'kiosk_post_categories';

  protected $plugin_slug;
  protected $version;

  public function __construct( &$general_admin ) {
    $this->plugin_slug = 'kiosk-post-admin';
    $this->version     = '0.1';
    $this->css         = plugin_dir_url( __FILE__ ) . 'css/posts-admin-manager.css';

    // Set default options:
    add_option(
        Posts_Admin::$options_name,
        array( Posts_Admin::$section_post_categories_id => 'posts-wrigley-lecture, posts-sustainability-series, posts-case-critical, posts-sustainability-after-school, posts-conferences, posts-defenses' )
    );

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
    wp_enqueue_script( 'jquery-ui', plugin_dir_url( __FILE__ ) . 'js/jquery-ui.min.js', array(), $this->version, false );
    wp_enqueue_style( $this->plugin_slug, $this->css, array(), $this->version, false );
  }

  public function admin_init() {
    // register settings
    register_setting(
        Posts_Admin::$options_group,
        Posts_Admin::$options_name,
        array( $this, 'form_submit' )
    );

    add_settings_section(
        Posts_Admin::$section_id,
        'Post Settings',
        array(
          $this,
          'print_section_info',
        ),
        Posts_Admin::$section_name
    );

    add_settings_field(
        Posts_Admin::$section_post_categories_id,
        'Post Categories', // Title
        array(
          $this,
          'post_categories_callback',
        ), // Callback
        Posts_Admin::$section_name,
        Posts_Admin::$section_id
    );
  }

  /**
   * Print the Section text
   */
  public function print_section_info() {
    print 'Enter your settings below:';
  }

  /**
   * Print the form section for the post categories
   */
  public function post_categories_callback() {
    $default = '';
    $options = get_option( \Kiosk_WP\Posts_Admin::$options_name );

    if ( isset ( $options[ Posts_Admin::$section_post_categories_id ] ) ) {
      $default = esc_attr( $options[ Posts_Admin::$section_post_categories_id ] );
    }

    /*$presenter  = new \Posts_Presenter();
    $categories = $presenter->get_full_listing_of_categories();
    $filtered   = '[';

    for ( $i = 0; $i < count( $categories ); $i++ ) {
      if ( 0 != $i ) {
        $filtered .= ',';
      }

      $filtered .= '{';
      $filtered .= 'slug : "' . $categories[ $i ]['slug'] . '",';
      $filtered .= 'title : "' . $categories[ $i ]['title'] . '"';
      $filtered .= '}';
    }

    $filtered .= ']';

    */
      $filtered   = '[';
      $filtered .= '{';
      $filtered .= 'slug :  1,';
      $filtered .= 'title : 1';
      $filtered .= '}';
      $filtered .= ',';
      $filtered .= '{';
      $filtered .= 'slug :  1,';
      $filtered .= 'title : 2';
      $filtered .= '}';
      $filtered .= ',';
      $filtered .= '{';
      $filtered .= 'slug :  3,';
      $filtered .= 'title : 4';
      $filtered .= '}';
      $filtered .= ',';
      $filtered .= '{';
      $filtered .= 'slug :  5,';
      $filtered .= 'title : 6';
      $filtered .= '}';
      $filtered .= ',';
      $filtered .= '{';
      $filtered .= 'slug :  7,';
      $filtered .= 'title : 8';
      $filtered .= '}';
      $filtered .= ',';
      $filtered .= '{';
      $filtered .= 'slug :  9,';
      $filtered .= 'title : 10';
      $filtered .= '}';
      $filtered .= ',';
      $filtered .= '{';
      $filtered .= 'slug :  11,';
      $filtered .= 'title : 12';
      $filtered .= '}';
       $filtered .= ']';

    $javascript = <<<JAVASCRIPT
<script>
+function ($) {
    'use strict';
    
    var allOptions = %s;
    
    var selectedOptions = '%s'.split(',');
    selectedOptions     = $.map( selectedOptions, function ( v ) {
      return v.trim();
    } );
    
    // Populate sortables
    
    var allSelected = $.map( selectedOptions, function ( v ) {
        for ( var i = 0; i < allOptions.length; i++ ) {
            if ( allOptions[i].slug === v ) {
                var value = allOptions[i].slug;
                var read = allOptions[i].title;
                allOptions[i].touched = true;
                
                return $( '<li class="ui-state-default" data-value="' +  value + '">' + read + '</li>' );
            }
        }
        return null;
    } );
    
    var allNotSelected = $.map( allOptions, function ( v ) {
        if ( ! v.touched ) {
            return $( '<li class="ui-state-default" data-value="' +  v.slug + '">' + v.title + '</li>' );
        }
    } );
 
    $( '#posts-sortable-selected' ).append( allSelected );
    $( '#posts-sortable-all' ).append( allNotSelected );
    
    // Enable sorting
    $( "#posts-sortable-selected, #posts-sortable-all" ).sortable({
        connectWith: "ul",
        placeholder: "ui-state-highlight",
        stop : function ( e, ui ) {
            var filtered = $.map( $( '#posts-sortable-selected li' ), function ( v ) {
                return $( v ).attr('data-value')
            } ).join(',')
            
            $( '#%s' ).val( filtered )
        }
    });
    
    $( "#posts-sortable-all, #posts-sortable-selected" ).disableSelection();
}(jQuery);
</script>
JAVASCRIPT;

    $html = <<<HTML
<div class="sortable-panel">
  <h4>Available Post Categories</h4>
  <ul id="posts-sortable-all" class="droptrue sortable"></ul>
</div>
<div class="sortable-panel">
  <h4>Selected Post Categories</h4>
  <ul id="posts-sortable-selected" class="droptrue sortable"></ul>
</div>
<br style="clear:both">
<input type="hidden" id="%s" name="%s[%s]" value="%s"/>
HTML;
    // HTML before Javascript!
    printf(
        $html,
        Posts_Admin::$section_post_categories_id,
        Posts_Admin::$options_name,
        Posts_Admin::$section_post_categories_id,
        $default
    );

    printf(
        $javascript,
        $filtered,
        $default,
        Posts_Admin::$section_post_categories_id
    );
  }

  /**
   * Handle form submissions
   */
  public function form_submit( $input ) {
    // TODO filter and make sure that post categories are valid
    return $input;
  }
}
