(function(){var a=function(e,c){e=e.match(/^(\W)?(.*)/);return(c||document)["getElement"+(e[1]?e[1]=="#"?"ById":"sByClassName":"sByTagName")](e[2])};a.isEmpty=function(c){for(var e in c){if(c.hasOwnProperty(e)){return false}}return true};a.popup=function(g,h,c,f){h=h||830;c=c||545;var j=(screen.width-h)/2,i=(screen.height-c)/2;var k="width="+h+", height="+c;k+=", top="+i+", left="+j,k+=", directories=no, location=no, menubar=no",k+=", resizable=no, scrollbars=no, status=no, toolbar=no";var e=window.open(g,f||"sparkrebel__window",k);if(window.focus){e.focus()}return e};a.hasHost=function(f){var g=document.location.hostname.toLowerCase().split(".");var c=g.length;for(var e=0;e<c;e++){if(g[e]==f){return true}}return false};a.query=function(h){var i,c=/\+/g,g=/([^&=]+)=?([^&]*)/g,j=function(e){return decodeURIComponent(e.replace(c," "))},f={};while(i=g.exec(h)){f[j(i[1])]=j(i[2])}return f};a.bind=(function(e,c){if(c.addEventListener){return function(k,j,g){if((k&&!k.length)||k===e){k.addEventListener(j,g,false)}else{if(k&&k.length){var f=k.length;for(var h=0;h<f;h++){addEvent(k[h],j,g)}}}}}else{if(c.attachEvent){return function(k,j,g){if((k&&!k.length)||k===e){k.attachEvent("on"+j,function(){return g.call(k,e.event)})}else{if(k.length){var f=k.length;for(var h=0;h<f;h++){addEvent(k[h],j,g)}}}}}}})(this,document);a.append=function(f,c){return c?c.appendChild(f):document.body.appendChild(f)};a.after=function(f,c){if(c.nextSibling){c.parentNode.insertBefore(f,c.nextSibling)}else{c.parentNode.appendChild(f)}};a.create=function(c,f){var g=document.createElement(c);if(f){a.append(g,f)}return g};a.remove=function(c){if(c&&c.parentNode){c.parentNode.removeChild(c)}};a.hasClass=function(f,c){return f.className.match(new RegExp("(\\s|^)"+c+"(\\s|$)"))};a.addClass=function(f,c){if(!a.hasClass(f,c)){f.className+=" "+c}};a.removeClass=function(g,f){if(a.hasClass(g,f)){var c=new RegExp("(\\s|^)"+f+"(\\s|$)");g.className=g.className.replace(c," ")}};var b=(typeof(b)=="undefined")?{}:b;b.container={id:"sparkrebel__container"};b.options={load:{js:[],css:["/css/compiled/share.css"]},images:{minHeight:100,minWidth:100},frame:{url:"/posts/add/multiasset?popup=1"},title:{sparking:"Click the images or videos you want to spark",posting_multiple:"Spark these items",posting_single:"Spark this item"},errors:{no_images_selected:"Select at least one item",empty_description:"Please provide a description for the selected items"},reject:{sparkrebel:"The SPARK IT! button is installed!\n\nClick it as you browse images on other sites to spark them instantly."},html:['<div id="sparkrebel__shadow"></div>','<div id="sparkrebel__content">',"<header>",'<div id="sparkrebel__logo"><span>SparkRebel</span></div>','<div id="sparkrebel__close">X</div>',"</header>",'<div id="sparkrebel__main">','<div id="sparkrebel__title"></div>','<div id="sparkrebel__images">','<div id="sparkrebel__col_1" class="sparkrebel__col">','<div class="sparkrebel__img">','<div class="sparkrebel__img_overlay"></div>','<div class="sparkrebel__img_size"></div>','<div class="sparkrebel__img_marker"><span class="sparkrebel__img_marker_icon"></span></div>',"<img/>","</div>",'<input type="checkbox" />','<textarea disabled="disabled"></textarea>',"</div>",'<div id="sparkrebel__errors" style="display: none;"></div>','<div id="sparkrebel__next">Next</div>',"</div>","</div>","</div>",].join(" ")};b.close=function(){if("element" in this.container){a.remove(this.container.element)}};b.draw=function(){if("element" in this.container){return}this._getSelectedText();if(a.hasHost("youtube")){this._processYoutubeVideos()}else{this._processImages();this._processYoutubeEmbededs()}var c=a.create("div");c.id=this.container.id;c.style.display="none";c.innerHTML=this.options.html;this.container.element=c;a.append(this.container.element);a("#sparkrebel__title").innerHTML=this.options.title.sparking;this._processScripts();this._loadAssets();this._drawImages();c.style.display="block";a.bind(window,"keydown",function(f){if(f.keyCode==27){b.close()}});a.bind(a("#sparkrebel__close"),"click",function(f){b.close()})};b._getSelectedText=function(){if(window.getSelection){this.selection=window.getSelection()}else{if(document.getSelection){this.selection=document.getSelection()}else{if(document.selection){this.selection=document.selection.createRange().text}}}};b._processImages=function(){this.images=[];for(var c=0;c<document.images.length;c++){var e=document.images[c];if(e.style.display!="none"){if(navigator.appName=="Microsoft Internet Explorer"){pathName=window.location.pathname.substring(0,window.location.pathname.lastIndexOf("/")+1);currentURL=window.location.href.substring(0,window.location.href.length-((window.location.pathname+window.location.search+window.location.hash).length-pathName.length))}else{currentURL=top.location.href}if(e.width<this.options.images.minWidth||e.height<this.options.images.minHeight||e.src<1||e.src==currentURL){continue}this.images.push(e)}}this.images.sort(function(g,f){return(f.width*f.height)-(g.width*g.height)})};b._processYoutubeVideos=function(){this.youtube_videos=[];this.youtube_video_codes=[];var l=[/ytimg.com\/vi/,/img.youtube.com\/vi/];var e=[/^http:\/\/s\.ytimg\.com\/yt/,/^http:\/\/.*?\.?youtube-nocookie\.com\/v/];var f=document.getElementsByTagName("embed");for(var k=0;k<f.length;k++){for(var g=0;g<e.length;g++){if(f[k].src.match(e[g])){this.youtube_videos.push(f[k]);var n=f[k].getAttribute("flashvars");if(d=n.split("video_id=")[1]){d=d.split("&")[0];d=encodeURIComponent(d);this.youtube_video_codes.push(d)}}}}if(this.youtube_video_codes.length>0){this._addThumbnailsForYoutubeVideoCodes();return}for(var k=0;k<document.images.length;k++){var h=document.images[k];for(var g=0;g<l.length;g++){var c=h.src;if(c.match(l[g])){this.youtube_videos.push(h);var j=c.split("/");this.youtube_video_codes.push(j[j.length-2])}}}this._addThumbnailsForYoutubeVideoCodes()};b._addThumbnailsForYoutubeVideoCodes=function(){if(typeof(this.images)=="undefined"){this.images=[]}for(var c=0;c<this.youtube_video_codes.length;c++){var e=new Image;e.src="http://img.youtube.com/vi/"+this.youtube_video_codes[c]+"/0.jpg";e.setAttribute("data-video-code",this.youtube_video_codes[c]);e.setAttribute("data-video-src","http://www.youtube.com/watch?v="+this.youtube_video_codes[c]);this.images.push(e)}};b._processYoutubeEmbededs=function(){if(typeof(this.youtube_videos)=="undefined"){this.youtube_videos=[]}if(typeof(this.youtube_video_codes)=="undefined"){this.youtube_video_codes=[]}var c=/^http:\/\/www\.youtube\.com\/embed\/([a-zA-Z0-9\-_]+)/;var g=document.getElementsByTagName("iframe");for(var e=0;e<g.length;e++){if(g[e].src.match(c)){this.youtube_videos.push(g[e]);var f=g[e].src.split("/");this.youtube_video_codes.push(f[f.length-1])}}this._addThumbnailsForYoutubeVideoCodes()};b._drawImages=function(){var c=a("#sparkrebel__col_1"),f=0;for(var j=0;j<this.images.length;j++){var g=this.images[j],m=j+1,h=a("#sparkrebel__col_"+m),f=m%8==1?f+1:f;if(!h){h=c.cloneNode(true);h.id="sparkrebel__col_"+m;a.after(h,a("#sparkrebel__col_"+j))}var k=h.getElementsByTagName("img")[0];k.src=g.src;var n=h.getElementsByTagName("textarea")[0];n.name="i["+m+"][desc]";if(g.title!=""){n.value=g.title}else{n.value=g.alt}var l=h.getElementsByTagName("input")[0];l.name="i["+m+"][src]";if(g.getAttribute("data-video-src")){l.value=g.getAttribute("data-video-src")}else{l.value=k.src}var e=h.getElementsByTagName("div")[0];a.bind(e,"click",function(r){var o=this,p=o.parentNode,i=p.getElementsByTagName("textarea")[0],q=p.getElementsByTagName("input")[0];if(q.checked){a.removeClass(p,"sparkrebel__selected");q.checked=false;i.disabled=true}else{a.addClass(p,"sparkrebel__selected");q.checked=true;i.disabled=false}a("#sparkrebel__errors").style.display="none"});if(g.getAttribute("data-video-code")){h.getElementsByClassName("sparkrebel__img_size")[0].innerHTML="Video Spark"}else{h.getElementsByClassName("sparkrebel__img_size")[0].innerHTML="W: "+g.width+"px<br />H: "+g.height+"px"}}a.bind(a("#sparkrebel__next"),"click",function(u){var t=b._serializeImages(),o=a("#sparkrebel__images"),q=a("#sparkrebel__frame"),v=a("#sparkrebel__title");selectedImages=o.getElementsByClassName("sparkrebel__selected");if(selectedImages.length<1){a("#sparkrebel__errors").innerHTML=b.options.errors.no_images_selected;a("#sparkrebel__errors").style.display="block";return false}for(var r=0;r<selectedImages.length;r++){if(selectedImages[r].getElementsByTagName("textarea")[0].value==""){a("#sparkrebel__errors").innerHTML=b.options.errors.empty_description;a("#sparkrebel__errors").style.display="block";return false}}var s=t.serialized+"&url="+encodeURIComponent(b.params.source),p=b.params.basePath+b.options.frame.url+"&"+s;b.popup=a.popup(p);b.close()})};b._serializeImages=function(){var l=[],g=a("#sparkrebel__images"),f=g.getElementsByTagName("input"),c=g.getElementsByTagName("textarea"),k=0,j=0;for(j=0;j<f.length;j++){var h=f[j];if(!h.disabled&&h.name&&h.name.length>0){if(h.type=="checkbox"&&h.checked){l.push(h.name+"="+encodeURIComponent(h.value));k++}}}for(j=0;j<c.length;j++){var e=c[j];if(!e.disabled&&e.name&&e.name.length>0){l.push(e.name+"="+encodeURIComponent(e.value))}}return{serialized:l.join("&"),count:k}};b._processScripts=function(){var c=a("script"),j=false,f=0;for(f=0;f<c.length;f++){if(/share\.js\?.*t=/i.test(c[f].src)){j=c[f].src;break}}if(j){var g=j.split("?"),h=a.query(g[1]),k=g[0].replace("/js/compiled/share.js","");k=k.replace("/js/share.js","");if("js" in h){var e=h.js.split(",");for(f=0;f<e.length;f++){this.options.load.js.push(e[f])}}if("css" in h){var e=h.css.split(",");for(f=0;f<e.length;f++){this.options.load.css.push(e[f])}}h.basePath=k;h.source=document.location.href;this.params=h}};b._loadAssets=function(){if(!("ready" in this.options.load)){if("basePath" in this.params){var k=this.params.basePath,c=this.container.element;for(var g=0;g<this.options.load.js.length;g++){var j=this.options.load.js[g];var f=a.create("script");f.type="text/javascript";f.charset="UTF-8";f.src=k+j+"?t="+Math.ceil(new Date().getTime());a.append(f,c)}for(var g=0;g<this.options.load.css.length;g++){var e=this.options.load.css[g];var h=a.create("link");h.href=k+e+"?t="+Math.ceil(new Date().getTime());h.rel="stylesheet";h.type="text/css";a.append(h,c)}this.options.load.ready=true}}};b.canSpark=function(){for(var c in this.options.reject){if(this.options.reject.hasOwnProperty(c)){if(a.hasHost(c)){alert(this.options.reject[c]);return false}}}return true};if(b.canSpark()){b.draw()}})();