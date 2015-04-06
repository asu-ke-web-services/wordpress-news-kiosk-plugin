<?php

class KioskPostsTest extends WP_UnitTestCase {
  // @codingStandardsIgnoreStart
  static function setUpBeforeClass() {
    WP_UnitTestCase::setUpBeforeClass();

    // Activate our plugin!
    $plugin = new \Kiosk_WP\Kiosk_Plugin();

  }
  // @codingStandardsIgnoreEnd

  /**
   * To Test Kiosk posts using tags will work only if have tags with names Kiosk SOS kiosk-sos Kiosk SSS kiosk-sss-lounge
   */
  function test_kiosk_posts_short_should_have_the_correct_number_of_entries() {
    $this->assertTrue( shortcode_exists( 'kiosk-posts' ) );

    // Test no limit
    $content = do_shortcode( '[kiosk-posts]' );
    $this->assertEquals( '', $content, 'Empty string should be returned when no limit is provided' );

    // Test 0
    $content = do_shortcode( '[kiosk-posts tags='']' );
    $this->assertEquals( '', $content, 'Empty string should be returned when passed empty string' );

    // Test 1
    $content = do_shortcode( '[kiosk-posts tags="Kiosk,SSS,kiosk-sss-lounge,SOS,kiosk-sos"' );
    $this->assertContains( 'kiosk-slider-caption carousel-caption', $content );
  }
}