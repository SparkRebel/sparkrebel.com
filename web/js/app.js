/*global $: true, APP: true */

var APP = (function (parent, $) {

    parent.flash = function (message, type) {
        var str, template, div;

        template = '<div class="flash :class:">:message:<a class="close" href="#">dismiss</a></div>';
        if (!type) {
            type = 'info';
        }

        str = template
                .replace(':class:', 'flash-' + type)
                .replace(':message:', message);

        div = $(str)
            .appendTo('#flash-messages');
            // .effect('highlight', {}, 3000);

        div.find('a.close').click(function(e) {
                e.preventDefault();

                $(this).parent().slideUp( function() {
                    $(this).remove();
                });
            });

        return div;
    };

    return parent;

}(APP || {}, $));



