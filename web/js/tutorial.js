/*global $: true, APP: true */

var APP = (function (parent, $) {
    if (parent.tutorial) {
        return parent;
    }

    function _getModalWidth(width) {
        var modalWidth = 306;
        if ($('#content').width() > 300) {
            modalWidth = width;
        }
        return modalWidth;
    }

    function open(createDialog) {
        $.get($.url('/tutorial'), function(html) {
            if (createDialog) {
                var $container = $("<div/>")
                    .appendTo("body")
                    .hide()
                    .html(html)
                    .dialog({
                        closeOnEscape: true,
                        autoOpen: true,
                        modal: true,
                        dialogClass: "modalWindow tutorialModal",
                        closeText: "X",
                        width: _getModalWidth(766),
                        draggable: false,
                        resizable: false,
                        open: function(event, ui) {
                            _gaq.push(['_trackEvent', 'Dialog', 'Show', 'Tutorial']);
                        },
                        close: function(event, ui) {
                            _gaq.push(['_trackEvent', 'Dialog', 'Hide', 'Tutorial']);
                            $container.remove();
                            $('a.startTutorial').on('click', function(e) {
                                e.preventDefault();
                                $(this).off('click');
                                open(true);
                            });
                        }
                    });
            }
        }, 'html');
    }

    function loadNotice() {
        var flash = parent.flash('new to site? <a class="startTutorial">quick tutorial</a>', 'success');

        flash.find("a.startTutorial").click(function(e) {
            e.preventDefault();

            flash.slideUp(function(){
                flash.remove();
            });

            open(true);
        });

        flash.find("a.close")
            .unbind('click')
            .click(function(e) {
                e.preventDefault();

                flash.slideUp(function(){
                    flash.remove();
                    open(false);
                });
            });
    }

    function init()  {
        $('a.startTutorial').click(function(e) {
            e.preventDefault();
            $(this).off('click');
            open(true);
        });
    }

    parent.tutorial = {
        init: init,
        open: open,
        loadNotice: loadNotice
    };

    return parent;
}(APP || {}, $));

$(function () {
    APP.tutorial.init();
});
