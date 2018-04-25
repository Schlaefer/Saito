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
        show: {effect: 'fade', duration: 200},
        hide: {effect: 'fade', duration: 200},
        title: $.i18n.__('Categories'),
        resizable: false,
        modal: true,
        position: {my: 'top', at: 'top'},
        width: '100%',
        draggable: false
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
