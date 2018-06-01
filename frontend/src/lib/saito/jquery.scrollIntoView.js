import jQuery from 'jquery';

(function($) {

    "use strict";

    var helpers = {

        _scrollToTop: function(elem) {
            $('body').animate(
                {
                    scrollTop: elem.offset().top - 10,
                    easing: "swing"
                },
                300
            );
        },

        _scrollToBottom: function(elem) {

            $('body').animate(
                {
                    scrollTop: helpers._elementBottom(elem) - $(window).height() + 20,
                    easing: "swing"
                },
                300,
                function() {
                    if (helpers._isHeigherThanView(elem)) {
                        helpers._scrollToTop(elem);
                    }
                }
            );
        },

        _elementBottom: function(elem) {
            return elem.offset().top + elem.height();
        },

        /**
         * Checks if an element is completely visible in current browser window
         *
         * @param elem
         * @return {Boolean}
         * @private
         */
        _isScrolledIntoView: function(elem) {
            if ($(elem).length === 0) {
                return true;
            }
            var docViewTop = $(window).scrollTop();
            var docViewBottom = docViewTop + $(window).height();

            var elemTop = $(elem).offset().top;
            var elemBottom = helpers._elementBottom(elem);

            return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
        },

        /**
         * Checks if an element is heigher than the current browser viewport
         *
         * @param elem
         * @return {Boolean}
         * @private
         */
        _isHeigherThanView: function(elem) {
            return ($(window).height() <= elem.height())	;
        }

    };

    var methods = {

        top: function() {
            var elem;
            elem = $(this);

            if (!helpers._isScrolledIntoView(elem)) {
                helpers._scrollToTop(elem);
            }

            return this;
        },

        bottom: function() {
            var elem;
            elem = $(this);

            if (!helpers._isScrolledIntoView(elem)) {
                helpers._scrollToBottom(elem);
            }

            return this;
        },

        isInView: function() {
            var elem;
            elem = $(this);

            return helpers._isScrolledIntoView(elem);
        }

    };

    $.fn.scrollIntoView = function(method) {

        if ( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.scrollIntoView' );
        }

    };

})(jQuery);
