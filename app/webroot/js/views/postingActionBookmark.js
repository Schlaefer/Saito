define(['jquery', 'underscore', 'marionette', 'models/app'],
    function($, _, Marionette, App) {
  'use strict';

  return Marionette.ItemView.extend({

    tagName: 'a',

    className: 'btn-bookmark-add',

    template: _.template('<i class="icon-large"></i>'),

    events: {
      'click': '_onClick'
    },

    modelEvents: {
      'change:isBookmarked': '_toggle'
    },

    initialize: function() {
      if (!this._shouldRender()) {
        return;
      }
      this.$el.attr('href', '#');
      this.render();
    },

    _shouldRender: function() {
      if (!App.currentUser.isLoggedIn()) {
        return false;
      }
      return true;
    },

    _onClick: function(event) {
      event.preventDefault();
      if (this.model.get('isBookmarked')) {
        window.location = App.settings.get('webroot') + 'bookmarks/index/#' +
            this.model.get('id');
      } else {
        this.model.set('isBookmarked', true);
      }
    },

    _toggle: function() {
      var _$icon = this.$('i');
      if (this.model.get('isBookmarked')) {
        _$icon.removeClass('icon-bookmark-empty');
        _$icon.addClass('icon-bookmark');
        this.$el.attr('title', $.i18n.__('Entry is bookmarked'));
      } else {
        _$icon.removeClass('icon-bookmark');
        _$icon.addClass('icon-bookmark-empty');
        this.$el.attr('title', $.i18n.__('Bookmark the entry'));
      }
    },

    onRender: function() {
      this._toggle();
    }

  });

});