/*global $: true, APP: true, parseJSON: true, setTimeout: true, console: true */

APP.admin = (function (parent, $) {
	if (parent.User) {
		return parent;
	}

	parent.User = (function () {

		function actions(id, container) {
			var i,
				toAppend,
				action,
				data,
				actions = [];

			data = APP.admin.data('User', id);

            action = $('<a />')
                .text('Morph')
                .attr("href", $.url('/?_morph=' + data.username));
            actions.push(action);

            if (data.deleted) {

            } else {
                action = $('<a />').text('Delete').click(function() {
                    $.get('/admin/user/delete/' + id);
                });
                actions.push(action);
            }

			if (data.enabled) {
				action = $('<a />')
					.text('Disable')
					.click(function() {
						$.get('/admin/user/setStatus/' + id + '/0');
					});
			} else {
				action = $('<a />')
					.text('Enable')
					.click(function() {
						$.get('/admin/user/setStatus/' + id + '/1');
					});
			}
			actions.push(action);

			if (data.roles && data.roles.indexOf('ROLE_ADMIN') === -1) {
				action = $('<a />')
					.text('Make Admin')
					.click(function() {
						$.get('/admin/user/addRole/' + id + '/ROLE_ADMIN');
					});
			} else {
				action = $('<a />')
					.text('Remove Admin')
					.click(function() {
						$.get('/admin/user/removeRole/' + id + '/ROLE_ADMIN');
					});
			}
			actions.push(action);


			action = $('<a />')
				.text('Build stream')
				.click(function() {
					if (confirm('This will wipe users stream and build it from scratch. Are You sure?')) {
						$.post('/admin/user/buildStream/' + id);
					}
					
				});
			actions.push(action);			

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
