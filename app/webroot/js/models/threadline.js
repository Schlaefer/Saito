define([
	'underscore',
	'backbone'
], function (_, Backbone) {

    "use strict";

    var ThreadLineModel = Backbone.Model.extend({
        defaults: {
            isContentLoaded: false,
            isInlineOpened: false,
            isAlwaysShownInline: false,
            isNewToUser: false
        },

        loadContent: function (options) {
            new ThreadLine(this.get('id')).load_inline_view(options);
            this.set('isContentLoaded', true);
        }
    });

    return ThreadLineModel;

});