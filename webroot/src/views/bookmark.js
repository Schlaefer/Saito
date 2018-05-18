import $ from 'jquery';
import _ from 'underscore';
import Backbone from 'backbone';

export default Backbone.View.extend({

  events: {
    'click .btn-bookmark-delete': 'deleteBookmark'
  },

  initialize: function () {
    _.bindAll(this, 'render');
    this.model.on('destroy', this.removeBookmark, this);
  },

  deleteBookmark: function (event) {
    event.preventDefault();
    this.model.destroy();
  },

  removeBookmark: function () {
    var collection = this.collection;
    this.$el.hide("slide", null, 500, function () {
      $(this).remove();
      collection.trigger('bookmark.removed');
    });
  }

});
