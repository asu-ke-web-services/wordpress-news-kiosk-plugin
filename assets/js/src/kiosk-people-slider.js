/*kisok-people-slider
 */
+function ($) {
  'use strict';
  /**
   * Resizes People Slider by calculating space occupied by logo and People slider
   * header and the padding of 1st column so the height and width accomdate to
   * aspect ratio of width: height :: 7:5
   * If window width is smaller then height use width for calculating aspect ratio
   * as when resized to width of max 1200px each column occupies full width slider
   * layout can still fit in available height.
   * Note: Default setting height will take care of aspect ratio which is handled
   * in CSS when width > height for other case calculate the ratio of width and
   * set height.
   */
  function resizePeopleSlider () {
    var logoHeight   = $('.kiosk-third-floor__logo').outerHeight(true);
    var peopleSliderheaderHeight = $('.kiosk-people-slider__layout__header').outerHeight(true);
    var remainHeight = peopleSliderheaderHeight + logoHeight + 30;
    $('.kiosk-people-slider__aspect-ratio').css('height', 'calc( 100vh - ' + remainHeight + 'px)');
    if ($(window).width() < $(window).height()) {
      $('.kiosk-people-slider__aspect-ratio').css('height', $(window).width() * 5 / 7);
    }
  }

  $(window).resize(function () {
    resizePeopleSlider();
  });
  resizePeopleSlider();

}(jQuery)
