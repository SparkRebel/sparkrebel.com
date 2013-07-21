//
// Post Form
var boardCategoryMap = {};

var APP = (function (parent, $) {
    if (parent.posts) {
        return parent;
    }

    var defaultBoardDisplay = 'newBoard',
        repostState;

    function _getModalWidth(width) {
        var modalWidth = 300;
        if ($('#content').width() > 300) {
            modalWidth = width;
        }
        return modalWidth;
    }

    function _createAddDialog($element, boardDisplay, respark) {
        // Sort boards
        $("#pw_post_create_post_board").sortSelect();

        if (boardDisplay != 'existingBoard' && boardDisplay != 'newBoard') {
            boardDisplay = defaultBoardDisplay;
            if (APP.me.counts && APP.me.counts.boards > 1) {
                boardDisplay = 'existingBoard';
            }
        }
        // Make it easier on Users spamming the reSpark button
        if (boardDisplay == 'existingBoard') {
            if (APP.session.last_board) {
                $("#pw_post_create_post_board").val(APP.session.last_board);
            }
            if (APP.session.last_category) {
                $("#pw_post_create_post_category").val(APP.session.last_category);
            }
        }
        $element.dialog({
            closeOnEscape: !APP.config.query.popup,
            autoOpen: true,
            modal: true,
            dialogClass: "modalWindow postModal " + boardDisplay,
            closeText: (APP.config.query.popup ? '' : "X"),
            width: _getModalWidth(780),
            draggable: false,
            resizable: false,
            position: (APP.config.query.popup ? ['center', 20] : ''),
            open: function(event, ui) {
                _gaq.push(['_trackEvent', 'Dialog', 'Show', (respark ? 'Respark' : 'Spark')]);
            },
            close: function(event, ui) {
                _gaq.push(['_trackEvent', 'Dialog', 'Hide', (respark ? 'Respark' : 'Spark')]);
                $element.remove();
            }
        });
        repostState = boardDisplay;
        setupRepostDialog($element);
        return $element;
    }
    
    function _createResparkDialog($element, $button) {
        $element.dialog({
            closeOnEscape: true,
            autoOpen: true,
            modal: false,
            dialogClass: "modalWindow resparkModal",
            closeText: "X",
            width: 220,
            minHeight: 100,
            position: positionResparkDialog($element, $button),
            draggable: false,
            resizable: false,
            open: function(event, ui) {
                // Prevent links from auto-focusing
                $(":focus").blur();
                _gaq.push(['_trackEvent', 'Dialog', 'Show', 'Respark']);
            },
            close: function(event, ui) {
                if ($("#stream").length) {
                    $button.parents(".postActions").css("visibility", "");
                } else {
                    $button.parents(".itemPictureOverlayTop").css("visibility", "");
                }
                $button.removeClass("pressed");
                $element.off('clickoutside');
                _gaq.push(['_trackEvent', 'Dialog', 'Hide', 'Respark']);
            }
        });
        setupResparkDialog($element, $button);
        return $element;
    }
    
    function _createLoveitDialog($element, $button) {
        $element.dialog({
            closeOnEscape: true,
            autoOpen: true,
            modal: false,
            dialogClass: "modalWindow loveitModal",
            closeText: "X",
            width: 220,
            minHeight: 100,
            position: positionLoveitDialog($element, $button),
            draggable: false,
            resizable: false,
            open: function(event, ui) {
                // Prevent links from auto-focusing
                $(":focus").blur();
                _gaq.push(['_trackEvent', 'Dialog', 'Show', 'Love It']);
            },
            close: function(event, ui) {
                if ($("#stream").length) {
                    $button.parents(".postActions").css("visibility", "");
                } else {
                    $button.parents(".itemPictureOverlayTop").css("visibility", "");
                }
                $button.removeClass("pressed");
                $element.off('clickoutside');
                _gaq.push(['_trackEvent', 'Dialog', 'Hide', 'Love It']);
            }
        });
        setupLoveitDialog($element, $button);
        return $element;
    }
    
    function positionResparkDialog($element, $button) {
        var $xPosition,
            $yPosition,
            $dialogWidth = 220,
            $dialogHeight = 280;
        $element.removeClass("modalOnTop");
        $xPosition = $button.offset().left - ($dialogWidth / 2) + ($button.outerWidth() / 2);
        $yPosition = ($button.offset().top + $button.outerHeight() + 23) - $(document).scrollTop();
        if ($yPosition + $dialogHeight >= $(window).height()) {
            $yPosition = $button.offset().top - ($(document).scrollTop() + 23 + $dialogHeight);
            $element.addClass("modalOnTop");
        }
        return [$xPosition, $yPosition];
    }
    
    function positionLoveitDialog($element, $button) {
        var $xPosition,
            $yPosition,
            $dialogWidth = 220,
            $dialogHeight = 155;
        $element.removeClass("modalOnTop");
        $xPosition = $button.offset().left - ($dialogWidth / 2) + ($button.outerWidth() / 2);
        $yPosition = ($button.offset().top + $button.outerHeight() + 23) - $(document).scrollTop();
        if ($yPosition + $dialogHeight >= $(window).height()) {
            $yPosition = $button.offset().top - ($(document).scrollTop() + 23 + $dialogHeight);
            $element.addClass("modalOnTop");
        }
        return [$xPosition, $yPosition];
    }

    /**
     * Bind to all ReSpark links
     */
    function setupRepostLinks() {
        $(".repost").live("click", function(e) {
            e.preventDefault();

            if($(e.currentTarget).hasClass('waiting') === false) {
                $(e.currentTarget).addClass('waiting');
                var boardDisplay = $(this).data('boardDisplay');
                $(".postModal").children().remove();
                $(".postModal").remove();
                $.get($(this).attr("href"), function(html) {
                    $(e.currentTarget).removeClass('waiting');
                    var $container = $("<div/>")
                        .appendTo("body")
                        .hide()
                        .html(html);
                    _createAddDialog($container, boardDisplay, true);
                }, 'html');
            }

           
        });
    }
    
    /**
     * Bind to all Respark links
     */
    function setupResparkLinks() {
        $(".respark").live("click", function(e) {
            var self = this;
            e.preventDefault();
            if (!APP.me) {
                APP.login.start();
            } else {

                $container = $("#resparkModalContainer");
                $container.find("#resparkForm").css("opacity", "1");
                $container.find("#resparkModalLoading").hide();
                $container.find("#resparkModalSuccess").hide();
                $container.find(".timer").text("5");
                $container.find('textarea').val($(this).data('description'))


                if ($("#stream").length) {
                    $(this).parents(".postActions").css("visibility", "visible");
                } else {
                    $(this).parents(".itemPictureOverlayTop").css("visibility", "visible");
                }
                
                $(this).addClass("pressed");
                
                _createResparkDialog($container, $(this));

            }    
            
        });
    }
    
    /**
     * Bind to all Love It links
     */
    function setupLoveitLinks() {
        $(".loveit").live("click", function(e) {
            e.preventDefault();
            
            if (!APP.me) {
                APP.login.start();
            } else {
                $container = $("#loveitModalContainer");
                $container.find("#loveitForm").css("opacity", "1");
                $container.find("#loveitModalLoading").hide();
                $container.find("#loveitModalSuccess").hide();
                $container.find(".timer").text("5");
                
                if ($("#stream").length) {
                    $(this).parents(".postActions").css("visibility", "visible");
                } else {
                    $(this).parents(".itemPictureOverlayTop").css("visibility", "visible");
                }
                
                $(this).addClass("pressed");
                
                _createLoveitDialog($container, $(this));
            }    
            
        });
    }

    /**
     * If we're on the post add page, setup the dialog
     */
    function setupPostAddPage() {
        var $postAddContainer = $("#postAddContainer");
        if ($postAddContainer.length) {
            _createAddDialog($postAddContainer);
        }
    }

    /**
     * Show error message in respark dialog
     */
    function _setRepostError($status, message) {
        $("#postModalLoading").hide();
        $("#addPostForm").find('.primary').removeClass("disabled");
        $status.text(message);
        $status.removeClass("success").addClass("error").slideDown("fast");
    }

    /**
     * Handle form submission for new board creation
     * during resparking
     */
    function _handleNewBoardForm($container, $form, $status) {
        if (!$("#pw_board_create_board_category").val()) {
            _setRepostError($status, 'Collection must have a Category.');
            return;
        }
        $.post($.url('/collection/add'), $form.serialize(), function(json) {
            if (json.success || json.duplicate) {
                if (!json.duplicate) {
                    _gaq.push(['_trackEvent', 'Collection', 'Created', json.id]);
                }
                APP.session.created_board = true;
                $("#pw_post_create_post_category").val($("#pw_board_create_board_category").val());
                $("#pw_post_create_post_board").append($('<option>', {'value': json.id}).text(json.name));
                $("#pw_post_create_post_board").val(json.id);
                _handleNewPostForm($container, $form, $status);
            } else {
                if (json.error) {
                    _setRepostError($status, json.error);
                } else {
                    _setRepostError($status, 'An error occurred. Please try again...');
                }
           }
        }).error(function() {
            _setRepostError($status, 'An error occurred. Please try again...');
        });
    }

    /**
     * Handle form submission for resparking
     */
    function _handleNewPostForm($container, $form, $status) {
        if (!$("#pw_post_create_post_category").val()) {
            _setRepostError($status, 'Spark must have a Category.');
            return;
        }
        $.post($form.attr("action"), $form.serialize(), function(json) {
            if (json.success) {
                if (json.facebook_attachment) {
                    json.facebook_attachment.method = 'feed';
                    FB.ui(json.facebook_attachment, function(response){
                        _handleNewPostSuccess(json, $container);
                        if (!response || !response.post_id) {
                            // @todo Handle unsuccessful facebook post
                        }
                    });
                } else {
                    _handleNewPostSuccess(json, $container);
                }
            } else {
                if (json.error) {
                    _setRepostError($status, json.error);
                } else {
                    _setRepostError($status, 'An error occurred. Please try again...');
                }
            }
        }).error(function() {
            _setRepostError($status, 'An error occurred. Please try again...');
        });
    }

    /**
     * Handle a successful sparking
     */
    function _handleNewPostSuccess(post, $container)
    {
        if (post.type) {
            _gaq.push(['_trackEvent', 'Spark', 'Created', post.type.capitalize()]);
        } else {
            _gaq.push(['_trackEvent', 'Spark', 'Created']);
        }
        APP.session.last_board    = post.board.id || false;
        APP.session.last_category = post.board.category.id || false;
        $container.find('#postModalSuccess .boardLink').attr('href', post.board.url).text(post.board.name);
        $container.find('#postModalSuccess .sparkLink').attr('href', post.redirect);
        $container.find('#addPostForm').animate({opacity: 0}, 'fast');
        $container.find('#postModalSuccess').fadeIn('fast', function(){
            var timeout = 10,
                $timer  = $container.find('#postModalSuccess .timer');
            function countdown() {
                if (timeout <= 1) {
                    clearInterval(interval);
                    if (APP.config.query.popup) {
                        self.close();
                    } else {
                        $container.dialog('close');
                    }
                } else {
                    $timer.text(--timeout);
                }
            }
            var interval = setInterval(countdown, 1000);
        });
    }

    /**
     * Setup the dialog for resparking
     */
    function setupRepostDialog($container)
    {
        var $form = $("#addPostForm");
        $form.submit(function(e) {
            e.preventDefault();
            var $status = $form.children(".status");
            $status.slideUp("fast");
            $("#postModalLoading").show();
            if (repostState != 'existingBoard') {
                _handleNewBoardForm($container, $form, $status);
            } else {
                _handleNewPostForm($container, $form, $status);
            }
        });

        $form.find('.primary').click(function(e) {
            e.preventDefault();
            if($(this).hasClass("disabled") === false) {
                $(this).addClass("disabled");
                $form.submit();
            }
            
        });

        $form.find('.back').click(function(e) {
            e.preventDefault();
            $container.dialog('close');
        });

        //
        // New/Existing Board Toggle
        var $modalTitle        = $('#modalTitle'),
            $showExistingBoard = $form.find('.show_board_existing'),
            $existingBoard     = $form.find('.board_existing'),
            $showNewBoard      = $form.find('.show_board_new'),
            $board             = $form.find('.board');
            $successDialog     = $('#postModalSuccess');

        $showExistingBoard.find('a').click(function(e) {
            e.preventDefault();
            repostState = 'existingBoard';
            if ($('#content').width() > 300) {
                $successDialog.css('height', '230px');
            } else {
                $successDialog.css('height', '296px');
            }
            $showExistingBoard.fadeOut('fast');
            $board.slideToggle(function() {
                $existingBoard.slideToggle();
                $showNewBoard.fadeIn('fast');
            });
        });

        $showNewBoard.find('a').click(function(e) {
            e.preventDefault();
            repostState = 'newBoard';
            $modalTitle.html('Create a new collection&hellip;');
            if ($('#content').width() > 300) {
              $successDialog.css('height', '350px');
            } else {
              $successDialog.css('height', '645px');
            }
            $showNewBoard.fadeOut('fast');
            $existingBoard.slideToggle(function() {
                $board.slideToggle();
                $showExistingBoard.fadeIn('fast');
            });
        });
    }
    
    /**
     * Setup the dialog for respark
     */
    function setupResparkDialog($container, $button)
    {
        var post_id = $button.data('id');
        $container.find('.primary').unbind('click');

        $container.find('.primary').click(function(e) {
            var $clicked = $(this);
            e.preventDefault();

            if($clicked.hasClass("disabled") === false) {
                $clicked.addClass("disabled");     
                $('#resparkModalLoading').show();
                $('#resparkModalError').hide();
                $form = $('#resparkForm');
                $.post($button.data("href"), $form.serialize(), function(resp) {    
                    $clicked.removeClass("disabled");            
                    $('#resparkModalLoading').hide();
                    if(resp.success === true) {

                        if (APP.me && APP.me.hasCuratorRole) {
                            $container.find('#resparkModalSuccess h2').html('You are curator. Your sparks will be processed and they will appear soon in the stream.');
                            $container.find('#resparkModalSuccess').find('.sparkLink').remove();
                        } else {
                            $container.find('#resparkModalSuccess').find('.sparkLink').attr('href', resp.redirect);
                        } 

                        $container.find('#resparkForm').animate({opacity: 0}, 'fast');
                        

                        $container.find('#resparkModalSuccess').find('.boardLink')
                            .attr('href', resp.board.url)
                            .text(resp.board.name);
                        
                        
                        $container.find('#resparkModalSuccess').fadeIn('fast', function(){
                            var timeout = 5,
                                $timer  = $container.find('#resparkModalSuccess .timer');
                            function countdown() {
                                if (timeout <= 1) {
                                    clearInterval(interval);
                                    $container.dialog('close');
                                } else {
                                    $timer.text(--timeout);
                                }
                            }
                            var interval = setInterval(countdown, 1000);
                        })

                        var json = resp;
                        if (json.facebook_attachment) {
                            json.facebook_attachment.method = 'feed';
                            FB.ui(json.facebook_attachment, function(response){                                
                                if (!response || !response.post_id) {
                                    // @todo Handle unsuccessful facebook post
                                }
                            });
                        }

                        _gaq.push(['_trackEvent', 'Spark', 'Created', 'repost']);

                    } else {
                        $('#resparkModalError').html(resp.error).show('fast');                    
                    }
                });
            }

            
                        
        });
        
        $container.find('.customSelectBoxCreate a.button').unbind('click');

        $container.find('.customSelectBoxCreate a.button').click(function(e) {
            e.preventDefault();
            var $clicked = $(this);
            $form = $('#resparkForm');
            $('#newCollectionErrors').hide();

            if($clicked.hasClass("disabled") === false) {
                $clicked.addClass("disabled");     
                $.post($.url('/collection/add'), $form.serialize(), function(json) {
                    $clicked.removeClass("disabled");     
                    if (json.success || json.duplicate) {
                        if (!json.duplicate) {
                            //@edu what to do here?
                            _gaq.push(['_trackEvent', 'Collection', 'Created']);
                        }
                        APP.session.created_board = true;
                        $("#pw_repost_create_post_board").append($('<option>', {'value': json.id}).text(json.name));
                        $("#pw_repost_create_post_board option").removeAttr("selected");
                        $("#pw_repost_create_post_board option[value='" + json.id + "']").attr("selected", "selected");
                        $el = $('<span class="customSelectBoxOption" data-value="'+json.id+'">'+json.name+'</span>');
                        $container.find('.customSelectBoxOptions').prepend($el);
                        $el.trigger('click');
                        $("#pw_board_create_board_name").val("");


                    } else {
                        if (json.error) {
                            $('#newCollectionErrors').html(json.error).show('fast');                    
                        } else {
                            $('#newCollectionErrors').html('An error occurred. Please try again...').show('fast');                                            
                        }
                   }
                }).error(function() {
                    $('#newCollectionErrors').html('An error occurred. Please try again...').show('fast');                                            
                });
            }    
        });

        $container.on('clickoutside', function(e){
            if ($("#stream").length) {
                $button.parents(".postActions").css("visibility", "");
            } else {
                $button.parents(".itemPictureOverlayTop").css("visibility", "");
            }
            $button.removeClass("pressed");
            $(this).dialog('close');
        });
        
		$container.find(".customSelectBox").each(function(){
            $(this).find(".customSelectBoxSelected").html($(this).find(".customSelectBoxOptions").find(".customSelectBoxOption:first").html());
            $(this).find("#pw_repost_create_post_board option:first").attr("selected", "selected");
			
			$(this).find(".customSelectBoxOptions").css("display", "none");
			
            $(this).find(".customSelectBoxSelected, .customSelectBoxArrow").unbind("click");
            $(this).find(".customSelectBoxSelected, .customSelectBoxArrow").click(function(e){
                if ($(this).parent().find(".customSelectBoxOptions").css("display") == "none") {
                    $(this).parent().find(".customSelectBoxOptions").css("display", "block");
                } else {
                    $(this).parent().find(".customSelectBoxOptions").css("display", "none");
                }
            });
            
            $(this).find(".customSelectBoxOption").unbind("click");
            $(this).find(".customSelectBoxOption").live("click", function(){
                $(this).parent().css("display", "none");
                $(this).parent().siblings(".customSelectBoxSelected").html($(this).html());
                $(this).closest('.customSelectBox').find("#pw_repost_create_post_board option").removeAttr("selected");
                $(this).closest('.customSelectBox').find("#pw_repost_create_post_board option[value='" + $(this).data("value") + "']").attr("selected", "selected");
            });
        });
    }

    /**
     * Setup the dialog for love it
     */
    function setupLoveitDialog($container, $button)
    {
        var post_id = $button.data('id');
        $container.find('#loveitModalTags a').unbind('click');
        $container.find('#loveitModalTags a').click(function(e) {
            e.preventDefault();
            $('#loveitModalLoading').show();
            $.post($(e.currentTarget).data('url'), { id: post_id },  function(data) {
                $('#loveitModalLoading').show();
                data = $.parseJSON(data);
                if (data.status === 'success') {
                    
                    $container.find('#loveitModalSuccess').find('.sparkLink').attr('href', data.path);

                    if (APP.me && APP.me.hasCuratorRole) {
                        $container.find('#loveitSuccessContent').html('<h2>You just <span>&hearts;</span> this!</h2><p style="color:white;">You are curator. Your sparks will be processed and they will appear soon in the stream.</p>');
                    } 
                    $container.find('#loveitForm').animate({opacity: 0}, 'fast');
                    $container.find('#loveitModalSuccess').fadeIn('fast', function(){
                        var timeout = 5,
                            $timer  = $container.find('#loveitModalSuccess .timer');
                        function countdown() {
                            if (timeout <= 1) {
                                clearInterval(interval);
                                $container.dialog('close');
                            } else {
                                $timer.text(--timeout);
                            }
                        }
                        var interval = setInterval(countdown, 1000);
                    });
                    _gaq.push(['_trackEvent', 'Spark', 'Created', 'heart']);
                } else {
                    alert('There was a problem with tagging. Please try again.')
                }
            });

            
        });
        
        $container.on('clickoutside', function(e){
            if ($("#stream").length) {
                $button.parents(".postActions").css("visibility", "");
            } else {
                $button.parents(".itemPictureOverlayTop").css("visibility", "");
            }
            $button.removeClass("pressed");
            $(this).dialog('close');
        });
    }

    function setupCommenting() {
         // Bind <a> to submit form
         $(".postCommentForm").find(".button").live("click", function(e){
             e.preventDefault();
             $(this).submit();
         });

        // Submit form when user pressed Enter key
        $(".postCommentForm").find("textarea").keypress(function(e){
          if (e.which == 13) {
            e.preventDefault();
            $(this).submit();
          }
        });

         // Submit comments via AJAX
         $(".postCommentForm").live("submit", function(e){
             e.preventDefault();            
             var _elem = $(this);
             $(this).find(".button").text('Posting...');
             
             var $form         = $(this),
                 $postActivity = $form.parent().siblings(".postActivity"),
                 data          = $form.serialize();
             $.post($(this).attr("action"), data, function(html) {
                 _elem.find(".button").text('Comment');
                 $stream = $('#stream');
                 $form.find("textarea").val("");
                 if ($stream.length) {
                     // Remove class for post without comments
                     $postActivity.parent().removeClass('noComments');
                     // Get the post ID
                     $alteredBrick = $postActivity.parents('.post').attr('data-id');
                     // Add the new comment before the "See all X comments"
                     $commentsMoreCount = $stream.find('.post[data-id=' + $alteredBrick + ']').find('.postCommentsMore a span');
                     if ($commentsMoreCount.length) {
                         $commentsMoreCount.text(parseInt($commentsMoreCount.text()) + 1);
                         $commentsMoreCount.parents('.postCommentsMore').before(html);
                     } else {
                         $postActivity.append(html);
                     }
                     // Update comments count
                     $commentsCount = $stream.find('.post[data-id=' + $alteredBrick + ']').find('.postCount span');
                     $commentsCount.text(parseInt($commentsCount.text()) + 1);
                 } else {
                     $postActivity.append(html);
                     /* TO-DO: Add the number of comments to the response to update the <h3>X Comments</h3> */
                 }
             }, 'html');
         });

         // Bind <a> to show reply form
         $(".commentMeta").find(".replyButton").live("click", function(e) {
             e.preventDefault();
             if (APP.me) {
                 $(this).parents('.comment').find('.activityReply').slideToggle();
             } else {
                 APP.login.start();
             }
         });

         // Handles comment/activity replies
         $(".activityReply").find("form").live("submit", function(e){
             e.preventDefault();
             var $replyLi = $(this).parents(".activityReply"),
                 $input   = $(this).children(".inputField");
             $.post($(this).attr("action"), $(this).serialize(), function(html) {
                 $replyLi.before(html);
                 $input.val("");
                 $replyLi.slideToggle();
             }, 'html');
         });

         $(".postActivity > li:last").addClass("last");
    }

    /**
     * Creates the REPSARK guider (does not show)
     */
    function getResparkGuider(attachTo) {
        return guiders.createGuider({
            attachTo: attachTo,
            autoFocus: false,
            buttons: [{
                name: "X",
                onclick: guiders.hideAll
            }],
            description: "Click this button to save it to your <strong>COLLECTIONS</strong>. You can access your <strong>COLLECTIONS</strong> by clicking on <a href=\"" + $.url("/member/collections") + "\"><strong>My Collections</strong></a>.",
            id: "guider-respark",
            position: 7,
            title: "Like this <strong>SPARK</strong>?",
            width: ($('#content').width() > 300 ? 430 : 250),
            onShow: function() {
                _gaq.push(['_trackEvent', 'Guider', 'Show', 'Respark', null, true]);
                $.get($.url('/guider/seen/respark'));
                APP.session.guiders.respark = new Date().getTime();
            },
            onHide: function() {
                _gaq.push(['_trackEvent', 'Guider', 'Hide', 'Respark', null]);
                $(".itemPicture, .post").removeClass("static");
            }
        });
    }
    
    /**
     * Creates the FOLLOW COLLECTION guider (does not show)
     */
    function getFollowCollectionGuider(attachTo) {
        return guiders.createGuider({
            attachTo: attachTo,
            autoFocus: false,
            buttons: [{
                name: "X",
                onclick: guiders.hideAll
            }],
            description: "Follow it and when new sparks are added you will see them in your <a href=\"" + $.url("/") + "\">STREAM</a>.",
            id: "guider-respark",
            position: ($('#content').width() > 300 ? 5 : 7),
            title: "Like this <strong>COLLECTION</strong>?",
            width: ($('#content').width() > 300 ? 430 : 250),
            onShow: function() {
                /*
                _gaq.push(['_trackEvent', 'Guider', 'Show', 'Respark', null, true]);
                $.get($.url('/guider/seen/respark'));
                APP.session.guiders.respark = new Date().getTime();
                */
            },
            onHide: function() {
                /* _gaq.push(['_trackEvent', 'Guider', 'Hide', 'Respark', null]); */
            }
        });
    }

    /**
     * Setup Guider (tooltip helpers)
     */
    function setupGuiders() {
        var threshold = APP.config.request_time - (10 * 60),
            showGuiders = { respark: true };

        // Check User's counts first
        if (APP.me) {
            if (APP.me.counts.reposts) {
                showGuiders.respark = false;
            } else if (APP.me.settings.viewed_guiders && APP.me.settings.viewed_guiders.respark) {
                if (APP.me.settings.viewed_guiders.respark > threshold) {
                    showGuiders.respark = false;
                }
            }
        } else {
            showGuiders.respark = false;
        }

        // Then check their session
        if (showGuiders.respark) {
            if (!APP.session.guiders) {
                APP.session.guiders = { respark: false }
            } else {
                if (APP.session.guiders.respark) {
                    if (APP.session.guiders.respark > threshold) {
                        showGuiders.respark = false;
                    }
                }
            }
        }
        
        /*
        if (showGuiders.respark) {
            //
            // Stream ReSpark
            if ($(".post").length > 0) {
                $(".post").hover(function(){
                    var self = this;
                    $(this).data('timer', setTimeout(function(){
                        if (guider = getResparkGuider($(self).find('.repost'))) {
                            $(".post").unbind("mouseenter mouseleave");
                            $(self).addClass("static");
                            guider.show();
                        }
                    }, 5 * 1000));
                }, function() {
                    if (timer = $(this).data('timer')) {
                        $(this).data('timer', clearTimeout(timer));
                    }
                });
            }

            //
            // Spark View ReSpark
            if ($(".itemPictureOverlayTop").length > 0) {
                console.log(showGuiders.respark);
                setTimeout(function(){
                    $(".itemPictureOverlayTop").fadeIn(function(){
                        $(".itemPicture").addClass("static");
                        if (guider = getResparkGuider(".repost")) {
                            guider.show();
                        }
                    });
                }, 5 * 1000);
            }
        }
        */
        
        
    }
    
    function showCollectionGuider() {
        //
        // Collection Follow
        /*
        if ($(".boardToolbar .follow").length > 0) {
            setTimeout(function(){
                if (guider = getFollowCollectionGuider(".boardToolbar .follow")) {
                    guider.show();
                }
            }, 5 * 1000);
        }
        */
    }
    
    /**
     * Prevent right click on Celebs posts.
     */
    function disableRightClickForCelebs() {
        $('.celebImage').on('contextmenu', 'img', function(e) {
            return false;
        });
    }

    function init() {
        /* setupRepostLinks(); */
        setupResparkLinks();
        setupLoveitLinks();
        setupCommenting();
        setupGuiders();
        disableRightClickForCelebs();

        $("#pw_post_create_post_board").live("change", function(){
            if (typeof boardCategoryMap != 'undefined') {
                var boardId = $(this).val();
                if (boardCategoryMap[boardId]) {
                    $("#pw_post_create_post_category").val(boardCategoryMap[boardId]);
                }
            }
        });
        
        $('#stream .postActions .comment').live("click", function(e){
          e.preventDefault();
          if (APP.me) {
              $post = $(this).parents('.post');
              if (!$post.find('.postComments').hasClass('open')) {
                $post.find('.postComments').addClass('open');
                $post.find('.postComment').show();
              }
              $post.find('.postComment textarea').focus();
              $(window).scrollTop($post.find('.postComment textarea').offset().top + $post.find('.postComment textarea').height() - $(window).height() + 60);
  		    } else {
              APP.login.start();
  		    }
        });
    }

    parent.posts = (function() {
        return {
            init: init,
            loadPostDialog: setupPostAddPage,
            showCollectionGuider: showCollectionGuider
        };
    }());

    return parent;
}(APP || {}, $));

