if($('#content').width() == 300) {
    truncateBoardName(58, 56);
    truncateBoardAuthor(20, 18);
    // New order for mobile version
    $('.itemBoards').appendTo('#content');
    
    // Vertical center for "Promos" overlay
    $('.itemPicture').find('.postPromoContent').css('top', function() {
        return ($(this).parent().height()/2) - $(this).height()/2;
    }).show();
};

$(window).on('enterBreakpoint720',function() {
    truncateBoardName(58, 56);
    truncateBoardAuthor(20, 18);
    
    // Vertical center for "Promos" overlay
    $('.itemPicture').find('.postPromoContent').css('top', function() {
        return ($(this).parent().height()/2) - $(this).height()/2;
    }).show();
});

$(window).on('enterBreakpoint960',function() {
    truncateBoardName(58, 56);
    truncateBoardAuthor(20, 18);
    
    // Vertical center for "Promos" overlay
    $('.itemPicture').find('.postPromoContent').css('top', function() {
        return ($(this).parent().height()/2) - $(this).height()/2;
    }).show();
});

$(window).on('enterBreakpoint1200',function() {
    truncateBoardName(58, 56);
    truncateBoardAuthor(20, 18);
    
    // Vertical center for "Promos" overlay
    $('.itemPicture').find('.postPromoContent').css('top', function() {
        return ($(this).parent().height()/2) - $(this).height()/2;
    }).show();
});

$(document).ready(function() {
    $copyrightWidth = $(".itemPictureImage img").width();
    if ($copyrightWidth > 0) {
        $(".itemPictureCopyright").width($copyrightWidth);
    } else {
        var $copyrightInterval;
        $copyrightInterval = setInterval(function() {
            $copyrightWidth = $(".itemPictureImage img").width();
            if ($copyrightWidth > 0) {
                $(".itemPictureCopyright").width($copyrightWidth);
                clearInterval($copyrightInterval);
            }
        }, 500);
    }
    
    // Full Screen
    if($.support.fullscreen){
        $(".fullscreen").show();
        $(".itemPictureImage img").addClass("fullscreen");
        
        $(".fullscreen").click(function(e){
            $('.itemPictureFullscreen').fullScreen({
                'background'    : '#000',
                'callback'      : function(isFullScreen) {
                    if (isFullScreen) {
                        var windowHeight = $(window).height();
                        var windowWidth = $(window).width();
                        
                        function imagePosition($image) {
                            if (typeof $image.attr("style") === "undefined") {
                                if ($image.width() * 2 > windowWidth || $image.height() * 2 > windowHeight) {
                                    if ((windowWidth / windowHeight) > ($image.width() / $image.height())) {
                                      $image.css({
                                        "height" : windowHeight + "px",
                                        "width"  : "auto"
                                      });
                                    } else {
                                      $image.css({
                                        "height" : "auto",
                                        "width"  : windowWidth + "px"
                                      });
                                    }
                                } else {
                                    if ($image.width() < $image.height()) {
                                      $image.css({
                                        "height" : $image.height() * 2 + "px",
                                        "width"  : "auto"
                                      });
                                    } else {
                                      $image.css({
                                        "height" : "auto",
                                        "width"  : $image.width() * 2 + "px"
                                      });
                                    }
                                }
                                
                                // Update margins to position in the center
                                $image.css({
                                  "margin-left" : "-" + (0.5 * $image.width()) + "px",
                                  "margin-top"  : "-" + (0.5 * $image.height()) + "px"
                                });
                            }
                        }
                        
                        function loadImage($image) {
                            var img = new Image();
                            $(img)
                                .load(function () {
                                    $(this).hide();
                                    $image.attr("src", $image.data("src"));
                                    $(".itemPictureFullscreen .loading").fadeOut("fast");
                                    imagePosition($image);
                                    $(this).fadeIn("fast");
                                })
                                .attr("src", $image.data("src"));
                        }
                        
                        $(".itemPictureFullscreen").show();
                        
                        $(".itemPictureFullscreenImages li").removeClass("current");
                        $(".itemPictureFullscreenImages li:first").addClass("current");
                        
                        $firstImage = $(".itemPictureFullscreenImages li:first img");
                        loadImage($firstImage);
                        
                        if ($(".itemPictureFullscreenImages li").length <= 1) {
                            $(".itemPictureFullscreenNext, .itemPictureFullscreenPrev").hide();
                        }
                        
                        $(".itemPictureFullscreenNext").click(function() {
                            $currentImage = $(".itemPictureFullscreenImages .current");
                            $nextImage = $(".itemPictureFullscreenImages .current").next();
                            if ($nextImage.length == 0) {
                                $nextImage = $(".itemPictureFullscreenImages li:first");
                            }
                            $(".itemPictureFullscreenImages li").removeClass("current");
                            $nextImage.addClass("current");
                            $(".itemPictureFullscreen .loading").fadeIn("fast");
                            loadImage($nextImage.find("img"));
                        });
                        
                        $(".itemPictureFullscreenPrev").click(function() {
                            $currentImage = $(".itemPictureFullscreenImages .current");
                            $prevImage = $(".itemPictureFullscreenImages .current").prev();
                            if ($prevImage.length == 0) {
                                $prevImage = $(".itemPictureFullscreenImages li:last");
                            }
                            $(".itemPictureFullscreenImages li").removeClass("current");
                            $prevImage.addClass("current");
                            $(".itemPictureFullscreen .loading").fadeIn("fast");
                            loadImage($prevImage.find("img"));
                        });
                    } else {
                        $(".itemPictureFullscreen").hide();
                    }
                    
                    return;
                }
            });
        });
    }
});