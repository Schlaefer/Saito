define([
    'jquery',
    'underscore',
    'backbone',
    'views/bookmark'
], function($, _, Backbone, BookmarkView) {
    var BookmarksView = Backbone.View.extend({

        initialize: function() {
            this.initCollectionFromDom('.js-bookmark', this.collection, BookmarkView);
        }

    });
    return BookmarksView;
});
