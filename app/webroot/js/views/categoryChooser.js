define([
  'jquery',
  'underscore',
  'backbone'
], function($, _, Backbone) {
  'use strict';

  return Backbone.View.extend({

    initialize: function() {
      this._initDialog();
    },

    _initDialog: function() {
      this.$el.dialog({
        autoOpen: false,
        show: {effect: 'scale', duration: 200},
        hide: {effect: 'fade', duration: 200},
        width: 400,
        title: $.i18n.__('Categories'),
        resizable: false
      });
    },

    _updateDialogPosition: function() {
      var $button = $('#btn-category-chooser'),
        $window = $(window),
        x = $button.offset().left + $button.width() -
          $window.scrollLeft() - 410,
        y = $button.offset().top - $window.scrollTop() +
          $button.height();

      this.$el.dialog({ position: [x, y] });
    },

    toggle: function() {
      if (this.$el.dialog('isOpen')) {
        this.$el.dialog('close');
      } else {
        this._updateDialogPosition();
        this.$el.dialog('open');
      }
    }

  });
});
