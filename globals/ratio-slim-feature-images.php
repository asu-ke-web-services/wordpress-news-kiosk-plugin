<?php

namespace Kiosk_WP;

// Avoid direct calls to this file
if ( ! defined( 'KIOSK_WP_VERSION' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

class Ratio_Slim_Feature_Images extends Base_Registrar {
  protected $plugin_slug;
  protected $version;

  public function __construct() {
    $this->plugin_slug = 'kiosk-templates';
    $this->version     = '0.1';

    $this->load_dependencies();
    $this->define_hooks();
  }

  /**
   * @override
   */
  public function load_dependencies() {
    // Nothing to load
  }

  public function define_hooks() {
    $this->add_filter( 'page_feature', $this, 'page_feature' );
  }

  public function page_feature( $options ) {
    $title = $options['title'];
    $image = $options['image'];
    $description = $options['description'];
    $type = $options['type'];

    if ( ( isset( $title ) ||
         isset( $image ) ||
         isset( $description ) ) &&
         ( 'ratio' === $type || 'slim' === $type ) ) {
      $html  = '<div class="column">';
      $html .= '  <div class="region region-content">';
      $html .= '    <div class="block block-system">';
      $html .= '      <div class="content">';
      $html .= '        <div class="panel-display clearfix">';

      if ( 'ratio' === $type ) {
        // =====
        // Ratio
        // =====
        $html     .= '<section class="sos-hero">';
        $image_tag = '<img src="%s" class="sos-image-hero"/>';

        if ( isset( $image ) ) {
          $html .= sprintf( $image_tag, $image );
        }

        $html_description = '';

        if ( isset( $description ) ) {
          $html_description .= '<div class="pane-content">';
          $html_description .= '  <div class="fieldable-panels-pane">';
          $html_description .= '    <div class="field field-name-field-basic-text-text field-type-text-long field-label-hidden">';
          $html_description .= '      <div class="field-items">';
          $html_description .= '        <div class="field-item even">';
          $html_description .= '          <p>';
          $html_description .= $description;
          $html_description .= '          </p>';
          $html_description .= '        </div>';
          $html_description .= '      </div>';
          $html_description .= '    </div>';
          $html_description .= '  </div>';
          $html_description .= '</div>';
        }

        $html_mobile = '<div class="sos-hero-mobile">';
        if ( isset( $title ) ) {
          $html_mobile .= '<h1 class="pane-title">';
          $html_mobile .= $title;
          $html_mobile .= '</h1>';
        }

        if ( isset( $description ) ) {
          $html_mobile .= '<div class="pane-content">';
          $html_mobile .= $description;
          $html_mobile .= '</div>';
        }
        $html_mobile .= '</div>';
      } else if ( 'slim' === $type ) {
        // ====
        // Slim
        // ====
        $image_tag = '<section class="sos-hero-slim" style="background-image: url(%s)">';

        if ( isset( $image ) ) {
          $html .= sprintf( $image_tag, $image );
        } else {
          $html .= sprintf( $image_tag, '' );
        }
      }

      $html .= '           <div class="container">';
      $html .= '             <div class="row">';
      $html .= '               <div class="fdt-home-container fdt-home-column-content clearfix panel-panel row-fluid container">';
      $html .= '                 <div class="fdt-home-column-content-region fdt-home-row panel-panel span12">';
      $html .= '                   <div class="panel-pane pane-fieldable-panels-pane pane-fpid-12 pane-bundle-text">';

      if ( isset( $title ) ) {
        $html .= '<h1 class="pane-title">';
        $html .= $title;
        $html .= '</h1>';
      }

      if ( isset( $html_description ) ) {
        $html .= $html_description;
      }

      $html .= '                   </div>';
      $html .= '                 </div>';
      $html .= '               </div>';
      $html .= '             </div>';
      $html .= '           </div>';
      $html .= '         </section>';
      $html .= '        </div>';
      $html .= '      </div>';
      $html .= '    </div>';
      $html .= '  </div>';
      $html .= '</div>';

      if ( isset( $html_mobile ) ) {
        $html .= $html_mobile;
      }

      return $html;
    }

    return null;
  }
}