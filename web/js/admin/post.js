/*global $: true, APP: true, parseJSON: true, setTimeout: true, console: true */

APP.admin = (function (parent, $) {
	if (parent.Post) {
		return parent;
	}

	parent.Post = (function () {

		function actions(id, container) {
			var i,
				toAppend,
				action,
				data,
				actions = [];

			data = APP.admin.data('Post', id);

            if (data.deleted) {

            } else {
                action = $('<a />').text('Delete').click(function() {
                    $.get('/admin/post/delete/' + id);
                });
                actions.push(action);
            }

			if (container && actions.length) {
				for (i = 0; i < actions.length; i++) {
					actions[i].appendTo(container).wrap('<li />');
				}
			}
			return actions;
		}

		return {
			actions: actions
		}
	}());

	return parent;
}(APP.admin || {}, $));
