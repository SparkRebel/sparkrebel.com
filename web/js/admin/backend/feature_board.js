/*global $: true, APP: true, parseJSON: true, setTimeout: true, console: true */

APP.admin = (function (parent, $) {
	if (parent.FeatureBoard) {
		return parent;
	}

	parent.FeatureBoard = (function () {
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
			editForm = $('#editFeatureBoard form');

			editForm.submit(function(e) {
				var data;

				e.preventDefault();

				data = editForm.serializeArray();

				$.post(
					editForm.attr('action'),
					data,
					function(response) {
						if (response.status === 'ok') {
							tr.find('td:nth-child(4)').html(response.data.priority);
							tr.find('td:nth-child(5)').html(response.data.start.substr(0, 10));
							tr.find('td:nth-child(6)').html(response.data.end.substr(0, 10));

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
				.load($.url('/admin/feature/board/templates'), function () {
					initEditForm();
				})
				.appendTo('body');

			$('a.edit').click(function(e) {
				e.preventDefault();

				tr = $(this).parents('tr');

				editForm.find('h2').text(tr.find('td:nth-child(2)').text());

				editForm.find('#featureId').val(tr.find('input').val());
				editForm.find('#featurePriority').val(tr.find('td:nth-child(4)').html());
				editForm.find('#featureStart').val(tr.find('td:nth-child(5)').attr('title'));
				editForm.find('#featureEnd').val(tr.find('td:nth-child(6)').attr('title'));

				showEditForm();
			});
			
			$('a.delete').click(function(e) {
			   e.preventDefault();
			   
			   tr = $(this).parents('tr');

               $.post(
                 $.url('/admin/feature/board/delete/' + tr.find('input').val()),
                 null,
                 function(response) {
                     if (response.status === 'ok') {
                         tr.slideUp('slow', function() {
                             tr.remove();
                         });
                     }
                 }
                );
							   
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
