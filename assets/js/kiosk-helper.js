/*! kisok v1.0 */
function scrollTweets() {
     $('#kiosk_tweets_list').animate({        
        top: '-220px'
    }, 8000, function () {
        $(this).animate({
            top : '0'
        }, 0);
        scrollTweets();
    });
}
scrollTweets();
