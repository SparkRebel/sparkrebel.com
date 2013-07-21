/*global $: true, APP: true */

var APP = (function (parent, $) {
    if (parent.login) {
        return parent;
    }

    var $loginModal = $("#loginForm");

    function _getModalWidth(width) {
        var modalWidth = 300;
        if ($('#content').width() > 300) {
            modalWidth = width;
        }
        return modalWidth;
    }

    function setupLoginModal() {
        $loginModal.dialog({
            closeOnEscape: true,
            autoOpen: false,
            modal: true,
            dialogClass: "registrationModal loginModal",
            width: _getModalWidth(720),
            closeText: 'X',
            draggable: false,
            resizable: false,
            open: function(event, ui) {
                _gaq.push(['_trackEvent', 'Dialog', 'Show', 'Login']);

                // Prevent links from auto-focusing
                $(":focus").blur();

                if (_getModalWidth(640) <= 300) {
                    $(window).scrollTop($loginModal.offset().top - 50);
                } else {
                    // Hide login strip
                    $("#loginStripContainer").hide();
                }
            },
            close: function(event, ui) {
                _gaq.push(['_trackEvent', 'Dialog', 'Hide', 'Login']);

                if (_getModalWidth(640) > 300) {
                    // Show login strip
                    $("#loginStripContainer").show();
                }
            }
        });
    }

    function init() {
        $loginModal = $("#loginForm");
        setupLoginModal();

        // Facebook login button
        $(".fbLoginButton").click(function(e){
            e.preventDefault();
            var $self = $(this);
            _gaq.push(['_trackEvent', 'User Login', 'Started']);
            _gaq.push(['_trackEvent', 'Facebook Login', 'Started']);
            FB.login(function(response){
                if (response.authResponse) {
                    // Authorized app and logged in
                    _gaq.push(['_trackEvent', 'Facebook Login', 'Connected', null, null, true]);
                    $.ajax({
                        url: $self.attr("href"),
                        cache: false,
                        global: false,
                        type: "GET",
                        dataType: "json",
                        beforeSend: function() {
                            if ($(".loginModal").is(":visible")) {
                                $(".modalContent").find(".error").text("").hide();
                                $self.addClass("disabled");
                                $("#loginPartner").find("a").addClass("disabled");
                                $('#inviteRedeem #loginFacebook .loading').show();
                            } else {
                                $self.addClass("disabled");
                                $("#loginStripFacebook .loading").show();
                            }
                        }
                    }).done(function(json, textStatus, jqXHR) {
                        handleLoginCheckResponse(json);
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        var json = false;
                        try {
                            json = $.parseJSON(jqXHR.responseText);
                            // Handle inconsitencies between message/error
                            if (!json.error && json.message) {
                                json.error = json.message;
                            } else if (!json.message && json.error) {
                                json.message = json.error;
                            }
                        } catch(e) {
                            json = {success: false, message: jqXHR.statusText};
                        }
                        handleLoginCheckResponse(json);
                    });
                } else {
                    // User cancelled login or did not fully authorize
                    _gaq.push(['_trackEvent', 'User Login', 'Failed', 'Facebook - Canceled', null, true]);
                    _gaq.push(['_trackEvent', 'Facebook Login', 'Canceled', null, null, true]);
                }
            }, {scope: 'email,user_birthday,user_location,user_interests,user_likes'});
        });

        // Bind to any link with .requireAuth
        $(".requireAuth").click(function(e){
            e.preventDefault();
            if (APP.me === false) {
                start();
                return false;
            }
        });

        if (!APP.me) {
            var $setupContainer = $("#nonLoggedUser"),
                $setupMyBrands  = $setupContainer.find(".my_brands"),
                $setupMyStream  = $setupContainer.find(".my_stream"),
                $setupMyCelebs  = $setupContainer.find(".my_celebs"),
                $setupPromos    = $setupContainer.find(".promos_sales");

            $setupContainer.find(".primary").click(function(e){
                if (typeof _gat == 'undefined') {
                    return;
                }
                e.preventDefault();
                _gat._getTrackerByName()._trackEvent('Button', 'Click', 'Get Started Now!');
                setTimeout('document.location = "' + $(this).attr("href") + '";', 100);
            });

            $(".navHome a").click(function(e){
               e.preventDefault();
               guiders.hideAll();
               if ($setupMyBrands.is(":visible")) {
                   $setupMyBrands.slideUp(function(){
                       $setupMyStream.slideDown("fast");
                   });
               } else if($setupMyCelebs.is(":visible")) {
                   $setupMyCelebs.slideUp(function(){
                       $setupMyStream.slideDown("fast");
                   });
               } else if($setupPromos.is(":visible")) {
                   $setupPromos.slideUp(function(){
                       $setupMyStream.slideDown("fast");
                   });
               } else {
                   $setupMyBrands.hide();
                   $setupMyCelebs.hide();
                   $setupPromos.hide();
                   $setupMyStream.show();
                   $setupContainer.slideDown();
                   $("#content > *:not(#nonLoggedUser)").fadeOut("fast");
               }
               $("#navContainer .current").removeClass("current");
               $("#navContainer .navHome").addClass("current");
            });

            $(".navMyBrands").find("span, a").click(function(e){
               e.preventDefault();
               guiders.hideAll();
               if ($setupMyStream.is(":visible")) {
                   $setupMyStream.slideUp(function(){
                       $setupMyBrands.slideDown("fast");
                   });
               } else if($setupMyCelebs.is(":visible")) {
                   $setupMyCelebs.slideUp(function(){
                       $setupMyBrands.slideDown("fast");
                   });
               } else if($setupPromos.is(":visible")) {
                   $setupPromos.slideUp(function(){
                       $setupMyBrands.slideDown("fast");
                   });
               
               } else {
                   $setupMyStream.hide();
                   $setupMyCelebs.hide();
                   $setupPromos.hide();
                   $setupMyBrands.show();
                   $setupContainer.slideDown();
                   $("#content > *:not(#nonLoggedUser)").fadeOut("fast");
               }
               $("#navContainer .current").removeClass("current");
               $("#navContainer .navMyBrands").addClass("current");
            });
            
            $(".navMyCelebs").find("span, a").click(function(e){
               e.preventDefault();
               guiders.hideAll();
               if ($setupMyStream.is(":visible")) {
                   $setupMyStream.slideUp(function(){
                       $setupMyCelebs.slideDown("fast");
                   });
               } else if($setupMyBrands.is(":visible")) {
                   $setupMyBrands.slideUp(function(){
                       $setupMyCelebs.slideDown("fast");
                   });
               } else if($setupPromos.is(":visible")) {
                   $setupPromos.slideUp(function(){
                       $setupMyCelebs.slideDown("fast");
                   });
               
               } else {
                   $setupMyStream.hide();
                   $setupMyBrands.hide();
                   $setupPromos.hide();
                   $setupMyCelebs.show();
                   $setupContainer.slideDown();
                   $("#content > *:not(#nonLoggedUser)").fadeOut("fast");
               }
               $("#navContainer .current").removeClass("current");
               $("#navContainer .navMyCelebs").addClass("current");
            });

            $(".navChannels .nav-sales-promos a").click(function(e){
               e.preventDefault();
               guiders.hideAll();
               if ($setupMyStream.is(":visible")) {
                   $setupMyStream.slideUp(function(){
                       $setupPromos.slideDown("fast");
                   });
               } else if($setupMyBrands.is(":visible")) {
                   $setupMyBrands.slideUp(function(){
                       $setupPromos.slideDown("fast");
                   });
               } else if($setupMyCelebs.is(":visible")) {
                   $setupMyCelebs.slideUp(function(){
                       $setupPromos.slideDown("fast");
                   });
               } else {
                   $setupMyStream.hide();
                   $setupMyBrands.hide();
                   $setupMyCelebs.hide();
                   $setupPromos.show();
                   $setupContainer.slideDown();
                   $("#content > *:not(#nonLoggedUser)").fadeOut("fast");
               }
               $("#navContainer .current").removeClass("current");
               $("#navContainer .navChannels, #navContainer .navChannels .nav-sales-promos").addClass("current");
            });
        }

        // On mobiles, put the video below
        if (_getModalWidth(640) <= 300) {
            $('#loginVideo').appendTo('#inviteRedeem');
        }

        //
        // Partners links
        $("#loginPartnerLink").click(function(e) {
            e.preventDefault();
            if ($("form.partnerLogin").length > 0) {
                $("#loginJoin").fadeOut('fast');
                $("#inviteRedeem").fadeOut('fast', function(){
                    $("form.partnerLogin").fadeIn('fast', function(){
                        $("#loginVideo").hide();
                    });
                });
            } else {
                $.get($(this).attr("href"), function(data) {
                    $("#loginVideo").hide();
                    $("#loginJoin").fadeOut('fast');
                    $("#inviteRedeem").fadeOut('fast', function() {
                        $(".modalContent").append(data);
                        $("#partnerCancel").click(function(e){
                            e.preventDefault();
                            $("form.partnerLogin").fadeOut('fast', function(){
                                $("#loginVideo").show();
                                $("#loginJoin").fadeIn('fast');
                                $("#inviteRedeem").fadeIn('fast');
                            });
                        });
                    });
                });
            }
            return false;
        })
    }

    function start(errorMessage) {
        if ($("#loginForm").dialog("isOpen")) {
            if (errorMessage) {
                $loginModal.find(".error").text(errorMessage);
            }
        } else {
            $loginModal.dialog("open");
        }
    }

    function handleLoginCheckResponse(json) {
        if (json.success) {
            if (!json.user.settings.signup_preferences) {
                $loginModal.dialog('close');
                if (APP.registration) {
                    APP.registration.register();
                }
            } else {
                _gaq.push(['_trackEvent', 'User Login', 'Success', null, null, true]);
                if (json.redirect) {
                    window.location = json.redirect;
                } else {
                    window.location.reload();
                }
            }
        } else {
            _gaq.push(['_trackEvent', 'User Login', 'Failed', json.message, null, true]);
            $(".modalContent").find(".error").text(json.message).addClass("status").show();
            $(".fbLoginButton").removeClass("disabled");
            $("#loginPartner").find("a").removeClass("disabled");
            $('#inviteRedeem #loginFacebook .loading').hide();
        }
    }

    parent.login = (function() {
        return {
            start: start,
            init: init
        };
    }());

    return parent;
}(APP || {}, $));

$(function() {
    APP.login.init();
});
