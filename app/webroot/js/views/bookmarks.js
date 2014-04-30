define([
  'jquery',
  'underscore',
  'backbone',
  'views/bookmark',
  'text!templates/no-content-yet.html'
], function($, _, Backbone, BookmarkView, NcyTemplate) {

  "use strict";

  var BookmarksView = Backbone.View.extend({

    initialize: function() {
      this.initCollectionFromDom('.js-bookmark', this.collection, BookmarkView);
      this._fillNoContentYet();

      this.listenTo(this.collection, 'bookmark.removed', this._fillNoContentYet);
    },

    _fillNoContentYet: function() {
      if (this.collection.isEmpty()) {
        this.$el.html(_.template(NcyTemplate));
      }
    }

  });

  return BookmarksView;

});
