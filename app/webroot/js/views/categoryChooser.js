define([
  'jquery',
  'underscore',
  'backbone'
], function($, _, Backbone) {
  'use strict';

  return Backbone.View.extend({

    initialize: function() {
      var _$categoryChooser = $('#btn-category-chooser'),
        $window = $(window),
        x = _$categoryChooser.offset().left + _$categoryChooser.width() -
          $window.scrollLeft() - 410,
        y = _$categoryChooser.offset().top - $window.scrollTop() +
          _$categoryChooser.height();

      this.$el.dialog({
        autoOpen: false,
        show: {effect: 'scale', duration: 200},
        hide: {effect: 'fade', duration: 200},
        width: 400,
        position: [x, y],
        title: $.i18n.__('Categories'),
        resizable: false
      });
    },

    toggle: function() {
      if (this.$el.dialog('isOpen')) {
        this.$el.dialog('close');
      } else {
        this.$el.dialog('open');
      }
    }

  });
});
