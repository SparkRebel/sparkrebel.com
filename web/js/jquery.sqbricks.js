/**
 * jQuery SparkRebel Bricks v1.0a
 * http://sparkrebel.com
 *
 */

(function( window, $, undefined ){

  /*
   * smartresize: debounced resize event for jQuery
   *
   * latest version and complete README available on Github:
   * https://github.com/louisremi/jquery.smartresize.js
   *
   * Copyright 2011 @louis_remi
   * Licensed under the MIT license.
   */

  var $event = $.event,
      resizeTimeout;

  $event.special.smartresize = {
    setup: function() {
      $(this).bind( "resize", $event.special.smartresize.handler );
    },
    teardown: function() {
      $(this).unbind( "resize", $event.special.smartresize.handler );
    },
    handler: function( event, execAsap ) {
      // Save the context
      var context = this,
          args = arguments;

      // set correct event type
      event.type = "smartresize";

      if ( resizeTimeout ) { clearTimeout( resizeTimeout ); }
      resizeTimeout = setTimeout(function() {
        jQuery.event.handle.apply( context, args );
      }, execAsap === "execAsap"? 0 : 100 );
    }
  };

  $.fn.smartresize = function( fn ) {
    return fn ? this.bind( "smartresize", fn ) : this.trigger( "smartresize", ["execAsap"] );
  };

  // pwBricks constructor
  $.Bricks = function( options, element ){
    this.element = $( element );

    this._create( options );
    this._init( options.callback );
  };

  $.Bricks.settings = {
    gutterWidth: 0
  };

  $.Bricks.prototype = {

    _filterFindBricks: function( $elems ) {
      var selector = this.options.itemSelector;
      // if there is a selector
      // filter/find appropriate item elements
      return !selector ? $elems : $elems.filter( selector ).add( $elems.find( selector ) );
    },

    _getBricks: function( $elems ) {
      var $bricks = this._filterFindBricks( $elems )
        .addClass('brick');
      return $bricks;
    },

    // sets up widget
    _create : function( options ) {
      this.options = $.extend( true, {}, $.Bricks.settings, options );

      // need to get bricks
      this.reloadItems();
      
      this.element.css({
        position : 'relative'
      });

      // add "sqBricks" class first time around
      var instance = this;
      setTimeout( function() {
        instance.element.addClass('sqBricks');
      }, 0 );

      // bind resize method
      $(window).bind( 'smartresize.sqBricks', function() {
        instance.resize();
      });
    },

    // _init fires when instance is first created
    // and when instance is triggered again -> $el.sqBricks();
    _init : function( callback ) {
      this._getColumns();
      this._reLayout( callback );
    },

    option: function( key, value ){
      // set options AFTER initialization:
      // signature: $('#foo').bar({ cool:false });
      if ( $.isPlainObject( key ) ){
        this.options = $.extend(true, this.options, key);
      }
    },

    // ====================== General Layout ======================

    // used on collection of atoms (should be filtered, and sorted before )
    // accepts atoms-to-be-laid-out to start with
    layout : function( $bricks, callback ) {

      // create the bricks queue
      if ($bricks.length >= 3) {
        this.$bricks_stack = [1, 2, 3];
      } else if ($bricks.length == 2) {
        this.$bricks_stack = [1, 2];
      } else if ($bricks.length == 1) {
        this.$bricks_stack = [1];
      }
      
      // place each brick
      for (var i=0, len = $bricks.length; i < len; i++) {
        this._placeBrick( $bricks );
        if ($bricks.length > i+3) {
          this.$bricks_stack.push(i+4);
        }
      }

      // provide $elems as context for the callback
      if ( callback ) {
        callback.call( $bricks );
      }
    },

    // calculates number of columns
    _getColumns : function() {
      if (!this.$bricks.length) {
        this.cols = 0;
        return;
      }
      
      var container = this.element;
      this.containerWidth = container.width();

      // Fluid container for 1200+
      if(this.containerWidth >= 1200) {
        unit_width = 200;
        this.element.parent().css('width', '100%');
        units = Math.floor(this.element.parent().width() / unit_width);
        this.containerWidth = units*unit_width;
        this.element.css('width', this.containerWidth + 'px');
        this.element.parent().css('width', this.containerWidth + 'px');
      }

      // Mobile
      if(this.containerWidth <= 300) {
        this.cols = 1;
        this.columnsWidth = Array(1);
        this.element.append('<div class="sqBricks_col" id="sqBricks_col_1">');
      
      // Tablet
      } else if(this.containerWidth < 960) {
        this.cols = Math.floor(this.containerWidth / this.$bricks.outerWidth(true));
        this.columnsLeft = [];
        this.columnsWidth = [];

        for(var i=0; i < this.cols; i++) {
            this.columnsWidth.push(1);
            this.element.append('<div class="sqBricks_col" id="sqBricks_col_' + (i+1) + '">');
        }
      
      // Normal
      } else if(this.containerWidth < 1200) {
        this.cols = 3;
        this.columnsWidth = Array(2, 1, 2);
        this.element.append('<div class="sqBricks_col double" id="sqBricks_col_1">', '<div class="sqBricks_col" id="sqBricks_col_2">', '<div class="sqBricks_col double" id="sqBricks_col_3">');
      
      // Wide & elastic
      } else {
        units_count = 0;
        this.columnsWidth = Array();
        
        for(i=1; i<=units; i++) {
          if(i%2 != 0) {
            this.columnsWidth.push(2);
            this.element.append('<div class="sqBricks_col double" id="sqBricks_col_' + (i) + '">');
            units_count = units_count + 2;
          } else {
            this.columnsWidth.push(1);
            this.element.append('<div class="sqBricks_col" id="sqBricks_col_' + (i) + '">');
            units_count++;
          }
          if(units_count >= units) {
            break;
          }
        }

        if(units_count > units) {
          $('.sqBricks_col').remove();
          units_count = 0;
          this.columnsWidth = Array();
          for(i=1; i<=units; i++) {
            if(i%2 == 0) {
              this.columnsWidth.push(2);
              this.element.append('<div class="sqBricks_col double" id="sqBricks_col_' + (i) + '">');
              units_count = units_count + 2;
            } else {
              this.columnsWidth.push(1);
              this.element.append('<div class="sqBricks_col" id="sqBricks_col_' + (i) + '">');
              units_count++;
            }
            if(units_count >= units) {
              break;
            }
          }
        }

        this.cols = this.columnsWidth.length;
      }
    },

    // layout logic
    _placeBrick: function( $bricks ) {
      var $brick,
          groupY,
          i,
          size = this.cols === 1 ? 'double' : 'single';

      groupY = this.colYs;

      // get the minimum Y value from the columns
      var minimumY = Math.min.apply( Math, groupY ),
          shortCol = 0;

      // Find index of short column, the first from the left
      for (i=0, len = groupY.length; i < len; i++) {
        if ( groupY[i] === minimumY ) {
          shortCol = i;
          break;
        }
      }
      
      if (this.element.width() >= 960) {
        // Is a wide column?
        if ( this.columnsWidth[shortCol] == 2 ) {
            $brick = this.chooseBrickFromStack($bricks, true);
            $brick.addClass('double');
            // if ($brick.hasClass('pinned')) {
            //     $brick.find('.postImage img').attr('usemap', '#srpinneddouble');
            // }
            size = 'double';
        } else {
            $brick = this.chooseBrickFromStack($bricks, false);
        }
      } else {
          $brick = $($bricks[this.$bricks_stack[0]-1]);
          this.$bricks_stack.splice(0, 1);
      }
      
      this.optimizeImage($brick, size);
      
      // place the brick
      $brick.appendTo('#sqBricks_col_' + (shortCol + 1));
      
      //try and figure out image height
      var $img = $brick.find('.postImage img');
      var ratio = $img.attr('data-ratio');
      if ( typeof(ratio) != 'undefined' && ratio != ''  ) {
          var $imgDiv = $brick.children('.postImage');
          $img.attr('height', Math.round($imgDiv.width() / parseFloat(ratio)) + 'px');
      }
      
      // apply setHeight to necessary columns
      var setHeight = minimumY + $brick.outerHeight(true),
          setSpan = this.cols + 1 - len;
      for ( i=0; i < setSpan; i++ ) {
        this.colYs[ shortCol + i ] = setHeight;
      }
    },


    chooseBrickFromStack: function ($bricks, wideColumn) {
        if (this.$bricks_stack.length == 0) {
            return null;
        }
        
        var choice = null,
            choiceIndex = null,
            choiceWidth = 0;
        
        for (indx in this.$bricks_stack) {
            var brick = $($bricks[this.$bricks_stack[indx] - 1]);
            var width = brick.find('.postImage img').attr('data-width');
            /* Temp fix for broken images */
            if (width == "") {
                width = 1;
            }
            /* Pinned brick */
            if (brick.hasClass('pinned')) {
                choice = brick;
                choiceIndex = indx;
                break;
            }
            if (choiceWidth == 0 || (wideColumn && width > choiceWidth) || (!wideColumn && width < choiceWidth) ) {
                choice = brick;
                choiceIndex = indx;
                choiceWidth = width;
            }
        }
        
        this.$bricks_stack.splice(choiceIndex, 1);
        return choice;  
    },


    /**
     * Use large images for:
     *     Double-width bricks
     *     single column layout
     *
     */
    optimizeImage: function (brick, size) {
      var $brick = $(brick),
          i,
          img,
          imgAttrs = ['src', 'data-src'],
          src;

        img = $brick.find('img.lazy');
        if (size !== 'double' || img.hasClass('doubleImage')) {
            return;
        }

        i = imgAttrs.length
        while (i) {
            i -= 1;

            src = img.attr(imgAttrs[i]);
            if (src && src.indexOf('.l.') === -1) {
                img.attr(imgAttrs[i], src.replace('.m.', '.l.'));
            }
        }
        img.addClass('doubleImage');
    },

    resize: function() {
      var prevContainerWidth = this.containerWidth;
      // remove inline styles for 1200+
      this.element.parent().attr('style', '');
      this.element.css('width', '100%');
      if ( prevContainerWidth !== this.element.width() ) {
        // remove double class
        this.$bricks.removeClass('double');
        // remove columns
        $('.brick').unwrap();
        // get updated containerWidth
        this._getColumns();
        // if column count has changed, trigger new layout
        this._reLayout();
        
        //
        //Temporary solution - TODO: Make a callback for resize()
        this.element.find('.postPromoContent').css('top', function() {
            return ($(this).parent().height()/2) - $(this).height()/2;
        });
      }
    },


    _reLayout : function( callback ) {
      // reset columns
      var i = this.cols;
      this.colYs = [];
      while (i--) {
        this.colYs.push( 0 );
      }

      // apply layout logic to all bricks
      this.layout( this.$bricks, callback );
    },
    
    // ====================== Convenience methods ======================

    // goes through all children again and gets bricks in proper order
    reloadItems : function() {
      this.$bricks = this._getBricks( this.element.children() );
    },


    reload : function( callback ) {
      this.reloadItems();
      this._init( callback );
    },


    // convienence method for working with Infinite Scroll
    appended : function( $content, callback ) {
      this._appended( $content, callback );
    },

    _appended : function( $content, callback ) {
      // Temp fix for pinned repeat
      $content.each(function(index) {
        // if ($(this).hasClass('pinned') && $(this).hasClass('double')) {
        //   $(this).find('.postImage img').attr('usemap', 'srpinneddouble');
        // }
      });
      var $newBricks = this._getBricks( $content );
      // add new bricks to brick pool
      this.$bricks = this.$bricks.add( $newBricks );
      this.layout( $newBricks, callback );
    },

  };

  // =======================  Plugin bridge  ===============================
  // leverages data method to either create or return $.Bricks constructor
  // A bit from jQuery UI
  //   https://github.com/jquery/jquery-ui/blob/master/ui/jquery.ui.widget.js
  // A bit from jcarousel
  //   https://github.com/jsor/jcarousel/blob/master/lib/jquery.jcarousel.js

  $.fn.sqBricks = function( options ) {
    if ( typeof options === 'string' ) {
      // call method
      var args = Array.prototype.slice.call( arguments, 1 );

      this.each(function(){
        var instance = $.data( this, 'sqBricks' );
        if ( !instance ) {
          logError( "cannot call methods on sqBricks prior to initialization; " +
            "attempted to call method '" + options + "'" );
          return;
        }
        if ( !$.isFunction( instance[options] ) || options.charAt(0) === "_" ) {
          logError( "no such method '" + options + "' for sqBricks instance" );
          return;
        }
        // apply method
        instance[ options ].apply( instance, args );
      });
    } else {
      this.each(function() {
        var instance = $.data( this, 'sqBricks' );
        if ( instance ) {
          // apply options & init
          instance.option( options || { callback : null} );
          instance._init( options.callback );
        } else {
          // initialize new instance
          $.data( this, 'sqBricks', new $.Bricks( options, this ) );
        }
      });
    }
    return this;
  };

})( window, jQuery );
