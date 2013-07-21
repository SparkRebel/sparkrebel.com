/*global $: true, APP: true, parseJSON: true, setTimeout: true, console: true */

APP.admin = (function (parent, $) {
	if (parent.Asset) {
		return parent;
	}

	parent.Asset = (function () {
		function actions(id, container) {
			var i, toAppend, action, data;
			data = APP.admin.data('Asset', id);
			return [];
		}

		return {
			actions: actions
		};
	}());

	return parent;
}(APP.admin || {}, $));
