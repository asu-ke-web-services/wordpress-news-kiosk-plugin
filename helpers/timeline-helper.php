<?php
  /**
   * TimeLine helper
   *
   */
namespace Kiosk_WP;
class TimeLine_Helper {
  public function __construct() {
  }
  /**
   * create_timeline( $data )
   * @param array $data with 'start-date' 'end-date' 'label' 'status' in each row
   * @return string
   */
  public static function create_timeline( $data ) {
    $start_dates        = array_column( $data,'start-date' );
    $end_dates          = array_column( $data, 'end-date' );
    $least_start_date   = $start_dates[ self::get_least_date( $start_dates )[0] ] ;
    $highest_end_date   = $end_dates[ self::get_highest_date( $end_dates )[0] ] ;
    $least_start_date   = strtotime( $least_start_date );
    $start_year         = date( 'Y', $least_start_date ); // gives 2011
    $highest_end_date   = strtotime( $highest_end_date );
    $end_year           = date( 'Y', $highest_end_date ); // gives 2011
    $timeline_header    = self::create_timeline_header_years( $start_year, $end_year );
    $rows               = '';
    $timeline_start     = strtotime( 'Jan 1, ' . $start_year );
    $timeline_end       = strtotime( 'Dec 31, ' . $end_year );
    foreach ( $data as $row ) {
      $post_start_time   = strtotime( $row['start-date'] );
      $post_end_time     = strtotime( $row['end-date'] );
      $post_label        = $row['label'];
      $post_status        = $row['status'];
      $margin_left       = self::get_row_margin_percentage( $timeline_start, $post_start_time, $timeline_start, $timeline_end );
      $row_width         = self::get_row_margin_percentage( $post_start_time, $post_end_time, $timeline_start, $timeline_end );
      $rows             .= self::create_row( $margin_left, $row_width, $post_label , $post_start_time, $post_end_time, $post_status );
    }
    return '<div class="timeline-block">' . $timeline_header . $rows . '</div>';
  }
  /**
   * get_least_date( $dates ) gets the least date  value among the array
   * @param array $dates
   * @return int index or array of indexes if duplicates found
   */
  public static function get_least_date( $dates ) {
    $dates    = array_map( 'strtotime', $dates );
    $min_key  = array_keys( $dates, min( $dates ) );
    return $min_key;
  }
  /**
   * get_highest_date( $dates ) gets the highest date value among the array
   * @param array $dates
   * @return int index or array of indexes if duplicates found
   */
  public static function get_highest_date( $dates ) {
    $dates    = array_map( 'strtotime',$dates );
    $max_key  = array_keys( $dates, max( $dates ) );
    return $max_key;
  }
  /**
   * create_timeline_header_years( $start, $end )
   * creates a div for timeline header from start to end values
   * @param int $start
   * @param int $end
   * @return string
   */
  public static function create_timeline_header_years( $start, $end ) {
    $html = '';
    for ( $i = $start; $i <= $end; $i++ ) {
      $html .= '<div id="header_section_' . $i . '" class="timeline_header_section">'. $i .'</div>';
    }
    $html = '<div class="timeline_header">' . $html . '</div>';
    return $html;
  }
  /**
   * get_time_difference( $start, $end )
   * @param string $start
   * @param string $end
   * @return int
   */
  public static function get_time_difference( $start, $end ) {
    return strtotime( $end ) - strtotime( $start );
  }
  /**
   * get_row_margin_percentage()
   * @param int $row_start
   * @param int $row_end
   * @param int $header_start
   * @param int $header_end
   */
  public static function get_row_margin_percentage( $row_start, $row_end, $header_start, $header_end ) {
    $header_width         = $header_end - $header_start;
    $row_width            = $row_end - $row_start;
    $row_width_percentage = $row_width * 100 / $header_width;
    return $row_width_percentage . '%';
  }
  public static function normalize( $max, $min, $value ){
    return ( $value - $min ) / ( $max - $min ) * 100;
  }
  public static function create_row( $margin_left, $row_width, $post_label, $post_start_time, $post_end_time, $post_status ) {
    $html = <<<HTML
    <div class="timeline-row-item">
      <div class="timeline-row-item__margin" style="margin-left:%s;width:%s;">&nbsp;</div>
      <div class="timeline-row-item__duration">%s to %s</div>
      <div class="timeline-row-item__status">%s</div>
      <div class="timeline-row-item__text">%s</div>
    </div>
HTML;
    return sprintf(
        $html,
        $margin_left,
        $row_width,
        date( 'm/d/y', $post_start_time ),
        date( 'm/d/y', $post_end_time ),
        $post_status,
        $post_label
    );
  }
}