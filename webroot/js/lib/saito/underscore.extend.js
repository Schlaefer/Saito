define(['underscore'], function (_) {
    'use strict';

    _.mixin({
        /**
         * Calculate chars in string
         *
         * @param {string} string
         * @return int
         */
        chars: function (string) {
            var count, twoByteEmojis;
            twoByteEmojis = string.match(/(\uD83C[\uDF00-\uDFFF]|\uD83D[\uDC00-\uDFFF])/g) || [];
            count = string.length - twoByteEmojis.length;
            return count;
        }
    });
});
