/*! kisok v1.0
 */
+function ($) {
/**
 * Invokes itself with 5 seconds interval and updates Kiosk widget date and time
 *
 */
+function updateKioskTime(time_selector, cal_month_selector, cal_date_selector) {
  var date         = new Date();
  var hours        = date.getHours();
  var minutes      = date.getMinutes();
  var date_string  = date.toString();
  var month        = date_string.substring(4, 7);
  var current_date = date_string.substring(8, 10);
  var ampm         = hours >= 12 ? 'PM' : 'AM';
  hours            = hours % 12;
  hours            = hours ? hours : 12; // the hour '0' should be '12'
  hours            = hours < 10 ? '0' + hours : hours;
  minutes          = minutes < 10 ? '0' + minutes : minutes;
  var strTime      = hours + ':' + minutes + ' ' + ampm;
  $(time_selector).html(strTime);
  $(cal_month_selector).html(month);
  $(cal_date_selector).html(current_date);
  setTimeout(function() {
    updateKioskTime(time_selector, cal_month_selector, cal_date_selector);
  }, 5000 /* 5 seconds */ );
}('#kiosk_display_time', '.kiosk-date-time__calendar-icon strong', '.kiosk-date-time__calendar-icon span')

/**
 * To update tweet time relative to current time every 10 seconds
 * For each tweet read the actual time got from server and update the tweet time
 * relative to the current time
 */
+function setRelativeTweetTime(old_time_selector) {
  var $formated_time = $(old_time_selector);
  $formated_time.each(function(_, tweet_time) {
    var old_time   = $(tweet_time).html();
    // Ignore the case there the date is already formatted
    // as a month
    if (old_time.length <= 3) {
      var actual_time   = $(tweet_time).data('actualTime');
      $(tweet_time).html(calculateRelativeTime(actual_time));
    }
  });
  setTimeout(function() {
    setRelativeTweetTime(old_time_selector);
  }, 10000 /* 10 seconds */ );
}('.kiosk-tweets__tweet__details__tweet-time');

/**
 * If less than 1 second show as now
 * If less than minute display as seconds ago
 * If less than hours display as minutes ago
 * If less than 24 hours display as hours ago
 * Otherwise return in days. This is duplicate functionality of php to update relative time
 * @param tweet_time is Unix timestamp (the number of seconds since January 1 1970 00:00:00 GMT).
 * @see Kiosk_WP::Twitter_Api_Helper::time_short_form
 */
function calculateRelativeTime(tweet_time) {
  var time         = Math.round(+new Date() / 1000);
  var elapsed_time = time - tweet_time;
  var temp         = '0h';
  if (elapsed_time < 1) {
    return 'now';
  }
  var time_conversion = [{
    secs: 1,
    unit: 's'
  }, {
    secs: 60,
    unit: 'm'
  }, {
    secs: 60 * 60,
    unit: 'h'
  }, {
    secs: 24 * 60 * 60,
    unit: 'd'
  }];

  for (var i = 0; i < time_conversion.length; i++) {
    var ratio_elapsed_to_unit = elapsed_time / time_conversion[i].secs;
    var rounded_time          = Math.round(ratio_elapsed_to_unit);
    temp                      = rounded_time + time_conversion[i].unit;
    var not_a_fractional_unit = ratio_elapsed_to_unit >= 1;
    var less_than_one_day     = time_conversion[i].unit == 'h' && ratio_elapsed_to_unit < 24;
    var less_than_one_hour    = time_conversion[i].unit == 'm' && ratio_elapsed_to_unit < 60;
    var less_than_one_minute  = time_conversion[i].unit == 's' && ratio_elapsed_to_unit < 60;
    if (not_a_fractional_unit && (less_than_one_day || less_than_one_hour || less_than_one_minute)) {
      return temp;
    }
  }
  return temp;
}

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
function resizePeopleSlider() {
  var logoHeight   = $('.kiosk-third-floor__logo').outerHeight(true);
  var peopleSliderheaderHeight = $('.kiosk-people-slider__layout__header').outerHeight(true);
  var remainHeight = peopleSliderheaderHeight + logoHeight + 30;
  $('.kiosk-people-slider__aspect-ratio').css('height', 'calc( 100vh - ' + remainHeight + 'px)');
  if ($(window).width() < $(window).height()) {
      $('.kiosk-people-slider__aspect-ratio').css('height', $(window).width() * 5 / 7);
  }
}

/**
 * set carousel effect to 10 seconds
 */
$('.carousel').carousel({
  interval: 10000
});

/**
 * Invokes ajax call to server every 5 minutes and
 * replaces tweets block and weather block
 * Updates tweets and weather widgets only when servers returns valid data
 */
var site_url          = $(location).attr("href");
var tweets_limit      = 20;
var $weather_location = $.trim($('.kiosk-weather__forecast__title').text());
var tweets_url        = site_url + 'kiosk/twitter/limit/' + tweets_limit;
var weather_url       = site_url + 'kiosk/weather/location='+ $weather_location;
setInterval(function() {
  $.ajax({
    url: tweets_url,
    success: function(response) {
      if(response.length){
        $(".kiosk-tweets").replaceWith(response);
      }
    }
  });

  $.ajax({
    url: weather_url,
    success: function(response) {
       if(response.length){
        $(".kiosk-weather").replaceWith(response);
      }
    }
  });
}, 300000 /* 5 minutes */ );

/**
 * every 10 seconds, move
 * the first tweet up. After animation, reset list,
 * and move first tweet to the end of the list
 */
var $tweet_list = $('#kiosk-tweets__tweets');
setInterval(function tweetAnimate() {
  var $first_tweet = $tweet_list.find('li:first');
  $tweet_list.find('li').animate({
    top: (-1 * $first_tweet.outerHeight(true)) + 'px' // calculate height of first element
  }, 2000, function() {
    $first_tweet.appendTo($tweet_list);
    $first_tweet.show();
    $tweet_list.find('li').css({
      'top': '0px'
    });
  });
}, 10000 /* 10 seconds */ );

/**
 * reloads page every night between 12 a.m to 1 a.m
 */
setTimeout(function() {
  var hour = new Date().getHours();
  if( 0 <= hour < 1 ) {
    location.reload();
  }
}, 1 * 60 * 60 * 1000 /* 1 hour */ );

/**
 * Hide page scroll bars when current page template is set to Kiosk
 *
 */
if ($('.kiosk-page-template').length) {
  $('html').css({
    'overflow-y': 'hidden',
    'overflow-x': 'hidden'
  });
}

$(window).resize(function(){
resizePeopleSlider();
});
resizePeopleSlider();
}(jQuery);
