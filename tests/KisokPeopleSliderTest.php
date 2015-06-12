<?php

class KioskPeopleSliderTest extends WP_UnitTestCase {
  // @codingStandardsIgnoreStart
  static function setUpBeforeClass() {
    WP_UnitTestCase::setUpBeforeClass();

  }
  // @codingStandardsIgnoreEnd

  /**
   * To Test Kiosk people slider can be called with limit attribute
   * [kiosk-people-slider gios_url ='url']
   */
  function test_kiosk_people_slider_shortcode() {
    $this->assertTrue( shortcode_exists( 'kiosk-people-slider' ) );

    $people_slider_helper_stub = $this->getMock(
        'Kiosk_WP\People_Slider_Helper',
        array(
            'get_persons_images_category_keywords',
            'get_person_image',
        )
    );

    $people_slider_helper_stub->expects( $this->any() )
                              ->method( 'get_persons_images_category_keywords' )
                              ->will(
                                  $this->returnValue(
                                      $this->return_unit_test_data(
                                          'get_persons_images_category_keywords'
                                      )
                                  )
                              );

    $people_slider_helper_stub->expects( $this->any() )
                              ->method( 'get_person_image' )
                              ->will(
                                  $this->returnValue(
                                      $this->return_unit_test_data( 'get_person_image' )
                                  )
                              );

    $stub = $this->getMockBuilder( 'Kiosk_WP\Kiosk_People_Slider_Shortcodes' )
          ->setConstructorArgs( array( $people_slider_helper_stub ) )
          ->setMethods( null )
          ->getMock();

    $content = $stub->kiosk_people_slider( '' );
    $this->assertContains( 'kiosk-people-slider__layout', $content, 'Should return carousel slider' );
    $content = $stub->kiosk_people_slider( '' );
    $this->assertContains( 'kiosk-people-slider__layout', $content, 'Should return carousel slider' );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertEquals( 3, $numberOfEvents, 'There should 3 slider items' );

    $test_with_content = <<<HTML
     keyword:Future and systems thinking
      <br/> person-slug:jeffrey-englin
      <br/> quote:Live for today&#8230; Hope for tomorrow
      <br/> keyword:deforestation
      <br/> keyword:deforestation
      <br/> person-slug:jeffrey-englin
      <br/> 
HTML;
    $content = $stub->kiosk_people_slider( array(), $test_with_content );
    $this->assertContains( 'kiosk-people-slider__layout', $content, 'Should return carousel slider' );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertEquals( 3, $numberOfEvents, 'There should 3 slider items' );
    $this->assertContains( 'aadhikari', $content, 'Should return content person-slug image' );

  }
  /**
    * return_unit_test_data() creates a mock up data
    * @return mixed
    */
  function return_unit_test_data( $method_name ){
    if ( 'get_persons_images_category_keywords' == $method_name ) {
      $person_image_keyword = array(
        array(
          array(
            'person_image' => '/img/people/dabbott.jpg',
            'keyword' => 'Biodiversity and preservation of natural environment',
            ),
          array(
            'person_image' => '/img/people/kabbott.jpg',
            'keyword' => 'Biodiversity and preservation of natural environment',
          ),
        ),
        array(
          array(
            'person_image' => '/img/people/hdavulcu.jpg',
            'keyword' => 'Future and systems thinking',
          ),
          array(
            'person_image' => '/img/people/pdeviche.jpg',
            'keyword' => 'Future and systems thinking',
          ),
          array(
            'person_image' => '/img/people/tday.jpg',
            'keyword' => 'Future and systems thinking',
          ),
        ),
        array(
          array(
            'person_image' => '/img/people/rdiaz.jpg',
            'keyword' => 'Social and behavioral change, ethics',
          ),
        ),
      );
      return $person_image_keyword ;
    } else if ( 'get_person_image' == $method_name ) {
      return '/img/people/aadhikari.jpg';
    }
    return null;
  }
}