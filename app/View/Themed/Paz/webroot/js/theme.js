define(['jquery', 'underscore', 'marionette'], function($, _, Marionette) {

  'use strict';

  var HeaderView = Marionette.ItemView.extend({

    events: {
      "click #js-top-menu-open": "_topMenuOpen",
      "click #js-top-menu-close": "_topMenuClose"
    },

    _topMenuClose: function(event) {
      event.preventDefault();
      this.$el.addClass('headerClosed');
      localStorage.headerClosed = true;
    },

    _topMenuOpen: function(event) {

      event.preventDefault();
      _.defer(function() {
        window.scrollTo(0, 0);
      });
      this.$el.removeClass('headerClosed');
      localStorage.headerClosed = false;
    }

  });

  new HeaderView({el: $('body')});

});
