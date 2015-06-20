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
   * @param array $data with 'start-date' 'end-date' 'label' in each row
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
      $row_start_time   = strtotime( $row['start-date'] );
      $row_end_time     = strtotime( $row['end-date'] );
      $row_label        = $row['label'];
      $margin_left      = self::get_row_margin_percentage( $timeline_start, $row_start_time, $timeline_start, $timeline_end );
      $margin_right     = self::get_row_margin_percentage( $row_end_time, $timeline_end, $timeline_start, $timeline_end );
      $rows            .= self::create_row( $margin_left, $margin_right, $row_label , $row_start_time, $row_end_time );
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
  public static function create_row( $margin_left, $margin_right, $row_label, $row_start_time, $row_end_time ) {
    $html = <<<HTML
    <div  title="Start Date: %s End Date: %s" class="timeline-row-item" style="margin-left:%s;
                margin-right:%s;
                ">&nbsp;<div class="timeline-row-item--text">%s</div>
    </div>
HTML;
    return sprintf(
        $html,
        date( 'F j, Y', $row_start_time ),
        date( 'F j, Y', $row_end_time ),
        $margin_left,
        $margin_right,
        $row_label
    );
  }
}