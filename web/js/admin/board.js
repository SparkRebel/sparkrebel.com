/*global $: true, APP: true, parseJSON: true, setTimeout: true, console: true */

APP.admin = (function (parent, $) {
	if (parent.Board) {
		return parent;
	}

	parent.Board = (function () {
		var areas;

		$(function () {
			$.ajax({
				url: $.url('/admin/data/area/*'),
				success: function(response) {
					areas = response;
				}
			});
		});

		function actions(id, container) {
			var i,
				toAppend,
				action,
				data,
				actions = [];

			data = APP.admin.data('Board', id);

            if (data.deleted) {

            } else {
                action = $('<a />').text('Delete').click(function() {
                    $.get('/admin/board/delete/' + id);
                });
                actions.push(action);
            }

			if (true) {
    			action = $('<a />')
    				.text('Feature')
    				.click(function () {
    					var aid, form, input, label;

    					form = $('<form />');
    					$('<label>Priority (0 = first)</label>')
    					    .appendTo(form);
    					$('<input type="text" />')
    					    .attr('name', 'priority')
    					    .attr('value', '5')
    					    .appendTo(form);
    					$('<br /><br />')
    					    .appendTo(form);
						$('<label>From (YYYY-MM-DD)</label>')
    					    .appendTo(form);
						$('<input type="text" />')
    					    .attr('name', 'start')
    					    .attr('value', '')
    					    .appendTo(form);
						$('<br /><br />')
    					    .appendTo(form);
						$('<label>To (YYYY-MM-DD)</label>')
    					    .appendTo(form);
						$('<input type="text" />')
    					    .attr('name', 'end')
    					    .attr('value', '')
    					    .appendTo(form);
    					$('<br /><br />')
    					    .appendTo(form);
    					$('<input type="submit" />')
    						.attr('value', 'Submit')
    						.click(function (e) {
    							e.preventDefault();
    							var pri= $('form input[name="priority"]').val();
    							var start= $('form input[name="start"]').val();
    							var end= $('form input[name="end"]').val();
    							
    							if (isNaN(pri)) {
    							    alert('priority must be a number');
    							    return false;
    							}
    							
    							var params = '?priority=' + pri + '&start=' + start + '&end=' + end;

    							$.ajax({
    								url: $.url('/admin/feature/board/' + id + params),
    								success: function() {
    									form.dialog('close');
    									form.remove();
    								}
    							});
    						})
    						.appendTo(form);

    					form.dialog({
    					    width: 400,
    					    height: 300,
    						title: 'Please set feature detailes for "' + data.display + '"'
    					});
    				});
			} else {
				action = $('<a />')
					.text('Unfeature')
					.click(function () {
						$.get('/admin/unfeature/board/' + id);
					});
			}
			actions.push(action);

			action = $('<a />')
				.text('Add to area')
				.click(function () {
					var aid, form, input, label;

					form = $('<form />');

					for (aid in areas) {
						if (areas.hasOwnProperty(aid)) {
							input = $('<input type="radio" />');
							input
								.attr('id', 'Area' + aid)
								.attr('value', aid)
								.attr('name', 'areaSelect')
								.appendTo(form);

							label = $('<label />');
							label
								.attr('for', 'Area' + aid)
								.text(areas[aid].name)
								.appendTo(form);

							$('<br />').appendTo(form);
						}
					}

					$('<input type="submit" />')
						.attr('value', 'Submit')
						.click(function (e) {
							e.preventDefault();
							aid = $('form input[name="areaSelect"]:checked').val();
						    if (aid == '') {
							    alert('You must select an area');
							    return false;
							}
							$.ajax({
								url: $.url('/admin/area/addBoard/' + aid + '/' + id),
								success: function() {
									form.dialog('close');
									form.remove();
								}
							});
						})
						.appendTo(form);

					form.dialog({
						title: 'Select which area to associate "' + data.name + '" with'
					});
				});
			actions.push(action);

			action = $('<a />')
				.text('Set Admin Score')
				.click(function () {
					var aid, form, input, label;

					form = $('<form />');
					$('<input type="text" />')
					    .attr('name', 'adminScore')
					    .attr('value', '')
					    .appendTo(form);

					$('<input type="submit" />')
						.attr('value', 'Submit')
						.click(function (e) {
							e.preventDefault();
							var val= $('form input[name="adminScore"]').val();
							if (isNaN(val)) {
							    alert('Score must be a number');
							    return false;
							}

							$.ajax({
								url: $.url('/admin/setField/Board/' + id + '/adminScore/' + val),
								success: function() {
									form.dialog('close');
									form.remove();
								}
							});
						})
						.appendTo(form);

					form.dialog({
						title: 'Please insert the new admin score for "' + data.display
					});
				});
			actions.push(action);


            action = $('<a />').text('Fix Count').click(function() {
                $.get($.url('/admin/board/syncCount/' + id));
            });
            actions.push(action);


			if (container && actions.length) {
				i = actions.length;
				while (i) {
					i -= 1;
					actions[i].wrap('<li />').appendTo(container);
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
