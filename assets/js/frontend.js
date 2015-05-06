
/**********************************************************************************************************************
 *                                                  Class Definition                                                  *
 **********************************************************************************************************************/

var ShopelloFrontend = {
    /******************************************************************************************************************
     *                                                     Init                                                       *
     ******************************************************************************************************************/
    init: function() {
        var self = this;

        jQuery.each([
            {
                field:  '#input-keyword',
                callback: this.callbacks.setKeyword,
                event:  'keyup'
            },
            {
                field: '#input-pagesize',
                callback: this.callbacks.setPagesize,
                event: 'change'
            },
            {
                field: '#input-price-max',
                callback: this.callbacks.setMaxPrice,
                event: 'change'
            },
            {
                field: '#reset-price-max',
                callback: this.callbacks.resetMaxPrice,
                event: 'click'
            },
            {
                field: '#input-sort-field',
                callback: this.callbacks.sorting,
                event: 'change'
            },
            {
                field: '#input-sort-order',
                callback: this.callbacks.sortOrder,
                event: 'change'
            },
            {
                field: 'input[name=color]:radio',
                callback: this.callbacks.colorFilter,
                event: 'click'
            }
        ], (function (key, value) {
            var callback = value.callback;

            jQuery(value.field).on(value.event, (function (event) {
                callback(event, self);
            }));
        }));
    },



    /******************************************************************************************************************
     *                                                    Helpers                                                     *
     ******************************************************************************************************************/
    buildGetParams: function(regex, newparam) {
        var uri = window.location.href;

        if (uri.search(regex) != -1) {
            uri = uri.replace(regex, newparam);
        } else {
            var join = (uri.search('\\?') == -1) ? '?' : '&';

            uri = uri + join + newparam;
        }

        return uri;
    },

    /**
     * Run a callback delayed
     */
    delay: (function() {
        var timer = 0;

        return function(callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })(),




    /******************************************************************************************************************
     *                                                   Callbacks                                                    *
     ******************************************************************************************************************/
    callbacks: {
        /**
         * Set Keyword
         */
        setKeyword: function(event, self) {
            var delay = 2000;

            if (event.keyCode == 13) {
                delay = 0;
            }

            self.delay(function() {
                var value = event.currentTarget.value;

                window.location.href = self.buildGetParams(/swp_query=[^&]+/, 'swp_query=' + value);
            }, delay);
        },

        /**
         * Set Page Size
         */
        setPagesize: function(event, self) {
            var value = jQuery('#' + event.currentTarget.id + ' option:selected').val();

            window.location.href = self.buildGetParams(/swp_pagesize=\d+/, 'swp_pagesize=' + value);
        },

        /**
         * Set Max Price
         */
        setMaxPrice: function(event, self) {
            var value = event.currentTarget.value;

            window.location.href = self.buildGetParams(/swp_maxprice=\d+/, 'swp_maxprice=' + value);
        },

        /**
         * Reset max price
         */
        resetMaxPrice: function(event, self) {
            window.location.href = self.buildGetParams(/swp_maxprice=\d+/, '');
        },

        /**
         * Sorting
         */
        sorting: function(event, self) {
            var value = jQuery('#' + event.currentTarget.id + ' option:selected').val();

            window.location.href = self.buildGetParams(/swp_sorting=[^&]+/, 'swp_sorting=' + value);
        },

        /**
         * Sort Order
         */
        sortOrder: function(event, self) {
            var value = jQuery('#' + event.currentTarget.id + ' option:selected').val();

            window.location.href = self.buildGetParams(/swp_sortorder=[^&]+/, 'swp_sortorder=' + value);
        },

        /**
         * Colorfilter
         */
        colorFilter: function (event, self) {
            var currentColor = null;
            var targetColor = jQuery(event.currentTarget).val();

            if (window.location.href.search(/swp_color=[^&]+/) !== -1) {
                currentColor = decodeURI(window.location.href.match(/swp_color=([^&]+)/)[1]);
            }

            var newParam = 'swp_color=' + targetColor;

            if (currentColor === targetColor) {
                newParam = '';
            }

            window.location.href = self.buildGetParams(/swp_color=[^&]+/, newParam);
        }
    }
};

ShopelloFrontend.init();
