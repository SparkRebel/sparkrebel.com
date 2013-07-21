/*global $: true, APP: true, parseJSON: true, setTimeout: true, console: true */

APP.admin = (function (parent, $) {
	if (parent.Merchant) {
		return parent;
	}

	parent.Merchant = (function () {

		function actions(id, container) {
			var i,
				toAppend,
				action,
				data,
				actions = [];

			data = APP.admin.data('Merchant', id);

			if (data.isActive) {
				action = $('<a />')
					.text('Disable')
					.click(function () {
						$.get('/admin/brand/setStatus/' + id + '/0');
					});
			} else {
				action = $('<a />')
					.text('Enable')
					.click(function () {
						$.get('/admin/brand/setStatus/' + id + '/1');
					});
			}
			actions.push(action);

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
    								url: $.url('/admin/feature/brand/' + id + params),
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
						$.get('/admin/unfeature/brand/' + id);
					});
			}
			actions.push(action);

			if (container && actions.length) {
				for (i = 0; i < actions.length; i++) {
					actions[i].wrap('<li />').appendTo(container);
				}
			}
			return actions;
		}

		return {
			actions: actions
		};
	}());

	return parent;
}(APP.admin || {}, $));
