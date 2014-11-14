define([
    'underscore',
    'backbone',
    'models/bookmark'
], function(_, Backbone, BookmarkModel) {
    var BookmarkCollection = Backbone.Collection.extend({
        model: BookmarkModel
    });
    return BookmarkCollection;
});
