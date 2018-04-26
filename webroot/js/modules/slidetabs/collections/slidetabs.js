define([
  'underscore',
  'backbone',
  'models/app',
  'modules/slidetabs/models/slidetab'
], function(_, Backbone, App, SlidetabModel) {

  'use strict';

  var SlidetabCollection = Backbone.Collection.extend({

    model: SlidetabModel,

    initialize: function() {
      App.eventBus.reply('slidetab:open', this.isOpen, this);
    },

    // returns if particular slidetab is open or not
    isOpen: function(id) {
      return this.get(id).get('isOpen');
    }

  });

  return SlidetabCollection;

});
