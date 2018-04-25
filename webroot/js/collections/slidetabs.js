define([
  'underscore',
  'backbone',
  'models/app',
  'models/slidetab'
], function(_, Backbone, App, SlidetabModel) {

  'use strict';

  var SlidetabCollection = Backbone.Collection.extend({

    model: SlidetabModel,

    initialize: function() {
      App.eventBus.on('slidetab:open', _.bind(this.isOpen, this));
    },

    // returns if particular slidetab is open or not
    isOpen: function(id) {
      return this.get(id).get('isOpen');
    }

  });

  return SlidetabCollection;

});
