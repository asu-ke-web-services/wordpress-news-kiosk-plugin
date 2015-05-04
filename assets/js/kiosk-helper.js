/*! kisok v1.0 */
function scrollTweets() {
var n = 0;
var size=$('.kiosk_tweets_item').size();
(function rotatetweets(jq) {
     jq.eq(n).fadeIn(2000).delay(2000).fadeOut(2000, function () {
        n++;
        if (n == 10) {
            n = 0;
            resetfaded(jq);           
        };
        rotatetweets(jq);
    }); 

})($('.kiosk_tweets_item'));
}
//resetfaded();
scrollTweets();

function resetfaded(jq){
    jq.each(function(){$(this).fadeIn(0)});
}
