define([
  'jquery',
  'underscore',
  'backbone'
], function($, _, Backbone) {

  "use strict";

  var BookmarkView = Backbone.View.extend({

    events: {
      'click .btn-bookmark-delete': 'deleteBookmark'
    },

    initialize: function() {
      _.bindAll(this, 'render');
      this.model.on('destroy', this.removeBookmark, this);
    },

    deleteBookmark: function(event) {
      event.preventDefault();
      this.model.destroy();
    },

    removeBookmark: function() {
      var collection = this.collection;
      this.$el.hide("slide", null, 500, function() {
        $(this).remove();
        collection.trigger('bookmark.removed');
      });
    }

  });

  return BookmarkView;
});