$(function() {
    APP.posts.init();
});

$(function() {

	if (!$('div.itemDetail').length) {
		return;
	}

    $("#activityList > li:last").addClass("last");

    //
    // Handles adding comments
    var $commentsAddForm = $("#commentsAddForm");

    // Load session data back into form
    var sessionForm = 'commentsAddForm';
    if ($.cookie(sessionForm)) {
        $commentsAddForm.unserializeForm($.cookie(sessionForm));
        $.cookie(sessionForm, null);
    }

    /*
    $commentsAddForm.find(".button").click(function(e){
        e.preventDefault();
        $commentsAddForm.submit();
    });

    $commentsAddForm.submit(function(e){
        e.preventDefault();
        var data = $(this).serialize();
        $(this).find('.loading').show();
        $.post(
            $(this).attr("action"),
            data,
            function(html) {
                $("#activityList").append(html);
                $commentsAddForm.find("#pw_post_comment_content").val("");
                $commentsAddForm.find('.loading').hide();
            },
            'html'
        ).error(function() {
            $.cookie(sessionForm, data);
        });
    });
    */

    // "More items in this board" paginator
    $('.itemPictureOverlayBottom .jcarousel').jcarousel();
    
    $scrollAmount = $('.itemPictureOverlayBottom .jcarousel .jcarousel-item-fullyvisible').length;
    
    $('.itemPictureOverlayBottom .jcarousel-next').jcarouselControl({
        target: '+=' + $scrollAmount + ''
    });
    
    $('.itemPictureOverlayBottom .jcarousel-prev').jcarouselControl({
        target: '-=' + $scrollAmount + ''
    });

    // Boards widget
    $('.itemBoards .jcarousel').jcarousel({
      list  : ">ul:eq(0)",
      items : ">li"
    });
    
    $('.itemBoards .jcarousel-next').jcarouselControl({
        target: '+=1'
    });
    
    $('.itemBoards .jcarousel-prev').jcarouselControl({
        target: '-=1'
    });
});


