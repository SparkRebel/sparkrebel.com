// Array Remove - By John Resig (MIT Licensed)
// From http://ejohn.org/blog/javascript-array-remove/
Array.prototype.remove = function(from, to) {
    var rest = this.slice((to || from) + 1 || this.length);
    this.length = from < 0 ? this.length + from : from;
    return this.push.apply(this, rest);
};

// From http://stackoverflow.com/a/3291856/3765
String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}

$("a[href^='http']:not([href^='http://" + document.domain + "']):not([href^='http://www." + document.domain + "'])").click(function(e){
    if (typeof _gat == 'undefined') {
        return;
    }
    e.preventDefault();
    var link = $(this),
        href = link.attr('href'),
        noProtocol = href.replace(/http[s]?:\/\//, '');
    _gat._getTrackerByName()._trackEvent('Outbound Link', noProtocol);
    if (link.attr('target') == '_blank') {
        window.open(href);
    } else {
        setTimeout('document.location = "' + href + '";', 100);
    }
});

$(function() {
    $.fn.sortSelect = function(){
        var options = $("#" + this.attr('id') + ' option');
        options.sort(function(a, b) {
            a = a.text.toLowerCase();
            b = b.text.toLowerCase();
            if (a == b) return 0;
            if (a > b) return 1;
            return -1;
        });
        $(this).empty().append(options);
        return $(this);
    }

    $.fn.disable = function() {
        return this.each(function() {
            if (typeof this.disabled != "undefined") this.disabled = true;
        });
    }

    $.fn.enable = function() {
        return this.each(function() {
            if (typeof this.disabled != "undefined") this.disabled = false;
        });
    }

    $.extend({
        /**
         * Make the ajax calls base-url (subfolder) independent
         * call $.('/a/url') to get /cakeInstallIsHere/a/url out
         * If there is no subfolder, the passed argument is returned directly
         */
        url: (function(url) {
            base = APP.config.base_url;
            if (base === '/' || !base) {
                return function(url) {
                    return url;
                };
            }
            return function (url) {
                return base + url;
            };
        })(),
        empty: (function(value) {
            if (value !== undefined) {
                if (value == "") {
                    return true;
                } else {
                    return value;
                }
            } else {
                return true;
            }
        })()
    });

    // Hijack all AJAX requests
    $(document).ajaxStart(function(){
        $("html").addClass("ajax");
    }).ajaxStop(function(){
        $("html").removeClass("ajax");
    }).ajaxError(function (event, jqXHR) {
        $("html").removeClass("ajax");
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

        }

        if (APP.admin) {
            if (json && json.message) {
                APP.flash('[Admin Error: ' + jqXHR.status + '] ' + json.message, 'error');
            } else {
                APP.flash('[Admin Error: ' + jqXHR.status + '] ' + jqXHR.statusText, 'error');
            }
        }
        // Handle specific cases
        if (403 === jqXHR.status) {
            if (json && json.message) {
                APP.login.start(json.message);
                return;
            }
            APP.login.start(jqXHR.statusText);
        } else if (503 === jqXHR.status) {
            //APP.login.start();
            $('#inviteRequest').fadeIn('fast');
            return;
        }
    });

    // Stick footer to bottom of window
    var $container = $("#container"),
    $footerNav = $("#footerNav");
    function stickFooter() {
        $container.css("min-height", $(document.body).height() - $footerNav.outerHeight() - 140);
    }
    if ($footerNav.length) {
        $(window).load(stickFooter).resize(stickFooter);
    }

    // Set breakpoints for difference responsive versions
    $(window).setBreakpoints({
        distinct: true,
        breakpoints: [300, 720, 960, 1200]
    });

    // User DropDown
    $('#headerUserLink').click(function(e) {
        /*
        e.preventDefault();
        $('#headerUserDropDown').slideToggle('fast');
        $(this).toggleClass('opened');
        */
        e.preventDefault();
        if($('#headerUserDropDown').css('display') == 'none') {
            $('#headerUserDropDown').slideDown('fast', function() {
                $('#headerUserDropDown').on('clickoutside', function(e){
                    $(this).slideUp('fast', function() {
                        $('#headerUserDropDown').off('clickoutside');
                    });
                });
            });
        }
    });

    // Help DropDown
    $('#headerHelpLink').click(function(e) {
        e.preventDefault();
        if($('#headerHelpDropDown').css('display') == 'none') {
            $('#headerHelpDropDown').slideDown('fast', function() {
                $('#headerHelpDropDown').on('clickoutside', function(e){
                    $(this).slideUp('fast', function() {
                        $('#headerHelpDropDown').off('clickoutside');
                    });
                });
            });
        }
    });

    // Search placeholder
    $('#headerSearchInput').focusin(function(){
        $(this).attr('placeholder', '');
    });

    $('#headerSearchInput').focusout(function(){
        $(this).attr('placeholder', 'Search site');
    });

    // Show/hide the header according to the scroll amount
    if($('#content').width() > 300) {
        $scrollY = $(window).scrollTop();
        if ($scrollY > 44) {
            $("#headerContainer #header h2").addClass("small").css({ "position" : "fixed", "top" : "0" });
            $("#navContainer").css({ "position" : "fixed", "top" : "0" });
            $('#navShowHeader').show();
        } else {
            $("#headerContainer #header h2").removeClass("small").css({ "position" : "absolute", "top" : "0" });
            $("#navContainer").css({ "position" : "absolute", "top" : "44px" });
            $('#navShowHeader').hide();
        }

        $('#navShowHeader').click(
            function() {
                $(window).scrollTop(0);
            }
        );
    } else {
        $('#navShowHeader').hide();
        $("#headerContainer").css({ "position" : "absolute", "top" : "0" });
        $("#headerContainer #header h2").removeClass("small").css({ "position" : "absolute", "top" : "-3px" });
        $("#navContainer").css({ "position" : "absolute", "top" : "92px" });
    }

    // Scroll to top arrow
    $('#navShowHeader').click(function(){
        $(window).scrollTop(0);
    });

});

