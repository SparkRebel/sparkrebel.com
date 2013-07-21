/*jslint browser: true, plusplus: true */
/*global $: true, APP: true, parseJSON: true, console: true */

var APP = (function (parent, $) {
    if (parent.admin) {
        return parent;
    }

    var activeItem, // the pseudo id of the admin item currently displayed
        current = [], // the class and id of the item currently shown
        domStack = {}, // An array of dom elements indexed by model and id
        enabled = false,
        ids = {}, // an array of document ids indexed by model
        metaData = {}; // an array of json responses indexed by model and id

    /**
     * jsonToDl
     *
     * Convert a nested json object into a nested dl.
     * Add a class "adminMeta" to (only) the top level dl
     * Add handlers to any li with a child dl to show and hide on click
     *
     * @param mixed data  the data to be converted
     * @param bool  child is it a child dl? the function works recursively, used internally
     */
    function jsonToDl(data, child) {
        var ret, key, value, row, display;

        if (typeof data === 'string') {
            data = parseJSON(data);
        }

        if (!data) {
            return;
        }

        ret = $('<dl />');

        if (!child) {
            ret.addClass('adminMeta');
        }

        function clickHandler(e) {
            var $this;

            e.stopPropagation();

            $this = $(this);

            $this.toggle(function () {
                $this
                    .removeClass('collapsible')
                    .addClass('expandable');
            }, function () {
                $this
                    .removeClass('expandable')
                    .addClass('collapsible');
            });
            $this.next().toggle();
        }

        for (key in data) {
            if (data.hasOwnProperty(key)) {
				if (key === 'display') {
					continue;
				}

                row = $('<dt />').text(key);

                row.appendTo(ret);

                if (data[key] == null) {
                    continue;
                }
                if (typeof data[key] !== 'object') {
                    $('<dd />')
                        .text(data[key])
                        .appendTo(ret);
                } else {
                    row
                        .addClass('collapsible')
                        .click(clickHandler);

                    $('<dd />')
                        .html(jsonToDl(data[key], true))
                        .appendTo(ret);

                }
            }
        }

        return ret;
    }

    /**
     * createAdminContainer
     */
    function createAdminContainer() {
        if ($('#adminData').length) {
            return;
        }

        $('<div id="adminData"><a href="#" class="refresh">↺</a><a href="#" class="close">x</a><h2>Admin Panel</h2><ul id="adminActionContainer"></ul><h3>Record Data</h3><div id="adminMetaContainer"></div></div>')
            .appendTo("body")
            .draggable({handle: 'h2'});

        $('#adminData a.close').click(function () {
            $('#adminData').hide();
        });

        $('#adminData a.refresh').click(function () {
            if (!current) {
                return;
            }
            refreshMetaData(current[0], current[1], function () {
                show(current[0], current[1]);
            });
        });
    }

    /**
     * createAdminToggle
     */
    function createAdminToggle() {
        if ($('#adminToggle').length) {
            return;
        }

        $('<a id="adminToggle" class="disabled" title="Click to toggle">Admin</a>')
            .appendTo("body")
            .click(function() {
                var $this = $(this);

                if ($this.hasClass('disabled')) {
                    enabled = true;
                    $this
                        .removeClass('disabled')
                        .addClass('enabled');

                    parent.admin.init();
                } else {
                    enabled = false;

                    $this
                        .removeClass('enabled')
                        .addClass('disabled');

                    $('#adminData').hide();
                    $('.adminHighlight').removeClass('adminHighlight');
                    $('.hovered').removeClass('hovered');
                }
            });
    }

    /**
     * updateActions
     *
     * @param model $model
     * @param id $id
     */
    function updateActions(model, id) {
        var container = $('#adminActionContainer');

        if (typeof model === 'object') {
            id = model.attr('data-id');
            model = model.attr('data-class');
        }

        container
            .children().remove();

        if (parent.admin[model] === undefined) {
            $.ajax({
                url: '/js/admin/' + model.toLowerCase() + '.js',
                dataType: 'script',
                error: function () {
                    parent.admin[model] = parent.admin.Default(model);
                    parent.admin[model].actions(id, container);
                    console.log('Create the file /js/admin/' + model.toLowerCase() + '.js to customize admin actions');
                },
                success: function () {
                    if (parent.admin[model] === undefined) {
                        parent.admin[model] = parent.admin.Default(model);
                        console.log('The file /js/admin/' + model.toLowerCase() + '.js exists but does not contain a valid admin handler');
                    } else {
                        parent.admin[model].actions(id, container);
                    }
                }
            });
            return;
        }
        parent.admin[model].actions(id, container);
    }

    /**
     * updateMeta
     *
     * @param model $model
     * @param id $id
     */
    function updateMeta(model, id) {
        var $item, display;

        if (typeof model === 'object') {
            $item = model;
            model = $item.attr('data-class');
            id = $item.attr('data-id');
        }

        if (!metaData || !metaData[model] || !metaData[model][id]) {
            return false;
        }

        if (metaData[model][id].display) {
            display = metaData[model][id].display;
        } else {
            display = id;
        }

        $('.adminHighlight').removeClass('adminHighlight');

        if ($item) {
            $item
                .addClass('adminHighlight');
        }

        $('#adminMeta-' + model + '-' + id)
            .show()
            .siblings().hide();

        $('#adminData h2').html(model + ' - ' + display);

        return metaData[model][id];
    }

    /**
     * show admin data for the specific class and id
     *
     * @param model $model
     * @param id $id
     */
    function show(model, id) {
        $('#adminData').show();

        if (typeof model === 'object') {
            current = [model.attr('data-class'), model.attr('data-id')];
        } else {
            current = [model, id];
        }

        updateActions(model, id);
        return updateMeta(model, id);
    }

    /**
     * Return the meta data by model and id
     *
     * @param model $model
     * @param id $id
     */
    function data(model, id) {
        if (!metaData || !metaData[model] || !metaData[model][id]) {
            return false;
        }

        return metaData[model][id];
    }

    /**
     * appendMetaData
     *
     * Add the meta data to the dom, and give it an id so easy finding later
     *
     * @param domElement item The item to attach to
     * @param array      data the json data to use
     */
    function appendMetaData(item, model, id, data) {
        var $item, ul;

        $item = $(item);
        if (!$('#adminMeta-' + model + '-' + id).length) {
            ul = jsonToDl(data);

            if (ul) {
                ul
                    .attr('id', 'adminMeta-' + model + '-' + id)
                    .appendTo($('#adminMetaContainer'));
            }
        }

        $item
            .hover(function (e) {
                var $this = $(this);

                e.stopPropagation();

                if (!enabled) {
                    return;
                }

                $this.addClass('hovered');
                setTimeout(function () {
                    if ($this.hasClass('hovered')) {
                        $this.removeClass('hovered');
                        show($this);
                    }
                }, 1000);
            }, function (e) {
                e.stopPropagation();

                if (!enabled) {
                    return;
                }

                $(this).removeClass('hovered');
            });
    }

    /**
     * getMetaData
     *
     * Search for all items inside the container which have a data-id attribute. Build up a list
     * of Model and ids to process - then bunch up all same-class ajax requests and send to the server
     * Store the response data in a model + id indexed object 'metaData'
     *
     * @param container $container
     * @param callback $callback
     */
    function getMetaData(container, callback) {
        var $item,
            model,
            id,
            i,
            chunk,
            chunksize,
            requestIds,
            missingIds = {};

        $('[data-id]', container).each(function (i, row) {
            $item = $(row);
            model = $item.attr('data-class');
            id = $item.attr('data-id');

            if (!domStack[model]) {
                domStack[model] = {};
                ids[model] = [];
            }
            if (!domStack[model][id]) {
                domStack[model][id] = [];
            }
            if (domStack[model][id].indexOf($item) === -1) {
                domStack[model][id].push($item);
            }
            if (ids[model].indexOf(id) === -1) {
                ids[model].push(id);

                if (!missingIds[model]) {
                    missingIds[model] = [];
                }
                missingIds[model].push(id);
            }
        });

        /**
         * successHandler
         *
         * Response is either an id-indexed array, or a single result
         *
         * @param response $response
         */
        function successHandler(response) {
            var id;

            if (!metaData[model]) {
                metaData[model] = {};
            }

            if (response.id) {
                id = response.id;
                metaData[model][id] = response;
                return;
            }

            for (id in response) {
                if (response.hasOwnProperty(id)) {
                    metaData[model][id] = response[id];
                }
            }
        }

        for (model in ids) {
            if (ids.hasOwnProperty(model)) {
                i = 0;
                chunksize = 30;

                if (missingIds[model]) {
					$.ajax({
						async: false,
						type: 'POST',
						url: $.url('/admin/data/' + model),
						data: JSON.stringify(missingIds[model]),
						success: successHandler
					});
                }
            }
        }

        if (callback) {
            callback();
        }
    }

    /**
     * refreshMetaData
     *
     * Reload the data for one specific item
     * Intended to be called after an action has taken place
     *
     * @param model $model
     * @param id $id
     * @param callback $callback
     */
    function refreshMetaData(model, id, callback) {
        function successHandler(response) {
            if (!metaData[model]) {
                metaData[model] = {};
            }

            metaData[model][id] = response;
        }

        if (!id) {
            id = ids[model].join(',');
        }
        $.ajax({
            async: false,
            url: $.url('/admin/data/' + model + '/' + id),
            success: successHandler
        });

        if (typeof id === 'array') {
            if (callback) {
                callback();
            }

            return;
        }

        if (callback) {
            callback(metaData[model][id]);
        }

        return metaData[model][id];
    }

    /**
     * addMetaData
     *
     * Search for all items inside the container which have a data-id attribute. For any that exist
     * and for which we've already retrieved their metaData - call appendMetaData for that dom element
     *
     * @param container $container
     * @param callback $callback
     */
    function addMetaData(container, callback) {
        $('[data-id]', container).each(function (i, row) {
            var $item, model, id, data;

            $item = $(row);
            model = $item.attr('data-class');
            id = $item.attr('data-id');

            if (metaData && metaData[model] && metaData[model][id]) {
                data = metaData[model][id];
                appendMetaData(row, model, id, data);
            }
        });

        if (callback) {
            callback();
        }
    }

    /**
     * processMetaData
     *
     * For meta data we've already retrieved - add it to the stack of dom elements we have already
     * found
     *
     * @param callback $callback
     */
    function processMetaData(container, callback) {
        var model, id, i, data;

        if (container) {
            $('[data-id]', container).each(function (i, row) {
                var $item = $(row);

                model = $item.attr('data-class');
                id = $item.attr('data-id');

                appendMetaData($item, model, id, data);
            });
        } else {
            for (model in domStack) {
                if (domStack.hasOwnProperty(model)) {
                    for (id in domStack[model]) {
                        if (domStack[model].hasOwnProperty(id) && metaData[model][id]) {
                            data = metaData[model][id];
                            i = domStack[model][id].length;

                            while (i) {
                                i -= 1;
                                appendMetaData(domStack[model][id][i], model, id, data);
                            }
                        }
                    }
                }
            }
        }

        if (callback) {
            callback();
        }
    }

    /**
     * init
     *
     * Set things up
     *
     * @param container $container
     */
    function init(container) {
        createAdminContainer();

        getMetaData(container, function () {
            processMetaData(container);
        });
    }

    parent.admin = {
        data: data,
        init: init,
        refresh: refreshMetaData,
        show: show,
        Default: function (model) {
            return {
                actions: function (id, container) {
                    $('<li />').append(
                        $('<a />')
                        .text('delete')
                        .click(function (e) {
                            e.preventDefault();
                            e.stopPropagation();

                            $.ajax({
                                url: $.url('/admin/delete'),
                                data: {
                                    'model': model,
                                    id: id
                                },
                                type: 'POST',
                                error: function () {
                                    console.log('Error received');
                                },
                                success: function () {
                                    console.log('Success received');
                                }
                            });
                        })
                    ).appendTo(container);
                }
            };
        }
    };

    $(function() {
        createAdminToggle();
    });

    return parent;
}(APP || {}, $));

