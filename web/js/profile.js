/*global $: true, APP: true */

var APP = (function (app, $) {
    if (app.profile) {
        return;
    }

    app.profile = (function () {
        var $communityBoards,
        $brandBoards,
        boardsInitialized,
        boardsIFollowInitialized;

        function initCollectionsIFollow() {
            $collections = $("#collectionsIFollowContainer .boardsList");
            if ($collections.length > 0 ) { //&& !boardsIFollowInitialized

                $("#boardsLoadMore").hide();

                $collections.infinitescroll({
                    navSelector  : "#boardsLoadMore",
                    nextSelector : "#boardsLoadMore a",
                    itemSelector : ".boardsList > li",
                    loading: {
                        msgText : 'Fetching more collections...',
                        finishedMsg: 'No more collections to load.',
                        img: "/images/stream/loading.gif"
                    }
                }, function() {
                    if ($('#content').width() == 300) {
                        truncateBoardAuthor(25, 23);
                    } else if($('#content').width() == 720) {
                        truncateBoardAuthor(18, 16);
                    } else if($('#content').width() == 960) {
                        truncateBoardAuthor(18, 16);
                    } else if($('#content').width() == 1200) {
                        truncateBoardAuthor(20, 18);
                    }
                });

                boardsIFollowInitialized = true;
            }
        }

        function initTabLinks() {
            $('#userTabs a').hover(function (e) {
                var $this, li, id, div;

                $this = $(this);
                li = $(this).parent();

                if (li.hasClass('current')) {
                    $this.unbind('hover');
                    return;
                }

                id = $this.attr('class');

                div = $('#' + id);
                if (!div.length) {
                    div = $('<div />')
                    .attr('id', id)
                    .appendTo('#userProfileContent')
                    .hide()
                    .load($this.attr('href') + ($this.attr('href').indexOf('?') != -1 ? "&isAjax=1" : "?isAjax=1") , function() {
                        if (id === 'mySparks' || id === 'onSale') {
                            yepnope({
                                test: false, // test if these js files are already loaded - they won't be if we're here
                                nope: [
                                '/js/jquery.sqbricks.js',
                                '/js/libs/webfont.js',
                                '/js/libs/jquery.jail.js',
                                '/js/libs/jquery.infinitescroll.js',
                                '/js/stream.js'
                                ],
                                complete: function() {
                                    div.smartresize();
                                }
                            })
                        }
                        if(id === 'peopleIFollow') {
                            APP.follow.init();
                        }
                        if(id === 'collectionsIFollow') {
                            initCollectionsIFollow();
                        }
                    });
                }
            });

            var pageTitle = document.title;
            $('#userTabs a').click(function (e) {
                e.preventDefault();
                var $this = $(this),
                    li = $(this).parent(),
                    id, div, state;

                if (li.hasClass('current')) {
                    return false;
                }
                li.addClass('current').siblings().removeClass('current');
                id = $this.attr('class');
                state = History.getState();
                History.pushState(state, pageTitle + " - " + $this.text(), $this.attr('href'));

                div = $('#' + id);
                div.siblings('div').css('display', 'none').end().css('display', 'block');
                if (id === 'mySparks' || id === 'onSale') {
                    div.smartresize();
                    // Vertically center for promos cover
                    $('#stream').find('.postPromoContent').css('top', function() {
                        return ($(this).parent().height()/2) - $(this).height()/2;
                    });
                    // Vertically center for post actions
                    $('#stream').find('.postActions').css('top', function() {
                        return ($(this).parent().height()/2) - $(this).height()/2;
                    });
                    // Render share buttons
                    FB.XFBML.parse();
                    twttr.widgets.load();
                    // Tooltips
                    $('#stream').find(".postActions a[title]").tooltips();
                }
            });
			
            //delegate handler for subtabs in main tab container
            $(document).delegate('.subTabs a', 'click', function(e){
                e.preventDefault();
                var $this = $(this);
                state = History.getState();
                History.pushState(state, pageTitle + " - " + $this.text(), $this.attr('href'));
                $('#userProfileContent .subTabs').parent().load($this.attr('href')  + ($this.attr('href').indexOf('?') != -1 ? "&isAjax=1" : "?isAjax=1") , function(){
                    initCollectionsIFollow();
                });
            });
        }

        function initCarousels() {
            $('#userFriends .jcarousel').jcarousel({
                list  : ">ul:eq(0)",
                items : ">li"
            });
      
            $('#userFriends .jcarousel-next').jcarouselControl({
                target: '+=2'
            });
      
            $('#userFriends .jcarousel-prev').jcarouselControl({
                target: '-=2'
            });
      
            $('#userActivity .jcarousel').jcarousel({
                list      : ">ul:eq(0)",
                items     : ">li",
                vertical  : true
            });
      
            $('#userActivity .jcarousel-next').jcarouselControl({
                target: '+=2'
            });
      
            $('#userActivity .jcarousel-prev').jcarouselControl({
                target: '-=2'
            });
        }
        
        var $followersModal = $("#followersList");
        var $followingModal = $("#followingList");
    
        function _getModalWidth(width) {
            var modalWidth = 300;
            if ($('#content').width() > 300) {
                modalWidth = width;
            }
            return modalWidth;
        }
    
        function setupFollowersModal() {
            $followersModal.dialog({
                closeOnEscape: true,
                autoOpen: false,
                modal: true,
                dialogClass: "modalWindow followersModal",
                width: _getModalWidth(720),
                closeText: 'X',
                draggable: false,
                resizable: false,
                open: function(event, ui) {
                    _gaq.push(['_trackEvent', 'Dialog', 'Show', 'User Followers']);
                },
                close: function(event, ui) {
                    _gaq.push(['_trackEvent', 'Dialog', 'Hide', 'User Followers']);
                }
            });
        }
        
        function setupFollowingModal() {
            $followingModal.dialog({
                closeOnEscape: true,
                autoOpen: false,
                modal: true,
                dialogClass: "modalWindow followingModal",
                width: _getModalWidth(720),
                closeText: 'X',
                draggable: false,
                resizable: false,
                open: function(event, ui) {
                    _gaq.push(['_trackEvent', 'Dialog', 'Show', 'User Followers']);
                },
                close: function(event, ui) {
                    _gaq.push(['_trackEvent', 'Dialog', 'Hide', 'User Followers']);
                }
            });
        }

        return {
            init: function () {
                initTabLinks();
                initCarousels();
                initCollectionsIFollow();
                setupFollowersModal();
                setupFollowingModal();
                
                $("#showFollowers").click(function(e) {
                    e.preventDefault();
                    $followersModal.dialog("open");
                });
                
                $("#showFollowing").click(function(e) {
                    e.preventDefault();
                    $followingModal.dialog("open");
                });
            }
        };
    }());

    return app;
}(APP || {}, $));

$(function () {
    APP.profile.init();

    // Truncate board name
    if($('#content').width() == 300) {
        truncateBoardName(65, 63);
        truncateBoardAuthor(25, 23);
    }

    $(window).on('enterBreakpoint720',function() {
        truncateBoardName(56, 54);
        truncateBoardAuthor(18, 16);
    });

    $(window).on('enterBreakpoint960',function() {
        truncateBoardName(55, 53);
        truncateBoardAuthor(18, 16);
    });

    $(window).on('enterBreakpoint1200',function() {
        truncateBoardName(55, 53);
        truncateBoardAuthor(20, 18);
    });
});
