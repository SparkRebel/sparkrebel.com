function StreamCounter () {
    var self = this;
    this.count = 0;
    jQuery.extend(this, { 
      
        initialize: function() {
            $(document).on('click', '#more-sparks-link', function(e){
                _gaq.push(['_trackEvent', 'More Sparks Signup', 'Clicked']);
                e.preventDefault();
                APP.login.start();
            });
        },
		
        incrementViews: function() {
            self.count += 1;
        },
        
        maximum: function() {
            return APP.config.max_sparks_for_non_logged;
        }
          
    });  
}
var stream_counter = new StreamCounter;
stream_counter.initialize();


WebFont.load({
  custom: {
    families: ['MuseoSans500', 'MuseoSans900']
//    urls: ['/css/typography.css']
  },
  active: function() {
    var lazyOptions = {
  	    loadHiddenImages : true,
  	    triggerElement: '#stream',
  	    event: 'scroll',
  	    offset : 300,
  	    callbackAfterEachImage : function(i){
  	        i.css('opacity', 1);
  	    }
  	};
  	
    var $stream = $('#stream');
    
    $("#stream img.lazy").jail(lazyOptions);

    $stream.sqBricks({itemSelector : '.post', callback: function(){
            // Vertically center for promos cover
            $stream.find('.postPromoContent').css('top', function() {
                return ($(this).parent().height()/2) - $(this).height()/2;
            });
            // Vertically center for post actions
            $stream.find('.postActions').css('top', function() {
                return ($(this).parent().height()/2) - $(this).height()/2;
            });
            $stream.css('opacity', 1);
            // $stream.find('.pinned .postImage img').rwdImageMaps();
            // Tooltips
            $(".postActionsButtons a[title]").tooltips();
        }
    });

    $("#nextPosts").css('margin-top', '-150px').css('margin-bottom', '150px').hide();
    
    $.infinitescroll.prototype.scrollPartTwo = $.infinitescroll.prototype.scroll;
    $.infinitescroll.prototype.scroll = function(){
        if (this.element.is(":visible")) {
            this.scrollPartTwo();
        } else {
            return;
        }
    };
    
    $stream.infinitescroll({
        navSelector  : "#nextPosts",
        nextSelector : "#nextPosts a",
        itemSelector : ".post",
        loading: {
            msgText : 'Fetching more sparks...',
            finishedMsg: 'No more sparks to load.',
            img: "/images/stream/loading.gif"
        },        
        pathParse: function(path, pageNumber) {
            return [path];
        }
    }, function(newElements) {
        // hide new items while they are loading
        var $newElems = $(newElements);
        $("#stream img.lazy").jail(lazyOptions);
        $stream.sqBricks('appended', $newElems, function() {
            // Vertically center for promos cover
            $newElems.find('.postPromoContent').css('top', function() {
                return ($(this).parent().height()/2) - $(this).height()/2;
            });
            // Vertically center for post actions
            $newElems.find('.postActions').css('top', function() {
                return ($(this).parent().height()/2) - $(this).height()/2;
            });
            // Render share buttons
            FB.XFBML.parse();
            twttr.widgets.load();
            // $stream.find('.pinned .postImage img').rwdImageMaps();
            // Tooltips
            $(".postActions a[title]").tooltips();
        });
        
          	    
  	    stream_counter.incrementViews();
    });
    
  }
});