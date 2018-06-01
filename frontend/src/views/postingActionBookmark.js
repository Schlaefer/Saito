import $ from 'jquery';
import _ from 'underscore';
import Marionette from 'backbone.marionette';
import App from 'models/app';
import BookmarkModel from 'modules/bookmarks/models/bookmark';

export default Marionette.View.extend({

  tagName: 'btn',

  className: 'btn btn-link',

  template: _.template('<i class="fa fa-lg"></i>'),

  events: {
    'click': 'handleClick',
  },

  modelEvents: {
    'change:isBookmarked': '_toggle'
  },

  initialize: function () {
    if (!this._shouldRender()) {
      return;
    }
    this.$el.attr('href', '#');
    this.render();
  },

  _shouldRender: function () {
    if (!App.currentUser.isLoggedIn()) {
      return false;
    }
    return true;
  },

  handleClick: function () {
    const success = (bookmarks) => {
      if (this.model.get('isBookmarked')) {
        const model = bookmarks.findWhere({ 'entry_id': this.model.get('id') });
        model.destroy();
        this.model.set('isBookmarked', false);
      } else {
        bookmarks.create({
          'entry_id': this.model.get('id'),
          'user_id': App.currentUser.get('id'),
        })
        this.model.set('isBookmarked', true);
      }

    };
    App.currentUser.getBookmarks({ success: success });
  },

  _toggle: function () {
    var _$icon = this.$('i');
    if (this.model.get('isBookmarked')) {
      _$icon.removeClass('fa-bookmark-o');
      _$icon.addClass('fa-bookmark');
      this.$el.attr('title', $.i18n.__('bmk.isBookmarked'));
    } else {
      _$icon.removeClass('fa-bookmark');
      _$icon.addClass('fa-bookmark-o');
      this.$el.attr('title', $.i18n.__('bmk.doBookmark'));
    }
  },

  onRender: function () {
    this._toggle();
  }

});
