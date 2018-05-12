define([
  'jquery',
  'underscore',
  'backbone',
  'views/bookmark',
  'text!templates/noContentYetTpl.html'
], function ($, _, Backbone, BookmarkView, NcyTemplate) {

  "use strict";

  var BookmarksView = Backbone.View.extend({

    initialize: function () {
      this.initCollectionFromDom('.js-bookmark', this.collection, BookmarkView);
      this._fillNoContentYet();

      this.listenTo(this.collection, 'bookmark.removed', this._fillNoContentYet);
    },

    _fillNoContentYet: function () {
      if (this.collection.isEmpty()) {

        this.$el.html(_.template(NcyTemplate)({content: $.i18n.__('ncy.bkm') }));
      }
    }

  });

  return BookmarksView;

});
