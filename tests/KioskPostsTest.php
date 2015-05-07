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

    // // Test no tags
    // $content = do_shortcode( '[kiosk-posts]' );
    // $this->assertContains( 'kiosk_events_caption carousel-caption', $content, 'Return all posts when no tags provided' );

    // // Test empty tag
    // $content = do_shortcode( '[kiosk-posts tags=""]' );
    // $this->assertContains( 'kiosk_events_caption carousel-caption', $content, 'Return all posts when empty string passed on tags' );

    // // Test with tags
    // $content = do_shortcode( '[kiosk-posts tags="Kiosk"]' );
    // $this->assertContains( 'kiosk_events_caption carousel-caption', $content, 'Return all posts with kisok tags' );

    // // Test with multipe tags
    // $content = do_shortcode( '[kiosk-posts tags="Kiosk,SOS,SSS,kisok,abc,linux,windows"]' );
    // $this->assertContains( 'kiosk_events_caption carousel-caption', $content,'Return all posts with kisok Kiosk,SOS,SSS,kisok,abc,linux,windows' );

    // // Test with wrong tags
    // $content = do_shortcode( '[kiosk-posts tags="no tags with this name"]' );
    // $this->assertNotContains( 'kiosk_events_caption carousel-caption', $content, 'Do not return any posts' );

  }
}