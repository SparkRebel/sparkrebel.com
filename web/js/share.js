//
// @todo Handle the event when no images are found on the page
// @todo Handle event after a successful or failed spark
//

(function () {

    //
    // Helpers
    //
    var $ = function(a,b){a=a.match(/^(\W)?(.*)/);return(b||document)["getElement"+(a[1]?a[1]=="#"?"ById":"sByClassName":"sByTagName")](a[2])}

    $.isEmpty = function(obj) {
        for (var prop in obj) {
            if (obj.hasOwnProperty(prop))
                return false;
        }
        return true;
    };

    $.popup = function(url, width, height, name) {
        width  = width || 830;
        height = height || 545;
        var left = (screen.width - width) / 2,
            top  = (screen.height - height) / 2;
        var params  = 'width=' + width + ', height=' + height;
            params += ', top=' + top + ', left=' + left,
            params += ', directories=no, location=no, menubar=no',
            params += ', resizable=no, scrollbars=no, status=no, toolbar=no';
        var popup = window.open(url, name || 'sparkrebel__window', params);
        if (window.focus) {popup.focus();}
        return popup;
    };

    $.hasHost = function(h) {
        var p = document.location.hostname.toLowerCase().split(".");
        var len = p.length;
        for (var i = 0; i < len; i++) {
            if (p[i] == h) {
                return true;
            }
        }
        return false;
    };

    $.query = function(q) {
        var e,
            a = /\+/g,
            r = /([^&=]+)=?([^&]*)/g,
            d = function(s) {return decodeURIComponent(s.replace(a, " "));},
            urlParams = {};
        while (e = r.exec(q)) {
            urlParams[d(e[1])] = d(e[2]);
        }
        return urlParams;
    };

    $.bind = (function(window, document) {
        if (document.addEventListener) {
            return function(elem, type, cb) {
                if ((elem && !elem.length) || elem === window) {
                    elem.addEventListener(type, cb, false);
                }
                else if (elem && elem.length) {
                    var len = elem.length;
                    for (var i = 0; i < len; i++) {
                        addEvent(elem[i], type, cb);
                    }
                }
            };
        }
        else if (document.attachEvent) {
            return function (elem, type, cb) {
                if ((elem && !elem.length) || elem === window) {
                    elem.attachEvent('on' + type, function() {
                        return cb.call(elem, window.event)
                    });
                }
                else if (elem.length) {
                    var len = elem.length;
                    for (var i = 0; i < len; i++) {
                        addEvent(elem[i], type, cb);
                    }
                }
            };
        }
    })(this, document);

    $.append = function(e, parent) {
        return parent ? parent.appendChild(e) : document.body.appendChild(e);
    };

    $.after = function(e, sibling) {
        if (sibling.nextSibling) {
            sibling.parentNode.insertBefore(e, sibling.nextSibling);
        } else {
            sibling.parentNode.appendChild(e);
        }
    };

    $.create = function(tag, parent) {
        var e = document.createElement(tag);
        if (parent) {
            $.append(e, parent);
        }
        return e;
    };

    $.remove = function(e) {
        if (e && e.parentNode) {
            e.parentNode.removeChild(e);
        }
    };

    $.hasClass = function(e, className) {
        return e.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'));
    };

    $.addClass = function(e, className) {
        if (!$.hasClass(e, className)) e.className += " " + className;
    };

    $.removeClass = function(e, className) {
        if ($.hasClass(e, className)) {
            var reg = new RegExp('(\\s|^)' + className + '(\\s|$)');
            e.className = e.className.replace(reg, ' ');
        }
    };

    //
    // Spark
    //
    var spark = (typeof (spark) == 'undefined') ? {} : spark;

    spark.container = {
        id: 'sparkrebel__container'
    };

    spark.options = {
        // Automatically load these assets
        load: {
            js: [],
            css: ['/css/compiled/share.css']
        },
        images: {
            minHeight: 100,
            minWidth: 100
        },
        frame: {
            url: '/posts/add/multiasset?popup=1'
        },
        title: {
            sparking: 'Click the images or videos you want to spark',
            posting_multiple: 'Spark these items',
            posting_single: 'Spark this item'
        },
        errors: {
            no_images_selected: 'Select at least one item',
            empty_description: 'Please provide a description for the selected items'
        },
        // Don't allow sparking from these hosts
        reject: {
            'sparkrebel': 'The SPARK IT! button is installed!\n\nClick it as you browse images on other sites to spark them instantly.'
        },
        // This uses mainly DIV's to avoid having to reset
        html: [
            '<div id="sparkrebel__shadow"></div>',
            '<div id="sparkrebel__content">',
                '<header>',
                    '<div id="sparkrebel__logo"><span>SparkRebel</span></div>',
                    '<div id="sparkrebel__close">X</div>',
                '</header>',
                '<div id="sparkrebel__main">',
                    '<div id="sparkrebel__title"></div>',
                    '<div id="sparkrebel__images">',
                        '<div id="sparkrebel__col_1" class="sparkrebel__col">',
                            '<div class="sparkrebel__img">',
                                '<div class="sparkrebel__img_overlay"></div>',
                                '<div class="sparkrebel__img_size"></div>',
                                '<div class="sparkrebel__img_marker"><span class="sparkrebel__img_marker_icon"></span></div>',
                                '<img/>',
                            '</div>',
                            '<input type="checkbox" />',
                            '<textarea disabled="disabled"></textarea>',
                        '</div>',
                        '<div id="sparkrebel__errors" style="display: none;"></div>',
                        '<div id="sparkrebel__next">Next</div>',
                    '</div>',
                '</div>',
            '</div>',
        ].join(' ')
    };

    /**
     * Closes dialog
     */
    spark.close = function() {
        if ('element' in this.container) {
            $.remove(this.container.element);
        }
    };

    /**
     * Draw the dialog on screen
     */
    spark.draw = function() {
        if ('element' in this.container) {
            return;
        }

        this._getSelectedText();
       
        if($.hasHost('youtube')) {
            this._processYoutubeVideos();
        } else {
            this._processImages();       
            this._processYoutubeEmbededs();
        }         
        
        
        // Container
        var container = $.create('div');
            container.id = this.container.id;
            container.style.display = "none";
            container.innerHTML = this.options.html;
        this.container.element = container;
        $.append(this.container.element);

        // Set title
        $("#sparkrebel__title").innerHTML = this.options.title.sparking;

        this._processScripts();
        this._loadAssets();
        this._drawImages();

        // Show dialog
        container.style.display = "block";

        // Close container when pressing ESC
        $.bind(window, "keydown", function(e) {
            if (e.keyCode == 27) {
                spark.close();
            }
        });

        // ... or clicking the close button
        $.bind($("#sparkrebel__close"), "click", function(e) {
            spark.close();
        });
    };

    /**
     * Returns User selected text
     */
    spark._getSelectedText = function() {
        if (window.getSelection) {
            this.selection = window.getSelection();
        } else if (document.getSelection) {
            this.selection = document.getSelection();
        } else if (document.selection) {
            this.selection = document.selection.createRange().text;
        }
    };

    /**
     * Finds all the images we can spark
     */
    spark._processImages = function() {
        this.images = [];
        for (var i = 0; i < document.images.length; i++) {
            var image = document.images[i];
            if (image.style.display != "none") {
                // Get URL to avoid images with src=""
                if (navigator.appName == 'Microsoft Internet Explorer') {
                    pathName = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
                    currentURL = window.location.href.substring(0, window.location.href.length - ((window.location.pathname + window.location.search + window.location.hash).length - pathName.length));
                } else {
                    currentURL = top.location.href;
                }

                if (image.width < this.options.images.minWidth || image.height < this.options.images.minHeight || image.src < 1 || image.src == currentURL) {
                    continue;
                }
                this.images.push(image);
            }
        }
        // Order by image size (bigger first)
        this.images.sort(function(a,b) {
            return (b.width*b.height) - (a.width*a.height);
        });
    };
    
    spark._processYoutubeVideos = function() {
        this.youtube_videos = []; 
        this.youtube_video_codes = [];
        var youtube_matches = [/ytimg.com\/vi/, /img.youtube.com\/vi/];
        var youtube_embed_matches = [/^http:\/\/s\.ytimg\.com\/yt/, /^http:\/\/.*?\.?youtube-nocookie\.com\/v/];
        
        
        //i think this could be priority and skip normal matches if found here
        var embeds = document.getElementsByTagName('embed');
        for (var i = 0; i < embeds.length; i++) {   
            for (var m = 0; m < youtube_embed_matches.length; m++) {
                if(embeds[i].src.match(youtube_embed_matches[m])) {
                    this.youtube_videos.push(embeds[i]);
                    var str = embeds[i].getAttribute('flashvars');
                    if (d = str.split("video_id=")[1]) {
                        d = d.split("&")[0];
                        d = encodeURIComponent(d);
                        this.youtube_video_codes.push(d);
                    }

                }
            }                 
        }
        
        //skip rest if we found embed, cuz its from view page
        if(this.youtube_video_codes.length > 0) {
            this._addThumbnailsForYoutubeVideoCodes();
            return;
        }
        
        for (var i = 0; i < document.images.length; i++) {
            var image = document.images[i];
            for (var m = 0; m < youtube_matches.length; m++) {
                var src = image.src;
                if (src.match(youtube_matches[m])) {
                    this.youtube_videos.push(image);
                    var parts = src.split('/');                        
                    this.youtube_video_codes.push(parts[parts.length-2]);
                }
            }    
        }

        
        
        this._addThumbnailsForYoutubeVideoCodes();
        
    };
    
    spark._addThumbnailsForYoutubeVideoCodes = function() {
        if(typeof (this.images) == 'undefined') {
            this.images = [];
        }
        for (var i = 0; i < this.youtube_video_codes.length; i++) {   
            var image = new Image;
            image.src = "http://img.youtube.com/vi/" + this.youtube_video_codes[i] + "/0.jpg";
            image.setAttribute('data-video-code', this.youtube_video_codes[i]);
            image.setAttribute('data-video-src', "http://www.youtube.com/watch?v=" + this.youtube_video_codes[i]);
            this.images.push(image);
        }  
    };
    
    spark._processYoutubeEmbededs = function() {
        if(typeof (this.youtube_videos) == 'undefined') {
            this.youtube_videos = [];
        }
        if(typeof (this.youtube_video_codes) == 'undefined') {
            this.youtube_video_codes = [];
        }
        var match = /^http:\/\/www\.youtube\.com\/embed\/([a-zA-Z0-9\-_]+)/;
        var iframes = document.getElementsByTagName('iframe');
        for (var i = 0; i < iframes.length; i++) {   
            if(iframes[i].src.match(match)) {
                this.youtube_videos.push(iframes[i]);        
                var parts = iframes[i].src.split('/');
                this.youtube_video_codes.push(parts[parts.length-1]);
            }
        }
        this._addThumbnailsForYoutubeVideoCodes();
    };
    
    /**
     * Draws all images on the screen
     */
    spark._drawImages = function() {
        var refColumn = $("#sparkrebel__col_1"),
            pages     = 0;
        for (var i = 0; i < this.images.length; i++) {
            var image   = this.images[i],
                current = i + 1,
                column  = $("#sparkrebel__col_" + current),
                pages   = current % 8 == 1 ? pages + 1 : pages;
            if (!column) {
                column = refColumn.cloneNode(true);
                column.id = "sparkrebel__col_" + current;
                $.after(column, $("#sparkrebel__col_" + i));
            }
            var img = column.getElementsByTagName("img")[0];
                img.src = image.src;
            var textarea = column.getElementsByTagName("textarea")[0];
                textarea.name = "i[" + current + "][desc]";
                if (image.title != "") {
                    textarea.value = image.title;
                } else {
                    textarea.value = image.alt;
                }
            var checkbox = column.getElementsByTagName("input")[0];
                checkbox.name = "i[" + current + "][src]";
            if(image.getAttribute('data-video-src')) {
                checkbox.value = image.getAttribute('data-video-src');
            } else {
                checkbox.value = img.src;
            }    
                
            var imgContainer = column.getElementsByTagName("div")[0];

            // Image click event
            $.bind(imgContainer, "click", function(e) {
                var imgContainer = this,
                    column       = imgContainer.parentNode,
                    textarea     = column.getElementsByTagName("textarea")[0],
                    checkbox     = column.getElementsByTagName("input")[0];
                if (checkbox.checked) {
                    $.removeClass(column, "sparkrebel__selected");
                    checkbox.checked = false;
                    textarea.disabled = true;
                } else {
                    $.addClass(column, "sparkrebel__selected");
                    checkbox.checked = true;
                    textarea.disabled = false;
                }
                $("#sparkrebel__errors").style.display = "none";
            });

            // Add size information to overlay
            if(image.getAttribute('data-video-code')) {
                column.getElementsByClassName("sparkrebel__img_size")[0].innerHTML = "Video Spark";                
            } else {
                column.getElementsByClassName("sparkrebel__img_size")[0].innerHTML = "W: " + image.width + "px<br />H: " + image.height + "px";                
            }
        }

        // Submit images -- 'Next' click event
        $.bind($("#sparkrebel__next"), "click", function(e) {
            var data           = spark._serializeImages(),
                imgContainer   = $("#sparkrebel__images"),
                frameContainer = $("#sparkrebel__frame"),
                title          = $("#sparkrebel__title");

            selectedImages = imgContainer.getElementsByClassName("sparkrebel__selected");

            if (selectedImages.length < 1) {
                $("#sparkrebel__errors").innerHTML = spark.options.errors.no_images_selected;
                $("#sparkrebel__errors").style.display = "block";
                return false;
            }

            for (var i = 0; i < selectedImages.length; i++ ) {
                if (selectedImages[i].getElementsByTagName("textarea")[0].value == "") {
                    $("#sparkrebel__errors").innerHTML = spark.options.errors.empty_description;
                    $("#sparkrebel__errors").style.display = "block";
                    return false;
                }
            }
            
            var query = data.serialized + '&url=' + encodeURIComponent(spark.params.source),
                url   = spark.params.basePath + spark.options.frame.url + '&' + query;
            
            // Load post add in new window
            spark.popup = $.popup(url);
            spark.close();
        });
    };

    /**
     * Serializes the User's selected images
     */
    spark._serializeImages = function() {
        var data         = [],
            imgContainer = $("#sparkrebel__images"),
            inputs       = imgContainer.getElementsByTagName("input"),
            textareas    = imgContainer.getElementsByTagName("textarea"),
            total        = 0,
            i            = 0;
        // Inputs
        for (i = 0; i < inputs.length; i++) {
            var input = inputs[i];
            if (!input.disabled && input.name && input.name.length > 0 ) {
                if (input.type == "checkbox" && input.checked) {
                    data.push(input.name + "=" + encodeURIComponent(input.value));
                    total++;
                }
            }
        }
        // Textareas
        for (i = 0; i < textareas.length; i++) {
            var textarea = textareas[i];
            if (!textarea.disabled && textarea.name && textarea.name.length > 0 ) {
                data.push(textarea.name + "=" + encodeURIComponent(textarea.value));
            }
        }
        return {
            'serialized': data.join('&'),
            'count': total
        }
    };

    /**
     * Processes all script tags to find ours
     * and read query string
     */
    spark._processScripts = function() {
        var scripts = $("script"),
            src     = false,
            i       = 0;
        // Find our script tag
        for (i = 0; i < scripts.length; i++) {
            if (/share\.js\?.*t=/i.test(scripts[i].src)) {
                src = scripts[i].src;
                break;
            }
        }
        if (src) {
            var parts    = src.split('?'),
                params   = $.query(parts[1]),
                basePath = parts[0].replace('/js/compiled/share.js', '');
                basePath = basePath.replace('/js/share.js', ''); // For older bookmarklets
            // Add any scripts to load
            if ('js' in params) {
                var sources = params.js.split(',');
                // Add any scripts to loader
                for (i = 0; i < sources.length; i++) {
                    this.options.load.js.push(sources[i]);
                }
            }
            // Add any stylesheets to load
            if ('css' in params) {
                var sources = params.css.split(',');
                // Add any stylesheets to loader
                for (i = 0; i < sources.length; i++) {
                    this.options.load.css.push(sources[i]);
                }
            }
            // Store params
            params['basePath'] = basePath;
            params['source']   = document.location.href;
            this.params = params;
        }
    };

    /**
     * Initialization before we draw()
     */
    spark._loadAssets = function() {
        if (!('ready' in this.options.load)) {
            if ('basePath' in this.params) {
                var basePath  = this.params.basePath,
                    container = this.container.element;
                // Javascript(s)
                for (var i = 0; i < this.options.load.js.length; i++) {
                    var src = this.options.load.js[i];
                    var script = $.create('script');
                        script.type    = 'text/javascript';
                        script.charset = 'UTF-8';
                        script.src     = basePath + src + '?t=' + Math.ceil(new Date().getTime());
                    $.append(script, container);
                }
                // Stylesheet(s)
                for (var i = 0; i < this.options.load.css.length; i++) {
                    var href = this.options.load.css[i];
                    var link = $.create('link');
                        link.href = basePath + href + '?t=' + Math.ceil(new Date().getTime());
                        link.rel  = 'stylesheet';
                        link.type = 'text/css';
                    $.append(link, container);
                }
                this.options.load.ready = true;
            }
        }
    };

    /**
     * Check hosts against blacklist
     */
    spark.canSpark = function() {
        for (var host in this.options.reject) {
            if (this.options.reject.hasOwnProperty(host)) {
                if ($.hasHost(host)) {
                    alert(this.options.reject[host]);
                    return false;
                }
            }
        }
        return true;
    };

    if (spark.canSpark()) {
        spark.draw();
    }

})();