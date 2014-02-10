define([
  'jquery',
  'underscore',
  'backbone'
], function($, _, Backbone) {

  "use strict";

  return Backbone.View.extend({

    initialize: function() {
      var _$categoryChooser = $('#btn-category-chooser'),
          _$window = $(window),
          _x = _$categoryChooser.offset().left + _$categoryChooser.width() -
              _$window.scrollLeft() - 410,
          _y = _$categoryChooser.offset().top - _$window.scrollTop() +
              _$categoryChooser.height();

      this.$el.dialog({
        autoOpen: false,
        show: {effect: "scale", duration: 200},
        hide: {effect: "fade", duration: 200},
        width: 400,
        position: [_x, _y],
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

