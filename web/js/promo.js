if($('#content').width() == 300) {
    // Vertical center for "Promos" overlay
    $('.itemPicture').find('.postPromoContent').css('top', function() {
        return ($(this).parent().height()/2) - $(this).height()/2;
    }).show();
};

$(window).on('enterBreakpoint720',function() {
    // Vertical center for "Promos" overlay
    $('.itemPicture').find('.postPromoContent').css('top', function() {
        return ($(this).parent().height()/2) - $(this).height()/2;
    }).show();
});

$(window).on('enterBreakpoint960',function() {
    // Vertical center for "Promos" overlay
    $('.itemPicture').find('.postPromoContent').css('top', function() {
        return ($(this).parent().height()/2) - $(this).height()/2;
    }).show();
});

$(window).on('enterBreakpoint1200',function() {
    // Vertical center for "Promos" overlay
    $('.itemPicture').find('.postPromoContent').css('top', function() {
        return ($(this).parent().height()/2) - $(this).height()/2;
    }).show();
});