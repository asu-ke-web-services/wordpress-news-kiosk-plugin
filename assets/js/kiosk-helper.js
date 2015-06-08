/*! kisok v1.0 
 */
/**
 * Invokes itself with 5 seconds interval and updates the time
 *
 */
function updateKioskTime(time_selector, cal_selector_mon, cal_selector_date) {
  var date = new Date();
  var hours = date.getHours();
  var minutes = date.getMinutes();
  var date_string = date.toString();
  var month = date_string.substring(4, 7);
  var current_date = date_string.substring(8, 10);
  var ampm = hours >= 12 ? 'PM' : 'AM';
  hours = hours % 12;
  hours = hours ? hours : 12; // the hour '0' should be '12'
  hours = hours < 10 ? '0' + hours : hours;
  minutes = minutes < 10 ? '0' + minutes : minutes;
  var strTime = hours + ':' + minutes + ' ' + ampm;
  $(time_selector).html(strTime);
  $(cal_selector_mon).html(month);
  $(cal_selector_date).html(current_date);
  setTimeout(function() {
    updateKioskTime(time_selector, cal_selector_mon, cal_selector_date);
  }, 5000 /* 5 seconds */ );
}

/**
 * To update tweet time relative to current time every 10 seconds
 * For each tweet read the actual time got from server and update the tweet time
 * relative to the current time every 10 seconds interval
 */
function setRelativeTweetTime(old_time_selector, actual_time_selector) {
  var $formated_time = $(old_time_selector);
  $(actual_time_selector).each(function(index, time_tag) {
    var old_time = $formated_time.eq(index).html();
    if (old_time.length <= 3) {
      var actual_time = $(time_tag).html();
      var relative_time = calculateRelativeTime(actual_time);
      $formated_time.eq(index).html(relative_time);
    }
  });
  setTimeout(function() {
    setRelativeTweetTime(old_time_selector, actual_time_selector);
  }, 10000 /* 10 seconds */ );
}

/**
 * calculateRelativeTime(tweet_time)
 * tweet_time is Unix timestamp (the number of seconds since January 1 1970 00:00:00 GMT).
 * If less than 1 second show as now
 * If less than minute display as seconds ago
 * If less than hours display as minutes ago
 * If less than 24 hours display as hours ago
 * Otherwise return in days. This is duplicate functionality of php to update relative time
 * @see Kiosk_WP::Twitter_Api_Helper::time_short_form
 */

function calculateRelativeTime(tweet_time) {
  var time = Math.round(+new Date() / 1000);
  var elapsed_time = time - tweet_time;
  var temp = '0h';
  if (elapsed_time < 1) {
    return 'now';
  }
  var time_conversion = [];
  time_conversion.push({
    secs: 1,
    unit: 's'
  });
  time_conversion.push({
    secs: 60,
    unit: 'm'
  });
  time_conversion.push({
    secs: 60 * 60,
    unit: 'h'
  });
  time_conversion.push({
    secs: 24 * 60 * 60,
    unit: 'd'
  });

  for (var i = 0; i < time_conversion.length; i++) {
    var ratio_elapsed_to_unit = elapsed_time / time_conversion[i].secs;
    var rounded_time = Math.round(ratio_elapsed_to_unit);
    temp = rounded_time + time_conversion[i].unit;
    var not_a_fractional_unit = ratio_elapsed_to_unit >= 1;
    var less_than_one_day = time_conversion[i].unit == 'h' && ratio_elapsed_to_unit < 24;
    var less_than_one_hour = time_conversion[i].unit == 'm' && ratio_elapsed_to_unit < 60;
    var less_than_one_minute = time_conversion[i].unit == 's' && ratio_elapsed_to_unit < 60;
    if (not_a_fractional_unit && (less_than_one_day || less_than_one_hour || less_than_one_minute)) {
      return temp;
    }
  }
  return temp;
}

setRelativeTweetTime('.kiosk-tweets__tweet__details__tweet-time', '.kiosk-tweets__tweet__details__actual-tweet-time');
updateKioskTime('#kiosk_display_time', '.kiosk-date-time__calendar-icon strong', '.kiosk-date-time__calendar-icon span');

/**
 * set carousel effect to 10 seconds
 */
$('.carousel').carousel({
  interval: 10000
});

/**
 * Invokes ajax call to server every 5 minutes and
 * replaces tweets block and weather block
 *
 */
var site_url = $(location).attr("href");
var tweets_limit = 20;
var tweets_url = site_url + 'kiosk/twitter/limit/' + tweets_limit;
var weather_url = site_url + 'kiosk/weather';
setInterval(function() {
  $.ajax({
    url: tweets_url,
    success: function(response) {
      var $response_tweets = $(".kiosk-tweets", response);
      if ($response_tweets.length) {
        $(".kiosk-tweets").replaceWith($response_tweets);
      }
    }
  });

  $.ajax({
    url: weather_url,
    success: function(response) {
      var $response_weather = $(".kiosk-weather", response);
      if ($response_weather.length) {
        $(".kiosk-weather").replaceWith($response_weather);
      }
    }
  });
}, 300000 /* 5 minutes */ );

/**
 * every 10 seconds, move
 * the first tweet up. After animation, reset list,
 * and move first tweet to the end of the list
 */
var $tweet_list = $('.kiosk-tweets__list');
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
 * reloads page every day
 */
setTimeout(function() {
  location.reload()
}, 24 * 60 * 60 * 1000 /* 24 hours */ );

/**
 * Hide page scroll bars when current page template is set to KIOSK
 *
 */
if ($('.kiosk-page-template').length) {
  $('html').css({
    'overflow-y': 'hidden',
    'overflow-x': 'hidden'
  });
}
