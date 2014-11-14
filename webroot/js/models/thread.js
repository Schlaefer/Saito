define([
    'underscore',
    'backbone',
    'collections/threadlines'
], function(_, Backbone, ThreadLinesCollection) {

    "use strict";

    var ThreadModel = Backbone.Model.extend({

        defaults: {
            isThreadCollapsed: false
        },

        initialize: function() {
            this.threadlines = new ThreadLinesCollection();
        }

    });
    return ThreadModel;
});