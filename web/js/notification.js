/*jslint browser: true */
/*global $: true, APP: true */

var APP = (function (parent, $) {
    if (parent.notifications) {
        return parent;
    }

    var bar = null,
        minimumWidthForBar = 481,
        friendsTs = null,
        notificationsTs = null,
        markedAsRead = {};

    /**
     * createNotificationBar
     */
    function createNotificationBar() {
        if ($('#footerBarContainer').length) {
            return;
        }
        
        if ($('#content').width() < minimumWidthForBar) {
            return;
        }

        // TODO move to templates
        bar = $('<section id="footerBarContainer"><div id="footerBar">\
                <div id="footerBarNotifications">\
                    <a id="footerBarNotificationsLink"><span>0</span>Notifications</a>\
                    <div id="footerNotificacionsDropDown"></div>\
                </div>\
                <div id="footerBarFriendsActivity">\
                    <a id="footerBarFriendsActivityLink"><span>0</span>Friends\' Activity</a>\
                    <div id="footerFriendsActivityDropDown"></div>\
                </div>\
                <div id="footerBarAllFriendsDropDown">\
                    <h4><a id="showAllFriendsLink">All friends</a></h4>\
                    <ul></ul>\
                </div>\
                <div id="footerBarMyCollections">\
                    <a href="' + APP.config.base_url + '/member/collections"><span></span>My Collections</a>\
                </div>\
           </div></section>\
        ').appendTo("body");

        $(window).scroll(function(){
            initScroll();
        });
        
        //notification indicator
        $(document).delegate('#footerNotificacionsList li:not(.seeAll) a, #footerFriendsActivityList li:not(.seeAll) a', 'click', function(e){
            $('#footerNotificacionsList .notification-indicator, #footerFriendsActivityList .notification-indicator').remove(); //remove indicator and then append it to newest clicked
            var indicator_template = "<span class='notification-indicator'><img src='/images/ajax-white-pink.gif' /></span>";
            var $parent = $($(this).parents("li")[0]);
            $parent.append(indicator_template);
            setTimeout(function() {
                $parent.find('.notification-indicator').remove();
            }, 3000);
        });
        
        $(document).mousemove(function(event) {
            if (isNear($('#footerBarContainer'), 20, event)) {
                showBottomBar();
            } else {
                if ($('#footerBarNotifications').hasClass('open') === false && $('#footerBarFriendsActivity').hasClass('open') === false && $(window).scrollTop() > 10) {
                    hideBottomBar();
                }
            };
        });           
        
        $("#showAllFriendsLink")
            .attr('href', '#')
            .click(function (e) {
                e.preventDefault();
                getFriend(null);
                $('#footerBarAllFriendsDropDown .current').removeClass('current');
            });

        $('#footerBarNotificationsLink').click(function(e) {
            e.preventDefault();
            if ($(this).parents('.bubbles').length) {
                $("#footerBarContainer").removeClass('bubbles');
                $("#footerBarContainer").stop().animate({
                    bottom: '0'
                });
            } else {
                if ($('#footerBarNotifications').hasClass('open') === false) {
                    $('#footerNotificacionsDropDown').slideDown('fast', function() {
                        $(this).parent().addClass('open');
                        $('#footerNotificacionsDropDown').on('clickoutside', function(e){
                            $(this).slideUp('fast', function() {
                                $(this).parent().removeClass('open');
                                $('#footerNotificacionsDropDown').off('clickoutside');
                            });
                        });

                        markAsRead('notifications');
                    });
                } else {
                    $('#footerNotificacionsDropDown').slideUp('fast');
                }
            }
        });

        $('#footerBarFriendsActivityLink').click(function(e) {
            e.preventDefault();
            if ($('#footerBarFriendsActivity').hasClass('open') === false) {
                $('#footerBarAllFriendsDropDown').slideDown('fast');
                $('#footerFriendsActivityDropDown').slideDown('fast', function() {
                    $(this).parent().addClass('open');
                    $('#footerFriendsActivityDropDown').on('clickoutside', function(e){
                        if ($(e.target).attr('id') == 'footerBarAllFriendsDropDown' || $(e.target).parents('#footerBarAllFriendsDropDown').length ) {
                            return false;
                        }
                        $('#footerBarAllFriendsDropDown').slideUp('fast');
                        $(this).slideUp('fast', function() {
                            $(this).parent().removeClass('open');
                            $('#footerFriendsActivityDropDown').off('clickoutside');
                        });
                    });

                    markAsRead('friends');
                });
            } else {
                $('#footerBarAllFriendsDropDown').slideUp('fast');
                $('#footerFriendsActivityDropDown').slideUp('fast');
            }
        });

        $('#footerBarNotificationsLink').hover(function() {
            if ($(this).parents('.bubbles').length) {
                $("#footerBarContainer").removeClass('bubbles');
                $("#footerBarContainer").stop().animate({
                    bottom: '0'
                });
            }
        });

        var $footerBar = $("#footerBar");

        $.ajax({
            url: $.url("/notifications/user/" + APP.me.id),
            context: $("#footerNotificacionsDropDown"),
            dataType: "html",
            global: false,
            ifModified: true
        }).done(function(d){
            $(this).html(d);
            $footerBar.find("abbr.timeago").timeago();
            $('#footerNotificacionsList li:not(.seeAll)').each(function(){
              $(this).children(':not(.notificationLink)').appendTo($(this).children('.notificationLink'));
            });
        });

        $.ajax({
            url: $.url("/notifications/friends/" + APP.me.id),
            context: $("#footerFriendsActivityDropDown"),
            dataType: "html",
            global: false,
            ifModified: true
        }).done(function(d){
            $(this).html(d);
            $footerBar.find("abbr.timeago").timeago();
            $('#footerFriendsActivityList li:not(.seeAll)').each(function(){
              $(this).children(':not(.notificationLink)').appendTo($(this).children('.notificationLink'));
            });
        });

        $.get($.url("/member/friends/" + APP.me.id), function (result) {
            var i, li, a,
            count = result.length,
            ul = $('#footerBarAllFriendsDropDown ul');

            for (i = 0; i < count; i += 1) {
                li = $('<li />')
                .attr('data-id', result[i].id);

                a = $('<a class="userPicture" />')
                .attr('href', $.url('/member/profile/' + result[i].name))
                .append(
                    $('<img />')
                    .attr('src', result[i].icon)
                    .attr('alt', result[i].name)
                    )
                .click(function(e) {
                    e.preventDefault();
                    getFriend($(this).parent().attr('data-id'))
                    $('#footerBarAllFriendsDropDown .current').removeClass('current');
                    $(this).parent().addClass('current');
                    })
                .appendTo(li);

                $('<a />')
                .attr('href', $.url('/member/profile/' + result[i].name))
                .text(result[i].name)
                .click(function(e) {
                    e.preventDefault();
                    getFriend($(this).parent().attr('data-id'))
                    $('#footerBarAllFriendsDropDown .current').removeClass('current');
                    $(this).parent().addClass('current');
                    })
                .appendTo(li);


                li.appendTo(ul);
            }
        });
    }

    function initScroll() {
        if ($(window).scrollTop() > 10) {
            hideBottomBar();
        } else {
            showBottomBar();
        }
    }
    
    function showBottomBar() {
        $("#footerBarContainer").removeClass('bubbles');
        $("#footerBarContainer").stop().animate({
            bottom: '0'
        });
    }
    
    function hideBottomBar() {
        if (!$("#footerBarContainer").is(":animated")) {
            if ($('#footerBarNotifications').hasClass('open') === true) {
                $('#footerNotificacionsDropDown').off('clickoutside');
                $('#footerNotificacionsDropDown').fadeOut('fast').parent().removeClass('open');
            }
            if ($('#footerBarFriendsActivity').hasClass('open') === true) {
                $('#footerFriendsActivityDropDown').off('clickoutside');
                $('#footerFriendsActivityDropDown').fadeOut('fast').parent().removeClass('open');
                $('#footerBarAllFriendsDropDown').fadeOut('fast');
            }
            $("#footerBarContainer").animate({
                bottom: '-44px'
            }, function(){
                if ($("#footerBarNotifications").hasClass("hasNotifications") === true) {
                    $("#footerBarContainer").addClass('bubbles');
                }
            });
        }
    }
    
    function isNear(element, distance, event) {
        var left = element.offset().left - distance,
        top = element.offset().top - distance,
        right = left + element.width() + 2*distance,
        bottom = top + element.height() + 2*distance,
        x = event.pageX,
        y = event.pageY;
      
        return ( x > left && x < right && y > top && y < bottom );
    }

    function getFriend(id) {
        var data = null;
        if (id) {
            data = "friend=" + id;
        }

        $("#footerFriendsActivityDropDown").load(
            $.url("/notifications/friends/" + APP.me.id),
            data, function() {
                $("#footerBar abbr.timeago").timeago();
                $('#footerFriendsActivityList li:not(.seeAll)').each(function(){
                  $(this).children(':not(.notificationLink)').appendTo($(this).children('.notificationLink'));
                });
            });
    }

    function incrementFriendsCount(number) {
        var counter = $("#footerBarFriendsActivityLink").find("span");
        counter.text(parseInt(counter.text(), 10) + number);

        if (number > 0) {
            $("#footerBarFriendsActivity").addClass("hasFriendsActivity");
        } else {
            $("#footerBarFriendsActivity").removeClass("hasFriendsActivity");
            $("#footerBarContainer").fadeOut('fast');
        }
    }

    function setFriendsCount(number) {
        var counter = $("#footerBarFriendsActivityLink").find("span");
        counter.text(number);

        if (number > 0) {
            $("#footerBarFriendsActivity").addClass("hasNotifications");
        } else {
            $("#footerBarFriendsActivity").removeClass("hasNotifications");
        }
    }

    function incrementNotificationCount(number) {
        var counter = $("#footerBarNotificationsLink").find("span");
        counter.text(parseInt(counter.text(), 10) + number);

        if (number > 0) {
            $("#footerBarNotifications").addClass("hasNotifications");
        } else {
            $("#footerBarNotifications").removeClass("hasNotifications");
            $("#footerBarContainer").fadeOut('fast');
        }
    }
    
    function setNotificationCount(number) {
        var counter = $("#footerBarNotificationsLink").find("span");
        counter.text(number);

        if (number > 0) {
            $("#footerBarNotifications").addClass("hasNotifications");
        } else {
            $("#footerBarNotifications").removeClass("hasNotifications");
        }
    }

    function markAsRead(type) {		
        if (markedAsRead[type] || !APP.notifications[type + "Ts"]) {
            return;
        }		
        markedAsRead[type] = true;        
        if (type === 'friends') {
            setFriendsCount(0);
        } else {
            setNotificationCount(0);
        }	
        $.get($.url("/notifications/" + type + "/markasread/" + APP.me.id + "/" + APP.notifications[type + "Ts"]));
    }

    /**
     * init
     *
     * Set things up
     *
     * @param container $container
     */
    function init(container) {
        img = new Image(); 
        img.src = "/images/ajax-white-pink.gif";
        createNotificationBar();
    }

    parent.notifications = {
        init: init,
        inc: incrementNotificationCount,
        incFriends: incrementFriendsCount,
        setFriends: setFriendsCount
    };

    $(function() {
        init();
    });

    return parent;
}(APP || {}, $));
