define(['jquery', 'underscore', 'marionette', 'models/app'],
    function($, _, Marionette, App) {
  'use strict';

  return Marionette.View.extend({

    tagName: 'a',

    className: 'btn-bookmark-add btn-icon panel-footer-form-btn',

    template: _.template('<i class="fa fa-lg"></i>'),

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
        _$icon.removeClass('fa-bookmark-o');
        _$icon.addClass('fa-bookmark');
        this.$el.attr('title', $.i18n.__('Entry is bookmarked'));
      } else {
        _$icon.removeClass('fa-bookmark');
        _$icon.addClass('fa-bookmark-o');
        this.$el.attr('title', $.i18n.__('Bookmark the entry'));
      }
    },

    onRender: function() {
      this._toggle();
    }

  });

});