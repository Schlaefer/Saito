import $ from 'jquery';
import _ from 'underscore';
import Backbone from 'backbone';
import BookmarkView from 'views/bookmark';
import NcyTemplate from 'templates/noContentYetTpl.html';

export default Backbone.View.extend({

  initialize: function () {
    this.initCollectionFromDom('.js-bookmark', this.collection, BookmarkView);
    this._fillNoContentYet();

    this.listenTo(this.collection, 'bookmark.removed', this._fillNoContentYet);
  },

  _fillNoContentYet: function () {
    if (this.collection.isEmpty()) {

      this.$el.html(_.template(NcyTemplate)({ content: $.i18n.__('ncy.bkm') }));
    }
  }

});
