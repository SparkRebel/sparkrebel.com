/*global $: true, APP: true, parseJSON: true, setTimeout: true, console: true */

APP.admin = (function (parent, $) {
	if (parent.Whitelist) {
		return parent;
	}

	parent.Whitelist = (function () {
		var editForm, tr, id, name;

        function showAddForm() {
			editForm
				.find('input, textarea').val('');

			return showEditForm();
		}

        function showEditForm() {
            editForm.dialog({
                width: 400,
                closeOnEscape: true,
                closeText: 'X',
                dialogClass: 'modalWindow',
                draggable: true,
                resizable: true
            });
        }

		function initEditForm() {
			editForm = $('#editWhitelist form');

			editForm.submit(function(e) {
				var data;

				e.preventDefault();

				data = editForm.serializeArray();

				$.post(
					editForm.attr('action'),
					data,
					function(response) {
						if (response.status === 'ok') {
							if (id) {
								tr.find('input').val(response.data.id);
								tr.find('td:nth-child(2)').html(response.data.id);
							} else {
								tr = $('<tr><td>&nbsp;</td><td>:name:</td><td>&nbsp;</td></tr>'
									.replace(':name:', response.data.name)
								 );

								tr
									.appendTo('table.datagrid tbody')
							}

							tr.children()
								.stop()
								.effect('highlight', {}, 3000);

							editForm.dialog('close');
						}
					}
				);
			});
		}

		function init() {
			$('<div id="templateContainer"/>')
				.load($.url('/admin/whitelist/templates'), function () {
					initEditForm();
				})
				.appendTo('body');

			$('<a />')
				.html('New Whitelist')
				.prependTo('div.massactions')
				.click(function(e) {
					e.preventDefault();

					id = false;

					editForm.find('input[name=whitelistId]').val('');
					editForm.find('input[name=whitelistName]').val('');
					editForm.find('textarea').val('');

					showEditForm();
				});

			$('a.edit').click(function(e) {
				e.preventDefault();

				tr = $(this).parents('tr');

				id = tr.find('input').val();
				name = tr.find('td:nth-child(2)').html();

				editForm.find('input[name=whitelistId]').val(id);
				editForm.find('input[name=whitelistName]').val(name);

				showEditForm();
			});

			$('a.delete').click(function(e) {
				e.preventDefault();

				tr = $(this).parents('tr');

				id = tr.find('input').val();

				$.ajax({
					type: 'DELETE',
					url: $(this).attr('href'),
					success: function(response) {
						if (response.status === 'ok') {
							tr.fadeOut(1000, function() {
								tr.remove();
							});
						}
					}
				});
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
