function srSort(element) {
    var width = element.width(),
        all = element.data('unsorted') || element.find('li'),
        minw = all.width(),
        total = all.length,
        columns = parseInt(width/minw),
        rows = Math.ceil(total/columns);

    if (!element.data('unsorted')) {
        element.data('unsorted', element.find('li'));
        $(window).resize(function() {
            srSort(element);
        });
    }

    var tmp = [], i=0, x, e,
        container = $('<div>').addClass("sp-container-column"), col;
    for (x=0; x < columns; x++) {
        col = $('<ul>').addClass("sp-container-column").appendTo(container);
        for (e=0; e < rows; e++) {
            col.append(all[i++]);
        }
    }

    element.empty().append(container);
}