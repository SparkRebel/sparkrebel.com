/*global $: true, APP: true */

var APP = (function (parent, $) {
    if (parent.curator) {
        return parent;
    }
    
    var enabled = false;
    
    /**
     * createCuratorToggle
     */
    function createCuratorToggle() {
        if ($('#curatorToggle').length) {
            return;
        }
    	
    	if(parent.me !== false && parent.me.hasCuratorRole)  {
    		enabled = true;
    	}

    	var klass = enabled ? 'enabled' : 'disabled';

        $('<a id="curatorToggle" class="'+klass+'" title="Click to toggle">Curator</a>')
            .appendTo("body")
            .click(function() {
                var $this = $(this);
    			$.post('/admin/toggle_curator_mode');

                if ($this.hasClass('disabled')) {
                    enabled = true;
                    $this
                        .removeClass('disabled')
                        .addClass('enabled');
                    
                    parent.curator.init();
                } else {
                    enabled = false;
    
                    $this
                        .removeClass('enabled')
                        .addClass('disabled');
                }
            });
    }
    
    /**
     * init
     */
    function init() {
        return;
    }
    
    parent.curator = {
        init: init
    };
    
    $(function() {
        createCuratorToggle();
    });
    
    return parent;
}(APP || {}, $));