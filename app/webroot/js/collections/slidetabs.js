define([
  'underscore',
  'backbone',
  'app/vent',
  'models/slidetab'
], function(_, Backbone, EventBus, SlidetabModel) {

  'use strict';

  var SlidetabCollection = Backbone.Collection.extend({

    model: SlidetabModel,

    initialize: function() {
      EventBus.reqres.setHandler('slidetab:open', _.bind(this.isOpen, this));
    },

    // returns if particular slidetab is open or not
    isOpen: function(id) {
      return this.get(id).get('isOpen');
    }

  });

  return SlidetabCollection;

});
