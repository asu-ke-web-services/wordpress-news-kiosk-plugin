<?php

class KioskNewsTest extends WP_UnitTestCase {
  // @codingStandardsIgnoreStart
  static function setUpBeforeClass() {
    WP_UnitTestCase::setUpBeforeClass();

  }
  // @codingStandardsIgnoreEnd

  /**
   * To Test Kiosk asu news can be called with limit and feed attributes
   * [kiosk-asu-news limit ='20' feed='153,40,178,358']
   */
  function test_kiosk_news_shortcode() {
    $this->assertTrue( shortcode_exists( 'kiosk-asu-news' ) );

    // Test no tags by default limit 20 news items if found
    $content = do_shortcode( '[kiosk-asu-news]' );
    $this->assertContains( 'kiosk_asu_news_slider', $content, 'Returns asu news items' );

    // Test limit tag
    $content = do_shortcode( '[kiosk-asu-news limit="20"]' );
    $this->assertContains( 'kiosk_asu_news_slider', $content, 'Returns asu news items' );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertLessThanOrEqual( 20, $numberOfEvents, 'There should be <= 20 news items' );

    // Test with content_limit tag
    $content = do_shortcode( '[kiosk-asu-news content_limit="10"]' );
    $this->assertContains( 'kiosk_asu_news_slider', $content, 'Returns asu news items' );

    // Test with feed tag
    $content = do_shortcode( '[kiosk-asu-news feed="153,40,358"]' );
    $this->assertContains( 'kiosk_asu_news_slider', $content, 'Returns asu news items' );

    // Test with multipe tags
    $content = do_shortcode( '[kiosk-asu-news feed="153,40,358" limit="5" ]' );
    $this->assertContains( 'kiosk_asu_news_slider', $content, 'Returns asu news items' );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertLessThanOrEqual( 5, $numberOfEvents, 'There should be <= 5 news items' );
  }
}