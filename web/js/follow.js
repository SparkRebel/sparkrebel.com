/*global $: true, APP: true */

var APP = (function (app, $) {
	if (app.follow) {
		return;
	}

	app.follow = (function () {
		var myFollows;

		function updateCounter(inc) {
			var i = $('#followerCount');
			i.text(Math.max(0, parseInt(i.text(), 10) + inc));
		}

		function bindLinks(container) {
			$('a.follow, a.unfollow', container).click(function (e) {
				var $this = $(this);

				e.preventDefault();

				if (!APP.me) {
					APP.login.start();
					return false;
				}

				$this.addClass('loading');

				$.ajax({
					url: this.href,
					context: $this,
					error: function (response) {
						this.removeClass('loading');
					},
					success: function (response) {
                        var targetType = response.data.type;
						this.removeClass('loading');
						if (response && response.result === 'ok') {
							if (this.hasClass('follow')) {
								this
									.removeClass('follow')
									.addClass('unfollow')
									.attr('href', this.attr('href').replace('/follow/', '/unfollow/'))
									.text('Un' + this.text());
								if (this.hasClass('userProfile')) {
									updateCounter(1);
								}
                                if (targetType) {
                                    _gaq.push(['_trackEvent', 'User', 'Followed', targetType.capitalize()]);
                                } else {
                                    _gaq.push(['_trackEvent', 'User', 'Followed']);
                                }
							} else {
								this
									.removeClass('unfollow')
									.addClass('follow')
									.attr('href', this.attr('href').replace('/unfollow/', '/follow/'))
									.text(this.text().substr(2));
								if (this.hasClass('userProfile')) {
									updateCounter(-1);
								}
                                if (targetType) {
                                    _gaq.push(['_trackEvent', 'User', 'Unfollowed', targetType.capitalize()]);
                                } else {
                                    _gaq.push(['_trackEvent', 'User', 'Unfollowed']);
                                }
							}
						}
					}
				});

				return false;
			});
		}

		function processMyFollows() {
			if (!APP.me) {
				$('a.follow').show();
				return false;
			}
			
			if (myFollows === undefined) {
				$.ajax({
					url: $.url("/member/following/" + APP.me.id),
					success: function (data) {
						myFollows = data;
						myFollows = jQuery.parseJSON(myFollows);
						if (myFollows) {
							processMyFollows();
						}
					}
				});
				return;
			}
			
			$('a.follow').each(function (i, follow) {
				var match, followType, id, $follow;

				match = follow.id.match(/-([a-z]*)-([a-f0-9]{24})$/);
				
				if (!match) {
					return;
				}
				
				followType = match[1];
				id = match[2];
				
				$follow = $(follow);
				if (id === APP.me.id) {
					$follow
						.removeClass('follow')
						.attr('href', '#')
						.text('That\'s you!')
						.unbind('click')
						.click(function () {
							return false;
						});
					return;
				}
				
				if (myFollows[followType] && myFollows[followType].indexOf(id) > -1) {
					$follow
						.removeClass('follow')
						.addClass('unfollow')
						.attr('href', $follow.attr('href').replace('/follow/', '/unfollow/'))
						.text('Un' + $follow.text());
				}
				$follow.show();
			});
		}

		return {
			bind: bindLinks,
			personalize: processMyFollows,
			init: function () {
				processMyFollows();
				bindLinks();
			}
		};
	}());

	return app;
}(APP || {}, $));

$(function () {
	APP.follow.init();
});
