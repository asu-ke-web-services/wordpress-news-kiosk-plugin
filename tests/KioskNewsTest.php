<?php
/**
 * @group news
 */
class KioskNewsTest extends WP_UnitTestCase {
  private $feed_stub  = null;
  private $stub       = null;
  // @codingStandardsIgnoreStart
  static function setUpBeforeClass() {
    WP_UnitTestCase::setUpBeforeClass();
  }

  // Setup the mockup data for the test case
  function setUp() {
        // Mockup the data
    $this->feed_stub = $this->getMock(
        'Kiosk_WP\Feed_Helper',
        array( 'fetch_feed_data' )
    );
    $this->feed_stub->expects( $this->any() )
         ->method( 'fetch_feed_data' )
         ->with( $this->stringContains( 'asunews' ) )
         ->will( $this->returnValue( $this->return_unit_test_data() ) );
    $this->stub = $this->getMockBuilder( 'Kiosk_WP\Kiosk_News_Shortcodes' )
         ->setConstructorArgs( array( $this->feed_stub ) )
         ->setMethods( null )
         ->getMock();
  }
  // @codingStandardsIgnoreEnd

  /**
   * test for shortcode existence
   */
  function test_kiosk_news_shortcode_exists() {
        $this->assertTrue( shortcode_exists( 'kiosk-asu-news' ) );
  }
  /**
   * To Test Kiosk asu news can be called with limit and feed attributes
   * [kiosk-asu-news limit ='20' feed_ids='153,40,178,358']
   */
  function test_kiosk_news_shortcode_without_attributes() {
    // Test no tags by default limit 20 news items if found
    $content = $this->stub->kiosk_asu_news( '' );

    $this->assertContains(
        'kiosk-asu-news__slider__slide',
        $content,
        'Returns asu news items'
    );
  }

  function test_kiosk_news_shortcode_with_limit() {
    // Test limit tag
    $content = $this->stub->kiosk_asu_news( array( 'limit' => 20 ) );
    $this->assertContains(
        'kiosk-asu-news__slider__slide',
        $content,
        'Returns asu news items'
    );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertLessThanOrEqual(
        20,
        $numberOfEvents,
        'There should be <= 20 news items'
    );
  }

  function test_kiosk_news_shortcode_with_content_limit() {
    // Test with content_limit tag
    $content = $this->stub->kiosk_asu_news( array( 'content_limit' => '50' ) );
    $this->assertContains(
        'kiosk-asu-news__slider__slide',
        $content,
        'Returns asu news items'
    );
  }

  function test_kiosk_news_shortcode_with_feed_ids() {
    // Test with feed tag
    $content = $this->stub->kiosk_asu_news(
        array( 'feed_ids' => '153,40,358' )
    );
    $this->assertContains(
        'kiosk-asu-news__slider__slide',
        $content,
        'Returns asu news items'
    );
  }

  function test_kiosk_news_shortcode_with_multiple_attributes() {
    // Test with multipe tags
    $content = $this->stub->kiosk_asu_news(
        array( 'feed_ids' => '153,40,358', 'limit' => 5 )
    );
    $this->assertContains(
        'kiosk-asu-news__slider__slide',
        $content,
        'Returns asu news items'
    );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertLessThanOrEqual(
        5,
        $numberOfEvents,
        'There should be <= 5 news items'
    );
  }