$(function () {
    var $massAction = $("#massAction"),
        $massSubmit = $("#massSubmit"),
        $selectAll  = $("#selectAll"),
        $checkboxes = $(".datagrid tbody :checkbox");

    function toggleSelectAll() {
        if ($selectAll.is(":checked")) {
            $checkboxes.attr("checked", true);
            $massAction.attr("disabled", false);
            $massSubmit.attr("disabled", false);
            $(".datagrid tbody :checked").parents("tr").addClass("selected");
        } else {
            $(".datagrid tbody :checked").parents("tr").removeClass("selected");
            $(".datagrid tbody :checked").attr("checked", false);
            $massAction.attr("disabled", true);
            $massSubmit.attr("disabled", true);
        }
    }

    $selectAll.click(toggleSelectAll);
    if ($selectAll.is(":checked")) {
        toggleSelectAll();
    }
    if ($checkboxes.length) {
        $selectAll.attr("disabled", false);
    }

    $checkboxes.click(function(){
        if ($(this).is(":checked")) {
            $(this).parents("tr").addClass("selected");
        } else {
            $(this).parents("tr").removeClass("selected");
        }
        if ($massAction.length && $massSubmit.length) {
            if ($(".datagrid tbody :checked").length) {
                $massAction.attr("disabled", false);
                $massSubmit.attr("disabled", false);
            } else {
                $massAction.attr("disabled", true);
                $massSubmit.attr("disabled", true);
            }
        }
    });

    $(".datagrid tbody :checked").parents("tr").addClass("selected");

    if ($massAction.length && $massSubmit.length) {
        if ($(".datagrid tbody :checked").length) {
            $massAction.val("").attr("disabled", false);
            $massSubmit.attr("disabled", false);
        }
    }

    $('.clipboard').live("click", function(e) {
        e.preventDefault();
        window.prompt('Copy to clipboard (CTRL+C):', $(this).data("value"));
    });
});
