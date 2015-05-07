<?php

class KioskSliderTest extends WP_UnitTestCase {
  // @codingStandardsIgnoreStart
  static function setUpBeforeClass() {
    WP_UnitTestCase::setUpBeforeClass();

  }
  // @codingStandardsIgnoreEnd

  /**
   * To Test Kiosk slider can be called with limit attribute
   * [kiosk-slider limit ='20']
   */
  function test_kiosk_slider_shortcode() {
    $this->assertTrue( shortcode_exists( 'kiosk-slider' ) );

    // Test no tags by default limit 20 image items if found
    $content = do_shortcode( '[kiosk-slider]' );
    $this->assertContains( 'kiosk_gallery_slider', $content, 'Should return images with div tag' );

    // Test limit attribute
    $content = do_shortcode( '[kiosk-slider limit="20"]' );
    $this->assertContains( 'kiosk_gallery_slider', $content, 'Should return images with div tag' );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertLessThanOrEqual( 20, $numberOfEvents, 'There should be <= 20 image items' );
  }
}