  /**
  * Creates a mock up data to be used as feed data
  * @return SimplePie
  */
  function return_unit_test_data() {
    $sample_json = <<<XML
<?xml version="1.0" encoding="utf-8"?><rss version="2.0" xml:base="https://asunews.asu.edu" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:media="http://search.yahoo.com/mrss/"><channel><title>ASU News - Sustainability, School of</title><link>https://asunews.asu.edu/taxonomy/term/153/all</link><description>Content from ASU&#039;s School of Sustainability.</description><language>en</language><item><title>Doctoral grad fosters the sustainability movement</title><link>https://asunews.asu.edu/20150515--omaya-ahmad-sustainability-grad</link><description>&lt;p&gt;Omaya Ahmad, who is completing her doctorate in Arizona State University&#039;s School of Sustainability at ASU, has spent the past decade thriving in her academic career.&lt;/p&gt;
&lt;fieldset class=&quot;fieldgroup group-video-settings&quot;&gt;&lt;legend&gt;Video settings&lt;/legend&gt;&lt;div class=&quot;field field-type-text field-field-video-estimated-time&quot;&gt;
      &lt;div class=&quot;field-label&quot;&gt;Estimated Time:&amp;nbsp;&lt;/div&gt;
    &lt;div class=&quot;field-items&quot;&gt;
            &lt;div class=&quot;field-item odd&quot;&gt;
                    3 min 27 sec        &lt;/div&gt;
        &lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;field field-type-emvideo field-field-external-video&quot;&gt;
      &lt;div class=&quot;field-label&quot;&gt;External Video:&amp;nbsp;&lt;/div&gt;
    &lt;div class=&quot;field-items&quot;&gt;
            &lt;div class=&quot;field-item odd&quot;&gt;
                    &lt;div class=&quot;emvideo emvideo-video emvideo-vimeo&quot;&gt;&lt;div id=&quot;media-vimeo-1&quot; class=&quot;media-vimeo&quot;&gt;
    &lt;iframe src=&quot;https://player.vimeo.com/video/127778522?fullscreen=1&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;autoplay=0&quot; width=&quot;425&quot; height=&quot;350&quot;&gt;&lt;/iframe&gt;
&lt;/div&gt;
&lt;/div&gt;        &lt;/div&gt;
        &lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;field field-type-filefield field-field-preview-image&quot;&gt;
      &lt;div class=&quot;field-label&quot;&gt;Preview Image:&amp;nbsp;&lt;/div&gt;
    &lt;div class=&quot;field-items&quot;&gt;
            &lt;div class=&quot;field-item odd&quot;&gt;
                    &lt;div class=&quot;filefield-file&quot;&gt;&lt;img class=&quot;filefield-icon field-icon-image-png&quot;  alt=&quot;image/png icon&quot; src=&quot;https://asunews.asu.edu/sites/all/modules/filefield/icons/image-x-generic.png&quot; /&gt;&lt;a href=&quot;//%252Ftmp/omaya-ahmad-grad-video.png&quot; type=&quot;image/png; length=449775&quot;&gt;omaya-ahmad-grad-video.png&lt;/a&gt;&lt;/div&gt;        &lt;/div&gt;
        &lt;/div&gt;
&lt;/div&gt;
&lt;/fieldset&gt;
&lt;fieldset class=&quot;fieldgroup group-related-for-video&quot;&gt;&lt;legend&gt;Related&lt;/legend&gt;&lt;div class=&quot;field field-type-nodereference field-field-related-story-video-page&quot;&gt;
      &lt;div class=&quot;field-label&quot;&gt;Related Story:&amp;nbsp;&lt;/div&gt;
    &lt;div class=&quot;field-items&quot;&gt;
            &lt;div class=&quot;field-item odd&quot;&gt;
                    &lt;a href=&quot;/20150508-spring-graduate-profiles&quot;&gt;ASU&amp;#039;s class of 2015 rises to the top&lt;/a&gt;        &lt;/div&gt;
        &lt;/div&gt;
&lt;/div&gt;
&lt;/fieldset&gt;
&lt;p&gt;&lt;a href=&quot;https://asunews.asu.edu/20150515--omaya-ahmad-sustainability-grad&quot; target=&quot;_blank&quot;&gt;read more&lt;/a&gt;&lt;/p&gt;</description><category domain="https://asunews.asu.edu/taxonomy/term/352">science &amp; tech</category><pubDate>Fri, 15 May 2015 20:56:03 +0000</pubDate><dc:creator>lccampb</dc:creator><guid isPermaLink="false">32532 at https://asunews.asu.edu</guid></item></channel></rss>
XML;
    $feed = new SimplePie();
    $feed->set_raw_data( $sample_json );
    $feed->init();
    $feed->handle_content_type();
    return $feed;
  }
}