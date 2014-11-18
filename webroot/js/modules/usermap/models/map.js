define(['backbone'], function(Backbone) {

  'use strict';

  var MapModel = Backbone.Model.extend({

    maxZoom: { edit: 14, single: 11, world: 11 },

    defaults: {
      lat: 30, lng: 0, zoom: 2, maxZoom: 11, minZoom: 1,
      maxBounds: [
        [-90, -180], /* south-east */
        [90, 180] /* north west */
      ]
    },

    initialize: function() {
      if (this.maxZoom[this.get('type')]) {
        this.set('maxZoom', this.maxZoom[this.get('type')]);
      }
    }

  });

  return MapModel;

});
