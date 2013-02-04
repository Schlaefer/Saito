define([
    'jquery',
    'underscore',
    'backbone',
    'views/bookmark'
], function($, _, Backbone, BookmarkView) {
    var BookmarksView = Backbone.View.extend({
        initialize: function() {
            // this.initCollectionFromDom();
        },

        initCollectionFromDom: function() {
            var createElement = _.bind(function(id, element) {
                this.collection.add({
                    id: id
                });
                new BookmarkView({
                    el: element,
                    model: this.collection.get(id)
                })
            }, this);

            this.$('.js-bookmark').each(function(){
                  createElement($(this).data('id'), this);
                }
            );
        }

    });
    return BookmarksView;
});
