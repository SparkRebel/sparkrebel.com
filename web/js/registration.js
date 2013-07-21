/*global $: true, APP: true */

var APP = (function (parent, $) {
    if (parent.registration) {
        return parent;
    }

    var $templates,
        selected = {
            areas: [],
            brands: [],
            categories: [],
            celebs: []
        };

    function _getModalWidth(width) {
        var modalWidth = 260;
        if ($('#content').width() > 300) {
            modalWidth = width;
        }
        return modalWidth;
    }

    function register() {
        if (!$templates) {
            $templates = $('<div id="templateContainer"/>')
                .load($.url('/register/templates'), function(){ registrationStep1(); })
                .appendTo('body');
        }
        _gaq.push(['_trackEvent', 'User Registration', 'Started']);
        registrationStep1();
    }

    function showRegistrationModal(div, step) {
        if ($("#loginForm").length > 0) {
            $("#loginForm").dialog('close');
        }
        div.dialog({
            closeOnEscape: false,
            closeText: 'X',
            dialogClass: 'registrationModal step' + step,
            draggable: false,
            height: 460,
            modal: true,
            resizable: false,
            width: _getModalWidth(820),
            open: function(event, ui) {
                _gaq.push(['_trackEvent', 'Dialog', 'Show', 'Registration - Step ' + step]);
            },
            close: function(event, ui) {
                _gaq.push(['_trackEvent', 'Dialog', 'Hide', 'Registration - Step ' + step]);
            }
        });
    }
    
    function sendRegistrationPreferences(div) {
        $.ajax({
            url: $.url('/register/preferences'),
            type: "POST",
            data: selected,
            dataType: "json",
            beforeSend: function() {
                $('.registrationModalPaginator', div).unbind('click').addClass('disable');
                $('.registrationModalContent', div).find('.loading').show();
            }
        }).done(function(json, textStatus, jqXHR) {
            if (APP.session.sub_id) {
                $.get("http://track.supersonicads.com/api/v1/processCommissionsCallback.php?advertiserId=532&password=da20a0b8&dynamicParameter=" + APP.session.sub_id);
            }
            _gaq.push(['_trackEvent', 'User Registration', 'Success']);
            div.dialog("close");
            registrationStep7();
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
                json = {
                    success: false,
                    message: jqXHR.statusText
                };
            }
            _gaq.push(['_trackEvent', 'User Registration', 'Failed', json.message]);
            $('.registrationModalError', div)
                .text(json.message)
                .stop()
                .fadeIn('fast');
        });
    }

    function registrationStep1() {
        var div = $("#registerStep1");

        showRegistrationModal(div, 1);
        $(document).scrollTop(0);

        // Prevent Privacy Policy link from auto-focusing
        $(".registrationModalPrivacy:focus").blur();

        $('.registrationModalPaginator', div).click(function(){
            div.dialog("close");
            registrationStep2();
        });
    }

    function registrationStep2() {
        var div = $("#registerStep2");

        showRegistrationModal(div, 2);
        $('input:checkbox', div)
            .unbind('click')
            .click(function(){
                var i, id = this.id.substr(-24);
                if ($(this).attr('checked')) {
                    selected.areas.push(id);
                } else {
                    i = selected.areas.indexOf(id);
                    selected.areas.remove(i);
                }
            });

        if ($('#content').width() > 300 && $('#registerStep2 .listContainer1').length <= 0) {
            $('.areas').easyListSplitter({ colNumber: 3 });
        }

        $('.registrationModalBack', div).click(function(){
            div.dialog("close");
            registrationStep1();
        });

        $('.registrationModalPaginator', div).click(function(){
            if (!selected.areas.length) {
                $('.registrationModalError', div).stop().fadeIn('fast');
                return false;
            }

            div.dialog("close");
            registrationStep3();
        });
    }

    function registrationStep3() {
        var i, div = $("#registerStep3");

        showRegistrationModal(div, 3);
        $('input:checkbox', div)
            .unbind('click')
            .click(function(){
                var i, id = this.id.substr(-24);
                if ($(this).attr('checked')) {
                    selected.categories.push(id);
                } else {
                    i = selected.categories.indexOf(id);
                    selected.categories.remove(i);
                }
            });

        if ($('#content').width() > 300 && $('#registerStep3 .listContainer2').length <= 0) {
            $('.categories').easyListSplitter({ colNumber: 3 });
        }

        $('.registrationModalBack', div).click(function(){
            div.dialog("close");
            registrationStep2();
        });

        $('.registrationModalPaginator', div).click(function(){
            if (!selected.categories.length) {
                $('.registrationModalError', div).stop().fadeIn('fast');
                return false;
            }

            div.dialog("close");
            registrationStep4();
        });
    }

    function registrationStep4() {
        var div = $("#registerStep4");
        
        showRegistrationModal(div, 4);
        $('input:checkbox', div)
            .unbind('click')
            .click(function(){
                var i, id = this.id.substr(-24);
                if ($(this).attr('checked')) {
                    selected.brands.push(id);
                } else {
                    i = selected.brands.indexOf(id);
                    selected.brands.remove(i);
                }
            });

        if ($('#content').width() > 300 && $('#registerStep4 .listContainer3').length <= 0) {
            $('.brands').easyListSplitter({ colNumber: 3 });
        }

        $('.registrationModalBack', div).click(function(){
            div.dialog("close");
            registrationStep3();
        });

        $('.registrationModalPaginator', div).click(function(){
            if (!selected.brands.length) {
                $('.registrationModalError', div).stop().text('Please select at least one of the options.').fadeIn('fast');
                return false;
            } else if(selected.brands.length > 5) {
                $('.registrationModalError', div).stop().text('You have selected too many brands, please select up to 5.').fadeIn('fast');
                return false;
            }

            div.dialog("close");
            registrationStep5();
        });
    }
    
    function registrationStep5() {
        var div = $("#registerStep5");

        showRegistrationModal(div, 5);
        $('input:checkbox', div)
            .unbind('click')
            .click(function(){
                var i, id = this.id.substr(-24);
                if ($(this).attr('checked')) {
                    selected.celebs.push(id);
                } else {
                    i = selected.celebs.indexOf(id);
                    selected.celebs.remove(i);
                }
            });

        if ($('#content').width() > 300 && $('#registerStep5 .listContainer4').length <= 0) {
            $('.celebs').easyListSplitter({ colNumber: 3 });
        }

        $('.registrationModalBack', div).click(function(){
            div.dialog("close");
            registrationStep4();
        });

        $('.registrationModalPaginator', div).click(function(){
            if ($('#content').width() > 300) {
                if (!selected.celebs.length) {
                    $('.registrationModalError', div).stop().text('Please select at least one of the options.').fadeIn('fast');
                    return false;
                } else if(selected.celebs.length > 5) {
                    $('.registrationModalError', div).stop().text('You have selected too many celebs, please select up to 5.').fadeIn('fast');
                    return false;
                }
    
                div.dialog("close");
                registrationStep6();
            } else {
                sendRegistrationPreferences(div);
            }
        });
    }
    
    function registrationStep6() {
        var div = $("#registerStep6");
        
        showRegistrationModal(div, 6);
        
        // Show correct screenshot (OS and Browser)
        $userOS = "win";
        $userBrowser = "chrome";
        
        if (navigator.appVersion.indexOf("Mac") != -1) {
            $userOS = "mac";
            if (navigator.userAgent.indexOf("Chrome") != -1) {
                $(".registrationModalChrome", div).css("display", "block");
            } else if (navigator.userAgent.indexOf("Firefox") != -1) {
                $userBrowser = "firefox";
                $(".registrationModalFirefox", div).css("display", "block");
            } else if (navigator.userAgent.indexOf("Safari") != -1) {
                $userBrowser = "safari";
                $(".registrationModalSafari", div).css("display", "block");
            } else {
                $userBrowser = "safari";
            }
        } else if(navigator.appVersion.indexOf("Win") != -1) {
            if (navigator.userAgent.indexOf("Firefox") != -1) {
                $userBrowser = "firefox";
                $(".registrationModalFirefox", div).css("display", "block");
            } else if (navigator.userAgent.indexOf("MSIE") != -1) {
                $userBrowser = "ie";
                $(".registrationModalIE", div).css("display", "block");
            } else if (navigator.userAgent.indexOf("Chrome") != -1) {
                $(".registrationModalChrome", div).css("display", "block");
            }
        }
        
        $(".registrationModalBrowser", div)
            .attr("src", "/images/modals/browsers/" + $userOS + "." + $userBrowser + ".jpg")
            .css("display", "block");

        $('.registrationModalBack', div).click(function(){
            div.dialog("close");
            registrationStep5();
        });

        $('.registrationModalPaginator', div).click(function(){
            sendRegistrationPreferences(div);
        });
    }

    function registrationStep7() {
        var div = $("#registerStep7");

        showRegistrationModal(div, 7);
        if (APP.tutorial) {
            $('#startTutorial').click(function() {
                APP.tutorial.open(true);
            });
        }

        setTimeout(function(){
          $('#loadingStream').fadeOut('fast', function(){
            if ($('#content').width() > 300) {
                $bottomPosition = '35px';
            } else {
                $bottomPosition = '15px';
            }
            $(this)
              .css({ 'background' : 'none', 'bottom' : $bottomPosition, 'paddingLeft' : '15px', 'right' : '20px' })
              .text('Done!')
              .fadeIn('fast')
              .delay(1000)
              .fadeOut('fast', function(){
                $('#gotoStream').fadeIn('fast');
            });
          });
        }, 7000);
    }

    parent.registration = (function(){
        return {
            register: register
        };
    }());

    return parent;
}(APP || {}, $));
