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
   * To Test Kiosk posts using tags... will work only if we have tags with names
   * Kiosk SOS kiosk-sos Kiosk SSS kiosk-sss-lounge
   */
  function test_kiosk_posts_shortcode_default() {
    $this->assertTrue( shortcode_exists( 'kiosk-posts' ) );

    // Test no tags
    $content = do_shortcode( '[kiosk-posts]' );
    $this->assertContains(
        'kiosk-posts__slider__slide',
        $content,
        'Return all posts when no tags provided'
    );
  }

  function test_kiosk_posts_shortcode_empty_tags() {
    // Test empty tag
    $content = do_shortcode( '[kiosk-posts tags=""]' );
    $this->assertContains(
        'kiosk-posts__slider__slide',
        $content,
        'Return all posts when empty string passed on tags'
    );
  }
  function test_kiosk_posts_shortcode_with_single_tag() {
    // Test with tags
    $content = do_shortcode( '[kiosk-posts tags="Kiosk"]' );
    $this->assertContains(
        'kiosk-posts__slider__slide',
        $content,
        'Return all posts with kisok tags'
    );
  }

  function test_kiosk_posts_shortcode_with_multiple_tag() {
    // Test with multipe tags
    $content = do_shortcode(
        '[kiosk-posts tags="Kiosk,SOS,SSS,kisok,abc,linux,windows"]'
    );
    $this->assertContains(
        'kiosk-posts__slider__slide',
        $content,
        'Return all posts with kisok Kiosk,SOS,SSS,kisok,abc,linux,windows'
    );
  }

  function test_kiosk_posts_shortcode_with_wrong_tag() {
    // Test with wrong tags
    $content = do_shortcode( '[kiosk-posts tags="no tags with this name"]' );
    $this->assertNotContains(
        'kiosk-posts__slider__slide',
        $content,
        'Do not return any posts'
    );
  }

  function test_kiosk_posts_shortcode_with_wrong_tag_default_images() {
    // Test with wrong tags and default image
    $content = do_shortcode(
        '[kiosk-posts tags="no tags with this name" 
        default_image="wp-content/uploads/Desert.jpg,
            wp-content/uploads/Desert.jpg,wp-content/uploads/Desert.jpg"]'
    );
    $this->assertContains(
        'kiosk-posts__slider__slide',
        $content,
        'Return the default images when tags are not present'
    );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertEquals( 3, $numberOfEvents, 'There should 3 event items' );
  }
}