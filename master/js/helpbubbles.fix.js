var delete_cookie = function (name) {
    document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
};


$('#main-help-link').click(function () {
    // show help bubbles from beginning again
    delete_cookie("helpbubblesseen");
    helpbubbles.doBubbles();
});


