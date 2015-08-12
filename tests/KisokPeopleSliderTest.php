<?php

class KioskPeopleSliderTest extends WP_UnitTestCase {
  private $People_Slider_Handler_stub = null;
  private $stub                      = null;
  // @codingStandardsIgnoreStart
  static function setUpBeforeClass() {
    WP_UnitTestCase::setUpBeforeClass();

  }
  function setUp() {
    $this->People_Slider_Handler_stub = $this->getMock(
        'Kiosk_WP\People_Slider_Handler',
        array(
            'get_keywords',
            'get_people',
        )
    );

    $this->People_Slider_Handler_stub->expects( $this->any() )
                              ->method( 'get_keywords' )
                              ->will(
                                  $this->returnValue(
                                      $this->return_unit_test_data(
                                          'get_keywords'
                                      )
                                  )
                              );

    $this->People_Slider_Handler_stub->expects( $this->any() )
                              ->method( 'get_people' )
                              ->will(
                                  $this->returnValue(
                                      $this->return_unit_test_data(
                                          'get_people'
                                      )
                                  )
                              );

    $this->stub = $this
          ->getMockBuilder('Kiosk_WP\Kiosk_People_Slider_Shortcodes' )
          ->setConstructorArgs( array( $this->People_Slider_Handler_stub ) )
          ->setMethods( null )
          ->getMock();

  }
  // @codingStandardsIgnoreEnd

  /**
   * To Test Kiosk people slider can be called with limit attribute
   * [kiosk-people-slider gios_url ='url']
   */
  function test_kiosk_people_slider_shortcode() {
    $this->assertTrue( shortcode_exists( 'kiosk-people-slider' ) );
    $content = $this->stub->kiosk_people_slider( '' );
    $this->assertContains(
        'kiosk-people-slider__layout',
        $content,
        'Should return carousel slider'
    );

    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertEquals(
        4,
        $numberOfEvents,
        'There should 4 slider items'
    );
  }

  function test_kiosk_people_slider_shortcode_with_gios_url() {
    $this->assertTrue( shortcode_exists( 'kiosk-people-slider' ) );
    $content = $this->stub->kiosk_people_slider(
        array( 'gios_url' => 'http:/example.asu.edu' )
    );
    $this->assertContains(
        'example.asu.edu',
        $content,
        'Should contain prefix given from gios_url'
    );

    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertEquals(
        4,
        $numberOfEvents,
        'There should 4 slider items'
    );
  }
  function test_kiosk_people_slider_shortcode_with_content() {
    $this->assertTrue( shortcode_exists( 'kiosk-people-slider' ) );
    $test_with_content = <<<HTML
    [
  {
    "keyword":"Biodiversity and preservation of natural environment",
    "person-slug":"jeffrey-englin",
    "quote":"Live for today Hope... for tomorrow&copy;"
   },
 {
    "keyword":"Energy",
    "person-slug":"jeffrey-englin"

   },
 {
    "keyword":"food systems",

    "quote":"Live for today Hope... for tomorrow&copy;"
   }

]
HTML;
    $content = $this->stub->kiosk_people_slider( array(), $test_with_content );
    $this->assertContains(
        'kiosk-people-slider__layout',
        $content,
        'Should return carousel slider'
    );
    $numberOfEvents = substr_count( $content, '<li' );
    $this->assertEquals(
        4,
        $numberOfEvents,
        'There should 4 slider items'
    );
    $this->assertContains(
        'gdawson',
        $content,
        'Should return content person-slug image'
    );
    $this->assertContains(
        'Live for today Hope... for tomorrow',
        $content,
        'Should return content quote'
    );

  }

  /**
    * Creates a mock up data
    * @return mixed
    */
  function return_unit_test_data( $method_name ) {
    $person                 = new Person();
    $keyword                = new Keyword();
    if ( 'get_keywords' == $method_name ) {
      return array( $keyword, $keyword, $keyword, $keyword );
    } else if ( 'get_people' == $method_name ) {
      return array( $person, $person, $person, $person );
    }
    return null;
  }
}
/**
 * Mocking up Person and Keyword class instead of depending on GIOS_API
 * for unit test
 */
class Person {
  public $slug           = 'gregory-dawson';
  public $person_id      = '15998';
  public $person_values  = array( 'photo_path' => '/people/gdawson.jpg' );
  public function photo_url() {
    return $this->person_values['photo_path'];
  }
}

class Keyword {
  public $keyword = 'Biodiversity and preservation of natural environment';
}