/*global $: true, APP: true, parseJSON: true, setTimeout: true, console: true */

APP.admin = (function (parent, $) {
	if (parent.Alias) {
		return parent;
	}

	parent.Alias = (function () {
		var editForm, tr, id, name, aliases;

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
			editForm = $('#editAlias form');

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
								tr.find('td:nth-child(2)').html(response.data.name);
								tr.find('td:nth-child(3)').html(response.data.aliases);
							} else {
								tr = $('<tr><td>&nbsp;</td><td>:name:</td><td>:aliases:</td><td>&nbsp;</td></tr>'
									.replace(':name:', response.data.name)
									.replace(':aliases:', response.data.aliases)
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
				.load($.url('/admin/alias/templates'), function () {
					initEditForm();
				})
				.appendTo('body');

			$('<a />')
				.html('New Alias')
				.prependTo('div.massactions')
				.click(function(e) {
					e.preventDefault();

					id = false;

					editForm.find('input[name=aliasId]').val('');
					editForm.find('input[name=aliasName]').val('');
					editForm.find('textarea').val('');

					showEditForm();
				});
			$('a.edit').click(function(e) {
				e.preventDefault();

				tr = $(this).parents('tr');

				id = tr.find('input').val();
				name = tr.find('td:nth-child(2)').html();
				aliases = tr.find('td:nth-child(3)').html();

				editForm.find('input[name=aliasId]').val(id);
				editForm.find('input[name=aliasName]').val(name);
				editForm.find('textarea').val(aliases);

				showEditForm();
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
