define([
  'jquery',
  'underscore',
  'backbone',
  'app/vent',
  'models/app'
], function($, _, Backbone, EventBus, App) {

  'use strict';

  var SlidetabModel = Backbone.Model.extend({

    defaults: {
      isOpen: false
    },

    initialize: function() {
      this.webroot = App.settings.get('webroot');
      this.listenTo(this, 'change:isOpen', this.onChangeIsOpen);
    },

    onChangeIsOpen: function() {
      EventBus.vent.trigger('slidetab:open', {
          slidetab: this.get('id'),
          open: this.get('isOpen')
        }
      );
    },

    sync: function() {
      var key = 'show_' + this.get('id');
      $.post(
        this.webroot + 'users/slidetab_toggle',
        { slidetabKey: key }
      );
    }

  });

  return SlidetabModel;

});