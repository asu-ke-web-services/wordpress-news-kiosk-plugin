/*kisok-time
 */
+function ($) {
  'use strict';
  /**
   * Invokes itself with 5 seconds interval and updates Kiosk widget date and time
   *
   */
  +function updateKioskTime(timeSelector, calMonthSelector, calDateSelector) {
    var date         = new Date();
    var hours        = date.getHours();
    var minutes      = date.getMinutes();
    var dateString   = date.toString();
    var month        = dateString.substring(4, 7);
    var currentDate  = dateString.substring(8, 10);
    var ampm         = hours >= 12 ? 'PM' : 'AM';
    hours            = hours % 12;
    hours            = hours ? hours : 12; // the hour '0' should be '12'
    hours            = hours < 10 ? '0' + hours : hours;
    minutes          = minutes < 10 ? '0' + minutes : minutes;
    var strTime      = hours + ':' + minutes + ' ' + ampm;
    $(timeSelector).html(strTime);
    $(calMonthSelector).html(month);
    $(calDateSelector).html(currentDate);
    setTimeout(function () {
      updateKioskTime(timeSelector, calMonthSelector, calDateSelector);
    }, 5000 /* 5 seconds */ );
  }('#kiosk_display_time', '.kiosk-date-time__calendar-icon strong', '.kiosk-date-time__calendar-icon span')
}(jQuery)
