$(document).ready(function() {
    var _storeCategories = {};
    var _storeInitialSearch = {};
    
    var _storeData = {};
    var _searchResults = {};
    
    var _defaultSearchDisplay = {
        count: 30,
        page: 0
    };
    var _searchDisplay = $.extend({}, _defaultSearchDisplay);
    
    var _storeConfig = {
        priceMax: 500,
        priceDefault: '0,500',
        
        priceArray: function(val) { return (val ? val.split(',') : this.priceDefault.split(',')); }
    };
    
    var _priorState = {};
    var _previousStoreSelection = false;
    var _storeSelection = {
        category: 0,
        subcategory: '',
        searchFilter: 'all',
        searchText: '',
        price: _storeConfig.priceDefault,
        store: ''
    };
    
    //Manipulating the user interface
    function setSearchPriceValues(val) {
        val = _storeConfig.priceArray(val);
        
        $('#shopSearchPriceSlider').slider({values: val});
        _storeSelection.price = val.join(',');
    }
    
    function setItemsFilter(val) {
        $('input:radio[name=shopSearchFilter][value=' + val + ']').attr('checked', true);
        _storeSelection.searchFilter = val;
    }
    
    function showSubcategorySelection(subcat) {
        $('.shopSubcategories li').removeClass('current');
        
        if (subcat === undefined) {
            subcat = '';
        }
        
        $('.shopSubcategories #subcat_' + subcat).addClass('current');
    }
    
    function sbOverlayItemsSelect(type, items) {
        var list = $('#shopOverlay_' + type);
        
        list.removeClass('selected');
        if (items) {
            $.each(items.split(','), function(idx, item) {
                var li = $('#' + item, list);
                if (li.length) {
                    userHitOverlayItem(li);
                }
            });
        }
    }
    
    
    //User Interaction
    function userHitItemsFilter(val) {
        _storeSelection.searchFilter = val;
        
        shopDoSearch();
    }
    
    function userHitCategoryResetSearch() {
        sbOverlaySelectedRead(false);
        $('#shopSearchPriceSlider').slider({values: _storeConfig.priceArray()});
    }
    
    function userHitResetSearch() {
        //clear all user data and trigger a new search.
        resetPagination();
        
        setItemsFilter('all');
        
        $('#shopSearchInput').val('');
        
        userHitCategoryResetSearch();
        
        shopCategorySelected(false, true); //this triggers a search
    }
    
    function userHitPaginatorSize(id) {
        var match = /^count_(\d+)/.exec(id);
        
        setPaginatorCount(parseInt(match[1]));
        
        shopDoSearch();
    }
    
    function userHitPagination(page) {
        var originalPage = _searchDisplay.page;
        
        if(_searchDisplay.count > 0) {
            if (page === 'first') {
                _searchDisplay.page = 0;
            } else if (page === 'prev') {
                if(--_searchDisplay.page < 0) {
                    _searchDisplay.page = 0;
                }
            } else if (page === 'next') {
                if(((_searchDisplay.page + 1) * _searchDisplay.count) < _searchResults.total) {
                    _searchDisplay.page++;
                }
            } else {
                _searchDisplay.page = page;
            }
            
            if(_searchDisplay.page != originalPage) {
                shopDoSearch();
            }
        }
    }
    
    function userRemovedSBSelection(t, id) {
        var tmp = [];
        
        $.each(_storeSelection[t].split(','), function(idx, elt) {
            if (elt != id) {
                tmp.push(elt);
            }
        });
        
        _storeSelection[t] = tmp.join(',');
        
        shopDoSearch();
    }
    
    function userUpdatedSBOverlay() {
        var changed = sbOverlaySelectedRead(true);
        if (changed) {
            shopDoSearch();
        }
    }
    
    function userHitOverlayItem(item) {
        $(item).toggleClass('selected');
    }
    
    function userChangedStoreBrandFilter(filterText) {
        if (!String.prototype.trim) {
           //code for trim in IE
           String.prototype.trim=function(){return this.replace(/^\s\s*/, '').replace(/\s\s*$/, '');};
        }
        
        filterText = filterText.trim().toLowerCase();
        
        if (filterText == '') {
            $('#shopOverlay_store li').show();
        } else {
            var item, show;
            var i;
            
            for (var x in _storeData.store) {
                item = _storeData.store[x];
                
                i = $('#' + x);
                
                show = (i.hasClass('selected') || (item.name.toLowerCase().indexOf(filterText) != -1));
                
                i.toggle(show);
            }
        }
    }
    
    function shopDoSearch() {
        //the user directly triggered an action that will cause a search
        _storeSelection.price = $('#shopSearchPriceSlider').slider('values').join(',');
        _storeSelection.searchText = $('#shopSearchInput').val();
        
        _storeSelection.from = _searchDisplay.page * _searchDisplay.count;
        _storeSelection.size = _searchDisplay.count;
        
        shopSearch(_storeSelection);
    }
    
    function setShopCategorySelection(cat, subcat) {
        _storeSelection.category = cat;
        _storeSelection.subcategory = subcat;
    }
    
    //will trigger search
    function userHitSubcategory(cat, subcat) {
        showSubcategorySelection(subcat.slug);
        
        setShopCategorySelection(cat.slug, subcat.slug);
        
        shopDoSearch();
    }
    
    //can trigger search
    function shopCategorySelected(cat, fromUser) {
        function _addSubcategory(subcat) {
            var listOpts = {
                id: 'subcat_' + subcat.slug
            };
            
            if (!subcat.id) {
                listOpts['class'] = 'current';
            }
            
            list.append($('<li>', listOpts).html($('<a>').text(subcat.name).click(function(e) {
                e.preventDefault();
                
                userHitSubcategory(cat, subcat);
            })));
        }
        
        //Hilight this category and deactivate any others
        $('.shopSearchCategories li').removeClass('selected');
        $('.shopSearchCategories').find('#shopCategory_' + cat.slug).parent().addClass('selected');
        
        var title = $('#shopCategoryTitle');
        var list = $('#shopSubcategories');
        
        list.html('');
        
        if (!cat) {
            title.html('');
            
            if (fromUser) {
                setShopCategorySelection(0, '');
            }

            $('.shopSearchCategories li').removeClass('selected');
        } else {
            title.html(cat.name);
            
            if (fromUser) {
                setShopCategorySelection(cat.slug, '');
            }
            
            _addSubcategory({name: 'All ' + cat.name, id: 0, slug: ''});
            
            for (var subcat in cat.subcategories) {
                _addSubcategory(cat.subcategories[subcat]);
            }
        }
        
        if (fromUser) {
            shopDoSearch();
        }
    }
    
    function userHitCategory(cat) {
        userHitCategoryResetSearch();
        
        shopCategorySelected(cat, true);
    }
    
    
    
    //Init our custom paginator renderer
    $.PaginationRenderers.srRenderer = function(maxentries, opts) {
        this.maxentries = maxentries;
        this.opts = opts;
        this.pc = new $.PaginationCalculator(maxentries, opts);
    };
    $.PaginationRenderers.srRenderer.prototype = new $.PaginationRenderers.defaultRenderer;
    
    $.extend($.PaginationRenderers.srRenderer.prototype, {
        parent: $.PaginationRenderers.defaultRenderer.prototype,
        
        getLinks: function(current_page, eventHandler) {
            var fragment = this.parent.getLinks.call(this, current_page, eventHandler);
            var a = this.createLink(0, current_page, {text:"First", classes:"first"});
            a.click(eventHandler);
            
            fragment.prepend(a);
            
            return fragment;
        }
    });
    
    //Displaying Data
    function sbSetPriceSliderText(values) {
        $('#shopSearchPrice').val('$' + values[0] + ' - $' + values[1]);
    }
    
    function setPaginatorCount(count) {
        //adjust the page as appropriate
        if (!count || _searchDisplay.count == 0) {
            _searchDisplay.page = 0;
        } else {
            _searchDisplay.page = Math.floor(_searchDisplay.page * (_searchDisplay.count / count));
        }
        
        _searchDisplay.count = count;
        
        $('.itemsCount a').removeClass('current');
        $('#count_' + count).addClass('current');
    }
    
    function setPaginator(searchResults, searchDisplay) {
        var str = '';
        
        if (searchResults.total && searchResults.total > 0) {
            str = '(' + searchResults.total + ' total)';
        }
        
        $('.paginationContainer').toggle(searchResults.total > 0);
        
        $('#shopItems .totalItems').text(str);
        
        var num = (searchResults.total ? searchResults.total : 0);
        
        $('.pagination').pagination(num, {
            items_per_page: (searchDisplay.count ? searchDisplay.count : num),
            current_page: searchDisplay.page,
            num_display_entries: 5,
            num_edge_entries: 2,
            link_to: "#",
            ellipse_text: "...",
            
            renderer: 'srRenderer',
            
            callback:function(newCurrentPage, containers){
                userHitPagination(newCurrentPage);
                return false;
            }
        });
    }
    
    function displaySearchResults(searchResults) {
        if (!searchResults.items) {
            return;
        }
        
        _searchDisplay.page = Math.floor(searchResults.from / searchResults.size);
        
        var list = $('#itemsContainer');
        list.html('');
        for (var x = 0; x < searchResults.items.length; x++) {
            var item = searchResults.items[x];
            var li;
            
            li = $('<li>').append(
                $('<div class="product">')
                    .append('<a>', {href:'#'}).html($('<img>', {src:item.imagePrimary}))
                    .append(
                        $('<div class="productOverlay">')
                            .append($('<a>', {href:item.buyUrl, 'class':'button buy', target:'_blank'}).text('Buy'))
                            .append($('<a>', {href:item.repostUrl, 'class':'button repost'}).text('Respark'))
                            .append($('<a>', {href:item.postUrl, 'class':'button moreInfo', target:'_blank'}).text('More Info'))
                    )
            )
            .append(
                $('<div class="itemInformation">').append(
                    $('<a>', {href: item.postUrl})
                        .append($('<span class="itemBrand">').text(item.merchant))
                        .append($('<span class="itemPrice">').text(item.price))
                )
            );
            
            list.append(li);
        }
        
        $('#noSearchResults').toggle(searchResults.items.length == 0);
        
        setPaginator(searchResults, _searchDisplay);
    }
    
    function displayCategories() {
        var list = $('#shopSearchCategories');
        var bigList = $('#shopBigCategories');
        
        function _addCategory(cat) {
            function _hitCategory(e) {
                e.preventDefault();
                
                userHitCategory(cat);
            }
            
            list.append($('<li>').html($('<a>', {'id': 'shopCategory_' + id}).text(cat.name).click(_hitCategory)));
            
            bigList.append($('<li>').html($('<a>')
                .append($('<img>', {src:'/images/shop/categories/' + cat.slug + '.jpg'}))
                .append($('<h3>').text(cat.name))
                ).click(_hitCategory));
        }
        
        for (var id in _storeCategories) {
            _addCategory(_storeCategories[id]);
        }
    }
    
    function displayOverlayFilters(data, newData) {
        var types = ['store'];
        for (var x = 0; x < types.length; x++) {
            var type = types[x];
            var list = $('#shopOverlay_' + type);
            
            if (newData) {
                list.html('');
                for (var id in data[type]) {
                    var sb = data[type][id];
                    
                    $(list[0]).append(
                        $('<li>', {'id': id}).html($('<a>').text(sb.name).append(' ').append($('<span>').text('(' + sb.count + ')')))
                    );
                }
                
                list.find('li').click(function(e) {
                    userHitOverlayItem(this);
                });
                
                $('#shopPopupBrandsInput').change();
            } else {
                list.find('li').removeClass('selected');
            }
            
            if (_storeSelection[type]) {
                $.each(_storeSelection[type].split(','), function(idx, elt) {
                    list.find('#' + elt).addClass('selected');
                });
            }
        }
    }
    
    
    //Search
    function sbOverlaySelectedRead(readValues) {
        function _addSBSelection(list, t, sb) {
            var li = $('<li>').text(sb.name);
            var a = $('<a>', {id: t + '_' + sb.id}).text('X').click(function(e){
                userRemovedSBSelection(t, sb.id);
                
                $(this).parent().remove();
            });
            
            list.append(li.append(a));
        }
        
        var list = $('#filtersStores');
        list.html('');
        
        var needSearch = false;
        
        var types = ['store'];
        for (var x = 0; x < types.length; x++) {
            var newSelection = [];
            var t = types[x];
            
            if (readValues) {
                var selected = $('#shopOverlay_' + t + ' li.selected');
                $(selected).each(function(idx, val) {
                    var a = $(val).find('a');
                    var sb = {id: val.id, name: a.text()};
                    
                    _addSBSelection(list, t, sb);
                    newSelection.push(sb.id);
                });
            }
            
            newSelection = newSelection.join(',');
            if (!needSearch) {
                needSearch = (newSelection != _storeSelection[t]);
            }
            
            _storeSelection[t] = newSelection;
        }
        
        return needSearch;
    }
    
    function resetPagination() {
        _searchDisplay = $.extend({}, _defaultSearchDisplay);
    }
    
    function _shopSearchCompare(search, oldSearch)
    {
        var bcFields = {
            store: 1,
            from: 'pagination',
            size: 'pagination'
        };
        var need = {
            search: false,
            data: false,
            pagination: false,
            anything: false
        };
        
        if (!oldSearch) {
            need.search = need.data = true;
        } else {
            for (var f in search) {
                if(search[f] != oldSearch[f])
                {
                    if(bcFields[f] === 'pagination') {
                        need.pagination = 1;
                    } else {
                        need.search = 1;
                        
                        if(bcFields[f] === undefined)
                            need.data = 1;
                    }
                }
            }
        }
        
        need.anything = need.search + need.data + need.pagination;
        
        return need;
    }
    
    function shopSearch(search, initialSearch) {
        var need = _shopSearchCompare(search, _previousStoreSelection);
        
        if(need.anything)
        {
            //If we have requested a state change, the pagination has to be reset, unless it's what changed
            if(need.search && !initialSearch) {
                search.from = 0;
                search.size = _defaultSearchDisplay.count;
            }
            
            var state = {
                searchTerms: search,
                initialSearch: initialSearch
            };
            
            var url = _storeInit.basePath;
            if(search.category)
            {
                url +='/' + search.category;
                
                if(search.subcategory)
                    url += '/' + search.subcategory;
                
                var params = {};
                
                if(search.searchFilter && search.searchFilter != 'all')
                    params.searchFilter = search.searchFilter;
                if(search.searchText)
                    params.searchText = search.searchText;
                if(search.price && search.price != _storeConfig.priceDefault)
                    params.price = search.price;
                if(search.store)
                    params.store = search.store;
                if(search.from)
                    params.from = search.from;
                if(search.size >= 0 && search.size != _defaultSearchDisplay.count)
                    params.size = search.size;
                
                if(!$.isEmptyObject(params)) {
                    url += '?' + $.param(params);
                }
            }
            
            var title = document.title;
            
            if(_previousStoreSelection)
            {
                var State = History.getState();
                
                _priorState[State.hash] = {
                    searchTerms: State.data.searchTerms,
                    storeData: _storeData,
                    searchResults: _searchResults
                };
                
                History.pushState(state, title, url);
            }
            else
            {
                //This is the first search for the page, populated from the initial search.
                //We set the state accordingly, then do the search that has been requested
                History.replaceState(state, title, url);
                _historyStateChanged();
            }
        }
        
        _previousStoreSelection = $.extend({}, search);
    }
    
    function _setStoreSelection(searchTerms) {
        shopCategorySelected(_storeCategories[searchTerms.category], false);
        showSubcategorySelection(searchTerms.subcategory);
        
        setItemsFilter(searchTerms.searchFilter);
        
        $('#shopSearchInput').val(searchTerms.searchText);
        
        setSearchPriceValues(searchTerms.price);
        
        sbOverlayItemsSelect('store', searchTerms.store);
        sbOverlaySelectedRead(true);
    }
    
    function _showStoreState(state, needSearch) {
        var searchTerms = state.searchTerms;
        
        _storeSelection = searchTerms;
        
        if (!searchTerms.category) {
            $('#shopSearch').hide();
            $('#shopCategories').show();
        } else {
            $('#shopCategories').hide();
            $('#shopSearch').show();
            
            if (needSearch) {
                getStoreDataForSearch(searchTerms, state.initialSearch);
                _shopSearch(searchTerms);
            } else {
                getStoreDataForSearchResponse(state.storeData);
                _shopSearchResponse(state.searchResults);
                _setStoreSelection(state.searchTerms);
            }
        }
    }
    
    function _historyStateChanged() {
        var State = History.getState();
        
        if(_priorState[State.hash]) {
            var ps = _priorState[State.hash];
            
            _showStoreState(ps);
        } else {
            _showStoreState(State.data, true);
        }
    }
    
    function _parseQueryString(queryString) {
        var e,
            a = /\+/g,  // Regex for replacing addition symbol with a space
            r = /([^&=]+)=?([^&]*)/g,
            d = function (s) { return decodeURIComponent(s.replace(a, " ")); };
        
        var urlParams = {};
        while (e = r.exec(queryString))
           urlParams[d(e[1])] = d(e[2]);
        
        return urlParams;
    }
    
    function shopSearchInit(storeInit) {
        //Check and see if we have a hash, then do something about i
        var State = History.getState();
        var hash = State.hash;
        var matches;
        
        if(matches = hash.match(new RegExp('shop(/[^?]*)([?](.*))?'))) {
            //1 = path, 3 = query string
            var query = _parseQueryString(matches[3]);
            var path = matches[1].split('/');
            
            if (path[1]) {
                query.category = path[1];
                if (path[2]) {
                    query.subcategory = path[2];
                }
            }
            
            shopSearch(query, true);
        } else { 
            shopSearch(storeInit.initialSearch, true);
        }
    }
    
    
    //Data Query Responses
    
    function _shopSearchResponse(data) {
        _searchResults = data;
        
        displaySearchResults(data);
    }
    
    function getStoreDataForSearchResponse(data) {
        _storeData = data;
        
        displayOverlayFilters(data, true);
    }
    
    
    
    //Data query operations
    
    function _shopSearch(search) {
        $.ajax({
            url: _storeInit.basePath + '/search',
            'type': 'POST',
            dataType: 'json',
            data: {
                search: search
            },
            success: _shopSearchResponse
        });
    }
    
    function getStoreDataForSearch(searchTerms, initialSearch) {
        $.ajax({
            url: _storeInit.basePath + '/data',
            'type': 'POST',
            dataType: 'json',
            data: {
                search: searchTerms
            },
            success: function(data) {
                getStoreDataForSearchResponse(data);
                if(initialSearch) {
                    _setStoreSelection(searchTerms);
                }
            }
        });
    }
    
    
    
    //jQuery Events
    $(window).bind('statechange', _historyStateChanged);
    
    $('.itemSpotfolios ul, .itemPictureOverlayBottom ul').jcarousel({
        scroll: 1
    });
    
    $(".itemSpotfolios ul a").fancybox({
        fitToView	: false,
        width       : '70%',
        autoSize    : false,
        closeClick	: false,
        loop        : false
    });
    
    $('#shopForm').submit(function(e){
        e.preventDefault();
        
        shopDoSearch();
    });
    
    $('#shopSearchSubmit').click(function(e){
        e.preventDefault();
        
        shopDoSearch();
    });
    
    $('#shopSearchReset, #shopSearchResetLink').click(function(e){
        e.preventDefault();
        
        userHitResetSearch();
    });
    
    $('.shopSidebarStoresLink').click(function(e){
        e.preventDefault();
        
        displayOverlayFilters(_storeData, false);
        
        $('#shopPopupBrands').fadeIn('fast');
        $(document).on('keyup.pwSPB', function(e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            
            if(code == 27) {
                e.preventDefault();
                $('#shopOverlayCancel').click();
            }
        });
    });
    
    $('#shopPopupBrands .button').click(function(e){
        e.preventDefault();
        $('#shopPopupBrands').fadeOut('fast');
        
        if (this.id == 'shopOverlayOK') {
            userUpdatedSBOverlay();
        }
        
        $(document).unbind('keyup.pwSPB');
    });
    
    $('#shopPopupBrandsInput').change(function(e) {
        userChangedStoreBrandFilter(this.value);
    });
    
    $('#shopPopupBrandsInput').keyup(function(e) {
        userChangedStoreBrandFilter(this.value);
    });
    
    $('.itemsCount a').click(function(e){
        e.preventDefault();
        
        userHitPaginatorSize(this.id);
    });
    
    $('input:radio[name=shopSearchFilter]').click(function(e){
        userHitItemsFilter(this.value);
    });
    
    $('.shopSidebarColorsLink').click(function(e){
      e.preventDefault();
      $('#shopPopupColors').fadeIn('fast');
    });
    $('#shopPopupColors .button').click(function(e){
      e.preventDefault();
      $('#shopPopupColors').fadeOut('fast');
    });
    
    //init
    function _priceSliderFn(event, ui) {
        sbSetPriceSliderText(ui.values);
    }
    
    $('#shopSearchPriceSlider').slider({
        range: true,
        min: 0,
        max: _storeConfig.priceMax,
        values: _storeConfig.priceArray(_storeSelection.price),
        slide: _priceSliderFn,
        change: _priceSliderFn,
        stop: function(e, ui) {
            shopDoSearch();
        }
    });
    
    sbSetPriceSliderText(_storeConfig.priceArray(_storeSelection.price));
    
    _storeCategories = _storeInit.categories;
    displayCategories();
    
    shopSearchInit(_storeInit);
});
