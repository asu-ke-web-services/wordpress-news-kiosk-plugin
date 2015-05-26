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
    // Test with mockup data
    $stub = $this->getMock( 'Kiosk_WP\Kiosk_News_Shortcodes', array( 'kiosk_news_fetch_feed' ) );

    $stub->expects( $this->any() )
         ->method( 'kiosk_news_fetch_feed' )
         ->will( $this->returnValue( $this->return_unit_test_data() ) );

    $content = $stub->kiosk_asu_news( '' );

    // Test no tags by default limit 20 news items if found
    $content = $stub->kiosk_asu_news( '' );
    $this->assertContains( 'kiosk_asu_news_slider', $content, 'Returns asu news items' );

    // Test limit tag
    //$content = do_shortcode( '[kiosk-asu-news limit="20"]' );
    $content = $stub->kiosk_asu_news( array( 'limit' => 20 ) );
    $this->assertContains( 'kiosk_asu_news_slider', $content, 'Returns asu news items' );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertLessThanOrEqual( 20, $numberOfEvents, 'There should be <= 20 news items' );

    // Test with content_limit tag
    //$content = do_shortcode( '[kiosk-asu-news content_limit="10"]' );
    $content = $stub->kiosk_asu_news( array( 'limit' => 10 ) );
    $this->assertContains( 'kiosk_asu_news_slider', $content, 'Returns asu news items' );

    // Test with feed tag
    //$content = do_shortcode( '[kiosk-asu-news feed="153,40,358"]' );
    $content = $stub->kiosk_asu_news( array( 'feed' => '153,40,358' ) );
    $this->assertContains( 'kiosk_asu_news_slider', $content, 'Returns asu news items' );

    // Test with multipe tags
    //$content = do_shortcode( '[kiosk-asu-news feed="153,40,358" limit="5" ]' );
    $content = $stub->kiosk_asu_news( array( 'feed' => '153,40,358', 'limit' => 5 ) );
    $this->assertContains( 'kiosk_asu_news_slider', $content, 'Returns asu news items' );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertLessThanOrEqual( 5, $numberOfEvents, 'There should be <= 5 news items' );
  }
  /**
  * return_unit_test_data() creates a mock up data to be used as feed data 
  * @return SimplePie
  */
  function return_unit_test_data(){
    $sample_json = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xml:base="https://asunews.asu.edu"  xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:media="http://search.yahoo.com/mrss/">
<channel>
 <title>ASU News - Sustainability, School of</title>
 <link>https://asunews.asu.edu/taxonomy/term/153/all</link>
 <description>Content from ASU&#039;s School of Sustainability.</description>
 <language>en</language>
<item>
 <title>Doctoral grad fosters the sustainability movement</title>
 <link>https://asunews.asu.edu/20150515--omaya-ahmad-sustainability-grad</link>
 <description>&lt;p&gt;Omaya Ahmad, who is completing her doctorate in Arizona State University&#039;s School of Sustainability at ASU, has spent the past decade thriving in her academic career.&lt;/p&gt;
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
&lt;p&gt;&lt;a href=&quot;https://asunews.asu.edu/20150515--omaya-ahmad-sustainability-grad&quot; target=&quot;_blank&quot;&gt;read more&lt;/a&gt;&lt;/p&gt;</description>
 <category domain="https://asunews.asu.edu/taxonomy/term/352">science &amp; tech</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/49">Biodesign Institute</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/153">Sustainability, School of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/169">Academic</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/238">Science and technology</category>
 <pubDate>Fri, 15 May 2015 20:56:03 +0000</pubDate>
 <dc:creator>lccampb</dc:creator>
 <guid isPermaLink="false">32532 at https://asunews.asu.edu</guid>
</item>
<item>
 <title>Giving everyday citizens a voice in global policy decisions</title>
 <link>https://asunews.asu.edu/20150501-wwviews-climate-energy-citizen-input</link>
 <description>&lt;p&gt;On June 6, thousands of average citizens around the world, including a group of 100 at Arizona State University, will have the opportunity to give their take on climate change and energy.&lt;/p&gt;
&lt;p&gt;&lt;a href=&quot;https://asunews.asu.edu/20150501-wwviews-climate-energy-citizen-input&quot; target=&quot;_blank&quot;&gt;read more&lt;/a&gt;&lt;/p&gt;</description>
 <category domain="https://asunews.asu.edu/taxonomy/term/352">science &amp; tech</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/13">News Release</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/63">College of Liberal Arts and Sciences</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/153">Sustainability, School of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/178">Sustainabilty, Global Institute of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/104">Science</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/30">Community</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/120">ASU as New American University</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/7">Be socially embedded</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/8">Engage Globally</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/1">Leverage our place</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/70">Tempe campus</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/2">Transform society</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/693">Washington DC</category>
 <pubDate>Fri, 01 May 2015 16:55:32 +0000</pubDate>
 <dc:creator>jlpillen</dc:creator>
 <guid isPermaLink="false">32423 at https://asunews.asu.edu</guid>
</item>
<item>
 <title>ASU named No. 1 producer of solar power among campuses nationwide</title>
 <link>https://asunews.asu.edu/20150422-asu-top-solar-energy-producer</link>
 <description>&lt;p&gt;An April 20 article from Energy Digital featured the top 10 campuses in the nation for solar energy production, with Arizona State University coming in at No. 1.&lt;/p&gt;
&lt;div class=&quot;field field-type-text field-field-source&quot;&gt;
      &lt;div class=&quot;field-label&quot;&gt;Article source:&amp;nbsp;&lt;/div&gt;
    &lt;div class=&quot;field-items&quot;&gt;
            &lt;div class=&quot;field-item odd&quot;&gt;
                    Energy Digital        &lt;/div&gt;
        &lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;field field-type-link field-field-url&quot;&gt;
      &lt;div class=&quot;field-label&quot;&gt;Article:&amp;nbsp;&lt;/div&gt;
    &lt;div class=&quot;field-items&quot;&gt;
            &lt;div class=&quot;field-item odd&quot;&gt;
                    &lt;a href=&quot;http://www.energydigital.com/top10/3530/Top-10-Which-Campuses-Take-the-Cake-on-Solar-Energy&quot; target=&quot;_blank&quot;&gt;http://www.energydigital.com/top10/3530/Top-10-Which-Campuses-Take-the-Cake-on-Solar-Energy&lt;/a&gt;        &lt;/div&gt;
        &lt;/div&gt;
&lt;/div&gt;
&lt;fieldset class=&quot;fieldgroup group-related-links-story-video-&quot;&gt;&lt;legend&gt;Related story/video/photo gallery&lt;/legend&gt;

&lt;pre&gt;
&lt;/pre&gt;
&lt;/fieldset&gt;
&lt;fieldset class=&quot;fieldgroup group-author-information-&quot;&gt;&lt;legend&gt;Author Information&lt;/legend&gt;&lt;div class=&quot;field field-type-text field-field-add-contributor-info&quot;&gt;
      &lt;div class=&quot;field-label&quot;&gt;Contributor contact information:&amp;nbsp;&lt;/div&gt;
    &lt;div class=&quot;field-items&quot;&gt;
            &lt;div class=&quot;field-item odd&quot;&gt;
                    No, do not include any contact information        &lt;/div&gt;
        &lt;/div&gt;
&lt;/div&gt;
&lt;/fieldset&gt;
&lt;p&gt;&lt;a href=&quot;https://asunews.asu.edu/20150422-asu-top-solar-energy-producer&quot; target=&quot;_blank&quot;&gt;read more&lt;/a&gt;&lt;/p&gt;</description>
 <category domain="https://asunews.asu.edu/taxonomy/term/353">university</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/17">ASU news coverage</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/153">Sustainability, School of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/178">Sustainabilty, Global Institute of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/40">Sustainability</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/317">Renewable energy</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/358">Sustainability @ ASU</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/34">News coverage</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/120">ASU as New American University</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/1">Leverage our place</category>
 <pubDate>Wed, 22 Apr 2015 20:25:08 +0000</pubDate>
 <dc:creator>egregusk</dc:creator>
 <guid isPermaLink="false">32376 at https://asunews.asu.edu</guid>
</item>
<item>
 <title>ASU students to &#039;Ditch the Dumpster&#039; on Earth Day</title>
 <link>https://asunews.asu.edu/20150421-asu-ditch-the-dumpster</link>
 <description>&lt;p&gt;ASU students across all campuses are being asked to &quot;Ditch the Dumpster&quot; by donating their unwanted items from campus residence halls instead of throwing them away.&lt;/p&gt;
&lt;p&gt;&lt;a href=&quot;https://asunews.asu.edu/20150421-asu-ditch-the-dumpster&quot; target=&quot;_blank&quot;&gt;read more&lt;/a&gt;&lt;/p&gt;</description>
 <category domain="https://asunews.asu.edu/taxonomy/term/353">university</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/13">News Release</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/432">Business and Finance/CFO</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/153">Sustainability, School of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/178">Sustainabilty, Global Institute of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/358">Sustainability @ ASU</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/242">Community partnerships</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/370">University news</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/70">Tempe campus</category>
 <pubDate>Tue, 21 Apr 2015 18:35:37 +0000</pubDate>
 <dc:creator>wcraft</dc:creator>
 <guid isPermaLink="false">32360 at https://asunews.asu.edu</guid>
</item>
<item>
 <title>ASU among nation&#039;s most sustainable colleges</title>
 <link>https://asunews.asu.edu/20150420-princeton-review-asu-sustainable-college</link>
 <description>&lt;p&gt;Arizona State University has been named one of the nation&#039;s most sustainable colleges for the sixth year in a row by The Princeton Review&#039;s Guide to 353 Green Colleges.&lt;/p&gt;
&lt;p&gt;&lt;a href=&quot;https://asunews.asu.edu/20150420-princeton-review-asu-sustainable-college&quot; target=&quot;_blank&quot;&gt;read more&lt;/a&gt;&lt;/p&gt;</description>
 <category domain="https://asunews.asu.edu/taxonomy/term/353">university</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/13">News Release</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/432">Business and Finance/CFO</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/153">Sustainability, School of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/178">Sustainabilty, Global Institute of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/40">Sustainability</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/18">University</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/358">Sustainability @ ASU</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/370">University news</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/120">ASU as New American University</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/73">Downtown Phoenix campus</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/1">Leverage our place</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/72">Polytechnic campus</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/70">Tempe campus</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/71">West campus</category>
 <pubDate>Mon, 20 Apr 2015 17:36:38 +0000</pubDate>
 <dc:creator>cderra</dc:creator>
 <guid isPermaLink="false">32352 at https://asunews.asu.edu</guid>
</item>
<item>
 <title>Study finds new link between environment, urban diets</title>
 <link>https://asunews.asu.edu/20150409-environment-urban-diet</link>
 <description>&lt;p&gt;An international team led by Arizona State University sustainability scientist Arianne Cease found that what we choose to eat can influence the nutrient cycle.&lt;/p&gt;
&lt;p&gt;&lt;a href=&quot;https://asunews.asu.edu/20150409-environment-urban-diet&quot; target=&quot;_blank&quot;&gt;read more&lt;/a&gt;&lt;/p&gt;</description>
 <category domain="https://asunews.asu.edu/taxonomy/term/352">science &amp; tech</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/13">News Release</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/153">Sustainability, School of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/178">Sustainabilty, Global Institute of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/40">Sustainability</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/368">Science and tech</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/120">ASU as New American University</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/4">Conduct use-inspired research</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/70">Tempe campus</category>
 <pubDate>Thu, 09 Apr 2015 20:13:24 +0000</pubDate>
 <dc:creator>mlalbrec</dc:creator>
 <guid isPermaLink="false">32300 at https://asunews.asu.edu</guid>
</item>
<item>
 <title>ASU study shows online education to be key driver in university sustainability strategies</title>
 <link>https://asunews.asu.edu/20150406-online-education-sustainability</link>
 <description>&lt;p&gt;Report indicates online education substantially increases access to degrees, promotes innovation of technologies, creates socio-economic value and reduces the carbon footprint for higher education institutions.&lt;/p&gt;
&lt;p&gt;&lt;a href=&quot;https://asunews.asu.edu/20150406-online-education-sustainability&quot; target=&quot;_blank&quot;&gt;read more&lt;/a&gt;&lt;/p&gt;</description>
 <category domain="https://asunews.asu.edu/taxonomy/term/352">science &amp; tech</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/13">News Release</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/153">Sustainability, School of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/178">Sustainabilty, Global Institute of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/40">Sustainability</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/358">Sustainability @ ASU</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/368">Science and tech</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/120">ASU as New American University</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/4">Conduct use-inspired research</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/5">Enable student success</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/70">Tempe campus</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/2">Transform society</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/3">Value Entrepreneurship</category>
 <pubDate>Mon, 06 Apr 2015 15:25:02 +0000</pubDate>
 <dc:creator>jlfranz</dc:creator>
 <guid isPermaLink="false">32269 at https://asunews.asu.edu</guid>
</item>
<item>
 <title>Urban ecologists at ASU seek to understand human-nature dynamic</title>
 <link>https://asunews.asu.edu/20150320-asu-urban-ecologists</link>
 <description>&lt;p&gt;Ecologists don&#039;t just work in the wilderness. Urban ecologists at ASU&#039;s CAP LTER are helping us to understand how humans and nature interact in the city, and to maximize the benefits we get from our environment.&lt;/p&gt;
&lt;p&gt;&lt;a href=&quot;https://asunews.asu.edu/20150320-asu-urban-ecologists&quot; target=&quot;_blank&quot;&gt;read more&lt;/a&gt;&lt;/p&gt;</description>
 <category domain="https://asunews.asu.edu/taxonomy/term/352">science &amp; tech</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/13">News Release</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/455">researchmatters</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/114">School of Life Sciences</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/63">College of Liberal Arts and Sciences</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/405">Office of Knowledge Enterprise Development</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/153">Sustainability, School of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/178">Sustainabilty, Global Institute of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/35">Research</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/40">Sustainability</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/37">Life Science</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/245">CLAS top headlines</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/368">Science and tech</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/461">Bioscience &amp; Health</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/120">ASU as New American University</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/4">Conduct use-inspired research</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/1">Leverage our place</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/70">Tempe campus</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/2">Transform society</category>
 <pubDate>Thu, 19 Mar 2015 19:57:13 +0000</pubDate>
 <dc:creator>anicodem</dc:creator>
 <guid isPermaLink="false">32203 at https://asunews.asu.edu</guid>
</item>
<item>
 <title>ASU researchers detail participatory budgeting experiment</title>
 <link>https://asunews.asu.edu/20150316-cohen-schugurensky-time</link>
 <description>&lt;p&gt;Two ASU researcher detailed a Phoenix high school&#039;s participatory budgeting experiment – the first of its kind in the U.S. – in an article that was featured in TIME.&lt;/p&gt;
&lt;div class=&quot;field field-type-text field-field-source&quot;&gt;
      &lt;div class=&quot;field-label&quot;&gt;Article source:&amp;nbsp;&lt;/div&gt;
    &lt;div class=&quot;field-items&quot;&gt;
            &lt;div class=&quot;field-item odd&quot;&gt;
                    TIME        &lt;/div&gt;
        &lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;field field-type-link field-field-url&quot;&gt;
      &lt;div class=&quot;field-label&quot;&gt;Article:&amp;nbsp;&lt;/div&gt;
    &lt;div class=&quot;field-items&quot;&gt;
            &lt;div class=&quot;field-item odd&quot;&gt;
                    &lt;a href=&quot;http://time.com/3740510/phoenix-budgeting-experiment/&quot; target=&quot;_blank&quot;&gt;http://time.com/3740510/phoenix-budgeting-experiment/&lt;/a&gt;        &lt;/div&gt;
        &lt;/div&gt;
&lt;/div&gt;
&lt;fieldset class=&quot;fieldgroup group-related-links-story-video-&quot;&gt;&lt;legend&gt;Related story/video/photo gallery&lt;/legend&gt;

&lt;pre&gt;
&lt;/pre&gt;
&lt;/fieldset&gt;
&lt;fieldset class=&quot;fieldgroup group-author-information-&quot;&gt;&lt;legend&gt;Author Information&lt;/legend&gt;&lt;div class=&quot;field field-type-text field-field-add-contributor-info&quot;&gt;
      &lt;div class=&quot;field-label&quot;&gt;Contributor contact information:&amp;nbsp;&lt;/div&gt;
    &lt;div class=&quot;field-items&quot;&gt;
            &lt;div class=&quot;field-item odd&quot;&gt;
                    Yes, include my contributor contact information (story byline)        &lt;/div&gt;
        &lt;/div&gt;
&lt;/div&gt;
&lt;/fieldset&gt;
&lt;p&gt;&lt;a href=&quot;https://asunews.asu.edu/20150316-cohen-schugurensky-time&quot; target=&quot;_blank&quot;&gt;read more&lt;/a&gt;&lt;/p&gt;</description>
 <category domain="https://asunews.asu.edu/taxonomy/term/336">business, culture &amp; affairs</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/17">ASU news coverage</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/128">School of Public Affairs</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/66">College of Public Service and Community Solutions</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/153">Sustainability, School of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/357">Public service</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/34">News coverage</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/73">Downtown Phoenix campus</category>
 <pubDate>Mon, 16 Mar 2015 18:03:20 +0000</pubDate>
 <dc:creator>hbeshear</dc:creator>
 <guid isPermaLink="false">32171 at https://asunews.asu.edu</guid>
</item>
<item>
 <title>ASU launches academy to educate young students about sustainability</title>
 <link>https://asunews.asu.edu/20150226-sustainability-teachers-academy</link>
 <description>&lt;p&gt;Arizona State University&#039;s new National Sustainability Teachers&#039; Academy equips elementary, middle and high school teachers with knowledge and tools to educate and transform students into agents of change.&lt;/p&gt;
&lt;p&gt;&lt;a href=&quot;https://asunews.asu.edu/20150226-sustainability-teachers-academy&quot; target=&quot;_blank&quot;&gt;read more&lt;/a&gt;&lt;/p&gt;</description>
 <category domain="https://asunews.asu.edu/taxonomy/term/352">science &amp; tech</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/13">News Release</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/153">Sustainability, School of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/178">Sustainabilty, Global Institute of</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/40">Sustainability</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/30">Community</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/368">Science and tech</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/120">ASU as New American University</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/5">Enable student success</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/1">Leverage our place</category>
 <category domain="https://asunews.asu.edu/taxonomy/term/2">Transform society</category>
 <pubDate>Thu, 26 Feb 2015 16:50:39 +0000</pubDate>
 <dc:creator>nmuilenb</dc:creator>
 <guid isPermaLink="false">32074 at https://asunews.asu.edu</guid>
</item>
</channel>
</rss>

XML;
    $feed = new SimplePie();
    $feed->set_raw_data( $sample_json );
    $feed->init();
    $feed->handle_content_type();
    return $feed;
  }
}