/*
use data-confirm attribute
$(".boardDeleteButton").click(function(e){
    return confirm("Are you sure you want to delete this Collection and *ALL* of its Sparks?");
});

$(".commentDeleteButton").click(function(e){
    return confirm("Are you sure you want to delete this comment?");
});

$(".postDeleteButton").click(function(e){
    return confirm("Are you sure you want to delete this Spark?");
});*/

function LinkMethodHandler () {
    var self = this;
    jQuery.extend(this, {
        initialize: function() {

            $('body').append('<div id="dialog-confirm" title="Confirmation Required"></div>');
            $("#dialog-confirm").dialog({
              autoOpen: false,
              modal: true
            });

            $(document).delegate('a[data-method], a[data-confirm]', 'click', function(e) {

                var link = $(this), method = link.data('method'), data = link.data('params'), href = link.attr('href'),
                confirm_message = link.data('confirm');
                if(confirm_message == undefined) {
                  confirm_message = 'Are You sure?';
                }

                if(method !== 'GET')
                  e.preventDefault();
                else
                  return true;

                self.confirm_message(confirm_message, href, method, link);

            });

            $(document).delegate('a.dialog', 'click', function(e) {
                e.preventDefault();
                var title = $(this).attr("title");
                var dialogClass = $(this).attr("data-dialog-class");
                var dialogWidth = $(this).attr("data-dialog-width");
                $.get($(this).attr("href"), function(html) {
                    //template = template similar to resparking with header and modalcontent
                    var template = "<div><header><h2>" + title + "</h2></header>";
                    template += "<div class='modalContent'>" + html + "</div></div>";
                    var $container = $(template)
                    .appendTo("body")
                    .hide()
                    self.createDefaultDialog($container, dialogClass, dialogWidth);
                }, 'html');
            });
        },


        confirm_message: function(text, href, method, link) {
          //return confirm(text);
          var template = "<div><header><h2>Are you sure?</h2></header>";
              template += "<div class='modalContent'>" + text + "</div></div>";
          $('#dialog-confirm').html(template);
          $("#dialog-confirm").dialog({
                modal: true,
                dialogClass: "modalWindow",
                draggable: false,
                resizable: false,
                closeText: "X",
                buttons : {
                  "Confirm" : function() {
                    if(method === 'delete' && link.data('replace-on-submit') === true) {
                      link.replaceWith(link.text());
                    }
                    self.handleRequest(href, method);
                    $(this).dialog("close");
                  },
                  "Cancel" : function() {
                    $(this).dialog("close");
                  }
                }
              });

          $("#dialog-confirm").dialog("open");
        },

        handleRequest: function(href, method) {
            var form = $('<form method="post" action="' + href + '"></form>');
            var metadata_input = '<input name="_method" value="' + method + '" type="hidden" />';
            form.hide().append(metadata_input).appendTo('body');
            form.submit();
        },

        createDefaultDialog: function($element, $dialogClass, $dialogWidth) {
            $element.dialog({
                autoOpen: true,
                modal: true,
                dialogClass: "modalWindow " + $dialogClass,
                draggable: false,
                resizable: false,
                closeText: "X",
                width: self.getModalWidth(660),
                width: ($dialogWidth ? self.getModalWidth($dialogWidth) : self.getModalWidth(660)),
                open: function(event, ui) {

                },
                close: function(event, ui) {
                    $element.remove();
                }
            });

            //
            // New/Existing Board Toggle for Edit Spark Modal
            if ($dialogClass == "postModal existingBoard") {
                var $showExistingBoard = $element.find('.show_board_existing'),
                $existingBoard     = $element.find('.board_existing'),
                $showNewBoard      = $element.find('.show_board_new'),
                $board             = $element.find('.board');

                $showExistingBoard.find('a').click(function(e) {
                    e.preventDefault();
                    repostState = 'existingBoard';
                    $showExistingBoard.fadeOut('fast');
                    $board.slideToggle(function() {
                        $existingBoard.slideToggle();
                        $showNewBoard.fadeIn('fast');
                    });
                });

                $showNewBoard.find('a').click(function(e) {
                    e.preventDefault();
                    repostState = 'newBoard';
                    $showNewBoard.fadeOut('fast');
                    $existingBoard.slideToggle(function() {
                        $board.slideToggle();
                        $showExistingBoard.fadeIn('fast');
                    });
                });
            }

            return $element;
        },

        getModalWidth: function(width) {
            var modalWidth = 300;
            if ($('#content').width() > 300) {
                modalWidth = width;
            }
            return modalWidth;
        }


    });
}

