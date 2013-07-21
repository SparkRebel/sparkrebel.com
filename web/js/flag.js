/*global $: true, APP: true */

var APP = (function (parent, $) {
    if (parent.flag) {
        return parent;
    }

    var $templates;

    function init()  {
        var postId,
            commentId,
            flagType = 'inappropriate',
            postForm,
            $templates;

        postId = $('div.itemPicture').attr('data-id');

        function initializePostForm() {
            postForm = $('#flagPost form');

            if (APP.me) {
                $('#anonUser', postForm).remove();
            }
            
            $('fieldset:not(#type, #anonUser)', postForm).hide();
            
            $('#type #inappropriate', postForm).attr('checked', true);

            $('input[type="radio"]').change(function() {
                var newType = $(this).val();

                if (newType !== flagType) {
                    flagType = newType;

                    $('#comments label', postForm).removeClass('required');
                    $('#anonUser', postForm).show();
                    $('fieldset:not(#type, #anonUser)', postForm).hide();

                    if (flagType === 'copyright') {
                        if ($('#flagCopyrightText').css('display') == 'block') {
                            $('#comments', postForm).hide();
                            $('#flagSubmit', postForm).hide();
                        }
                        $('#anonUser', postForm).hide();
                        $('#flagCopyright', postForm).show();
                        return;
                    }

                    if (flagType === 'inappropriate') {
                        $('#flagComment', postForm).show();
                        $('#comments', postForm).show();
                        $('#flagSubmit', postForm).show();
                        return;
                    }

                    if (flagType === 'other') {
                        $('#flagOther', postForm).show();
                        $('#comments', postForm).show();
                        $('#flagSubmit', postForm).show();
                        $('#comments label', postForm).addClass('required');
                        return;
                    }
                }
            });
            
            $('#flagCopyrightText .button').click(function(e) {
                e.preventDefault();
                $('#flagCopyrightText').fadeOut('fast', function() {
                    $('#flagCopyrightForm, #comments, #flagSubmit', postForm).fadeIn('fast');
                });
            });

            postForm.submit(function (e) {
                var errors;

                e.preventDefault();

                errors = validatePostForm();
                if (errors.length) {
                    for(i in errors) {
                        if (errors.hasOwnProperty(i)) {
                            $('#' + errors[i], postForm)
                                .stop()
                                .effect('highlight', {}, 3000);
                        }
                    }
                    return;
                }

                data = postForm.serializeArray();
                data.push({
                    name: "url",
                    value: document.location.pathname
                });

                $.post(
                    $.url('/flag/post/' + postId),
                    data,
                    function(response) {
                        if (response.status === 'ok') {
                            postForm.dialog('close');
                            APP.flash('Thanks for contacting us. Your request has been received and will be handled shortly');
                        }
                    }
                );
            });
        }

        function initializeCommentForm() {
            commentForm = $('#flagComment form');

            if (APP.me) {
                $('#anonUser', commentForm).remove();
            }

            commentForm.submit(function (e) {
                var errors;

                e.preventDefault();

                errors = validateCommentForm();
                if (errors.length) {
                    for(i in errors) {
                        if (errors.hasOwnProperty(i)) {
                            $('#' + errors[i], commentForm)
                                .stop()
                                .effect('highlight', {}, 3000);
                        }
                    }
                    return;
                }

                data = commentForm.serializeArray();
                data.push({
                    name: "url",
                    value: document.location.pathname
                });

                $.post(
                    $.url('/flag/comment/' + commentId),
                    data,
                    function(response) {
                        if (response.status === 'ok') {
                            commentForm.dialog('close');
                            APP.flash('Thanks for contacting us. Your request has been received and will be handled shortly');
                        }
                    }
                );
            });
        }
        
        function _getModalWidth(width) {
            var modalWidth = 300;
            if ($('#content').width() > 300) {
                modalWidth = width;
            }
            return modalWidth;
        }

        function showPostDialog() {
            postForm.dialog({
                width: _getModalWidth(680),
                closeOnEscape: true,
                closeText: 'X',
                dialogClass: 'modalWindow flagModal',
                draggable: false,
                modal: true,
                resizable: false
            });
        }

        function showCommentDialog(id) {
            commentId = id;
            flagType = 'comment';

            commentForm.dialog({
                width: _getModalWidth(680),
                closeOnEscape: true,
                closeText: 'X',
                dialogClass: 'modalWindow flagModal',
                draggable: false,
                modal: true,
                resizable: false
            });
        }

        function validatePostForm() {
            var errors = [];

            if (!APP.me && flagType !== 'copyright') {
                if (!$('[name="anonName"]', postForm).val()) {
                    errors.push('anonName');
                }
                if (!$('[name="anonEmail"]', postForm).val()) {
                    errors.push('anonEmail');
                }
            }

            if (!flagType) {
                errors.push('type');
                return errors;
            }

            if (flagType === 'inappropriate') {
                return errors;
            }

            if (flagType === 'copyright') {
                if (!$('[name="copyrightOriginal"]').val()) {
                    errors.push('copyrightOriginal');
                }

                if (!$('[name="copyrightName"]').val()) {
                    errors.push('copyrightName');
                }

                if (!$('[name="copyrightAddress"]').val()) {
                    errors.push('copyrightAddress');
                }

                if (!$('[name="copyrightPhone"]').val()) {
                    errors.push('copyrightPhone');
                }

                if (!$('[name="copyrightEmail"]').val()) {
                    errors.push('copyrightEmail');
                }

                if (!$('[name="copyrightFaith"]:checked').val()) {
                    errors.push('copyrightFaith');
                }

                if (!$('[name="copyrightAccurate"]:checked').val()) {
                    errors.push('copyrightAccurate');
                }

                if (!$('[name="copyrightSignature"]').val()) {
                    errors.push('copyrightSignature');
                }

                return errors;
            }

            if (!$('[name="otherSubject"]').val()) {
                errors.push('flagOther');
            }

            if (!$('[name="comments"]', postForm).val()) {
                errors.push('comments');
            }

            return errors;
        }

        function validateCommentForm() {
            var errors = [];

            if (!APP.me) {
                if (!$('[name="anonName"]', commentForm).val()) {
                    errors.push('anonName');
                }
                if (!$('[name="anonEmail"]', commentForm).val()) {
                    errors.push('anonEmail');
                }
            }

            return errors;
        }

        $('<a class="flag">')
            .attr('title', 'Report this post')
            .text('Report this spark')
            .click(function (e) {
                e.stopPropagation();
                e.preventDefault();

                if (!$templates) {
                    $templates = $('<div id="templateContainer"/>')
                        .load($.url('/flag/templates'), function () {
                            postForm = $('#flagPost form');
                            commentForm = $('#flagComment form');
                            initializePostForm();
                            initializeCommentForm();
                            showPostDialog();
                        })
                        .appendTo('body');
                        return;
                }

                showPostDialog();
            })
            .appendTo('div.itemDetail .itemShare');

        $('<a class="flag">')
            .attr('title', 'Report this comment')
            .text('Report this comment')
            .click(function (e) {
                var that = $(this);

                e.stopPropagation();
                e.preventDefault();

                if (!$templates) {
                    $templates = $('<div id="templateContainer"/>')
                        .load($.url('/flag/templates'), function () {
                            initializePostForm();
                            initializeCommentForm();
                            showCommentDialog(that.parents('li').attr('data-id'));
                        })
                        .appendTo('body');
                        return;
                }

                showCommentDialog(that.parents('li').attr('data-id'));
            })
            .appendTo('li.comment .commentMeta');
    }

    parent.flag = (function() {
        return {
            init: init
        };
    }());

    return parent;
}(APP || {}, $));

$(function () {
    APP.flag.init();
});
