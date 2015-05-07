<?php

class KioskTweetsTest extends WP_UnitTestCase {
  // @codingStandardsIgnoreStart
  static function setUpBeforeClass() {
    WP_UnitTestCase::setUpBeforeClass();

  }
  // @codingStandardsIgnoreEnd

  /**
   * To Test Kiosk tweets
   * [kiosk-tweets]
   */
  function test_kiosk_tweets_shortcode() {
    $this->assertTrue( shortcode_exists( 'kiosk-tweets' ) );

    // Test no tags
    //$content = do_shortcode( '[kiosk-tweets]' );
    //$this->assertContains( 'kiosk_tweets_item', $content, 'Should return current tweets item' );
  }
}