var link_method_handler = new LinkMethodHandler();
$(document).ready(function() {
    link_method_handler.initialize();
});

$(window).scroll(function() {
    // Header and Nav
    if($('#content').width() > 300) {
        $scrollY = $(window).scrollTop();
        $header = $("#headerContainer");
        $logo = $("#headerContainer #header h2");
        $nav = $("#navContainer");

        $header.css("top", "0");

        if ($scrollY > 44) {
            $('#navShowHeader').show();
            $nav.css({ "position" : "fixed", "top" : "0" });
            if ($logo.hasClass("small") != true) {
                $logo.fadeOut(200, function() {
                    $logo.css({ "position" : "fixed", "top" : "0" });
                    $logo.addClass("small");
                    $logo.fadeIn(200);
                });
            } else {
                $logo.css({ "position" : "fixed", "top" : "0" });
            }
        } else {
            $('#navShowHeader').hide();
            $nav.css({ "position" : "absolute", "top" : "44px" });
            $logo.css("top", "44px");
            if ($logo.hasClass("small") == true) {
                $logo.fadeOut(200, function() {
                    $logo.css({ "position" : "absolute", "top" : "0" });
                    $logo.removeClass("small");
                    $logo.fadeIn(200);
                });
            } else {
                $logo.stop(true).removeClass("small").css({ "top" : "0", "opacity" : "1" });
            }
        }
    }
});

//
// Fix for iPad (portrait to landscape rotation)
if (navigator.userAgent.match(/iPad/i)) {
    var viewportmeta = document.querySelector('meta[name="viewport"]');
    if (viewportmeta) {
        viewportmeta.content = 'width=device-width, minimum-scale=1.0, maximum-scale=1.0, initial-scale=1.0';
        document.body.addEventListener('gesturestart', function () {
            viewportmeta.content = 'width=device-width, minimum-scale=0.25, maximum-scale=1.6';
        }, false);
    }
}
