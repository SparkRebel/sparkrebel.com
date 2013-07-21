/*global $: true, APP: true, parseJSON: true, setTimeout: true, console: true */

APP.admin = (function (parent, $) {
	if (parent.Newsletter) {
		return parent;
	}

	parent.Newsletter = (function () {
		var newForm, editForm;

        function testNewsletter(link)
        {
            var dialogDiv = $('<div id="test-newsletter-dialog" title="Newsletter Test"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>' +
                '<form id="testForm" method="POST" action="'+ link.attr('href') +'"> <label for="testEmail">Email:</label><input type="text" name="testEmail" id="testEmail"><br> <label for="justPreview">Just preview:</label><input type="checkbox" name="justPreview" id="justPreview"> </form>' +
                '</p></div>').appendTo($('body'));

            dialogDiv.dialog({
                resizable: false,
                modal: true,
                buttons: {
                    "Send": function() {
                        if ($("#testEmail").val() != '') {
                            $('#testForm').submit();
                        }
                    },
                    Cancel: function() {
                        $(this).dialog( "close" );
                        dialogDiv.remove();
                    }
                }
            });
        }

        function deleteNewsletter(link)
        {
            var dialogDiv = $('<div id="delete-newsletter-dialog" title="Newsletter Delete"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>' +
                'Are you sure you want to delete this newsletter ?' +
                '</p></div>').appendTo($('body'));

            dialogDiv.dialog({
                resizable: false,
                modal: true,
                buttons: {
                    "Delete": function() {
                        document.location.href = link.attr('href');
                    },
                    Cancel: function() {
                        $(this).dialog( "close" );
                        dialogDiv.remove();
                    }
                }
            });
        }

        function sendToIntervalNewsletter(link)
        {
            var dialogDiv = $('<div id="send-newsletter-dialog" title="Newsletter Send to Interval"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>' +
                '<form id="sendForm" method="POST" action="'+ link.attr('href') +'"> <label for="intervalStart">Interval start:</label><input type="text" name="interval[start]" id="intervalStart"><br> <label for="intervalEnd">Interval end:</label><input type="text" name="interval[end]" id="intervalEnd"> <br> <label for="resend">Resend:</label><input type="checkbox" name="resend" id="resend"></form>' +
                '</p></div>').appendTo($('body'));

            dialogDiv.dialog({
                resizable: false,
                modal: true,
                buttons: {
                    "Send": function() {
                        $('#sendForm').submit();
                    },
                    Cancel: function() {
                        $(this).dialog( "close" );
                        dialogDiv.remove();
                    }
                }
            });
        }

        function sendToAllNewsletter(link)
        {
            var dialogDiv = $('<div id="send-newsletter-dialog" title="Newsletter Send to All"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>' +
                'Are you sure you want to send this newsletter to all? <br>' +
                '<form id="sendForm" method="POST" action="'+ link.attr('href') +'"><label for="resend">Resend:</label><input type="checkbox" name="resend" id="resend"></form>' +
                '</p></div>').appendTo($('body'));

            dialogDiv.dialog({
                resizable: false,
                modal: true,
                buttons: {
                    "Send": function() {
                        $('#sendForm').submit();
                    },
                    Cancel: function() {
                        $(this).dialog( "close" );
                        dialogDiv.remove();
                    }
                }
            });
        }

		function init() {

            $('#pw_newsletter_create_newsletter_curatedTop').parent().hide();
            $('#pw_newsletter_create_newsletter_curatedBottom').parent().hide();
            $('#pw_newsletter_create_newsletter_eventsTopBoard').parent().hide();
            $('#pw_newsletter_create_newsletter_eventsBottomBoard').parent().hide();

            topType = $("input[name='pw_newsletter_create[newsletter][topType]']:checked").val();
            bottomType = $("input[name='pw_newsletter_create[newsletter][bottomType]']:checked").val();

            if(topType == 'curated') {
                $('#pw_newsletter_create_newsletter_curatedTop').parent().show();
            }

            if(topType == 'events') {
                $('#pw_newsletter_create_newsletter_eventsTopBoard').parent().show();
            }

            if(bottomType == 'curated') {
                $('#pw_newsletter_create_newsletter_curatedBottom').parent().show();
            }

            if(bottomType == 'events') {
                $('#pw_newsletter_create_newsletter_eventsBottomBoard').parent().show();
            }

            $("input[name='pw_newsletter_create[newsletter][topType]']").click(function(ev){
                if($(this).val() == 'curated') {
                    $('#pw_newsletter_create_newsletter_curatedTop').parent().show();
                }
                else {
                    $('#pw_newsletter_create_newsletter_curatedTop').parent().hide();
                }

                if($(this).val() == 'events') {
                    $('#pw_newsletter_create_newsletter_eventsTopBoard').parent().show();
                }
                else {
                    $('#pw_newsletter_create_newsletter_eventsTopBoard').parent().hide();
                }
            });

            $("input[name='pw_newsletter_create[newsletter][bottomType]']").click(function(ev){
                if($(this).val() == 'curated') {
                    $('#pw_newsletter_create_newsletter_curatedBottom').parent().show();
                }
                else {
                    $('#pw_newsletter_create_newsletter_curatedBottom').parent().hide();
                }

                if($(this).val() == 'events') {
                    $('#pw_newsletter_create_newsletter_eventsBottomBoard').parent().show();
                }
                else {
                    $('#pw_newsletter_create_newsletter_eventsBottomBoard').parent().hide();
                }
            });

            $('.actions a[test=true]').click(function(ev) {
                testNewsletter($(ev.target));

                return false;
            });

            $('.actions a[delete=true]').click(function(ev) {
                deleteNewsletter($(ev.target));

                return false;
            });

            $('.actions a[sendToInterval=true]').click(function(ev) {
                sendToIntervalNewsletter($(ev.target));

                return false;
            });

            $('.actions a[sendToAll=true]').click(function(ev) {
                sendToAllNewsletter($(ev.target));

                return false;
            });
        }

		$(function() {
			init();
		});

		return {
			init: init
		}
	}());

	return parent;
}(APP.admin || {}, $));
