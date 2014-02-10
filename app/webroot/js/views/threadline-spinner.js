define([
  'jquery',
  'underscore',
  'backbone'
], function($, _, Backbone) {

  "use strict";

  var ThreadlineSpinnerView = Backbone.View.extend({

    show: function() {
      this.$el.addClass('is-pulsing');
    },

    hide: function() {
      this.$el.removeClass('is-pulsing');
    }

  });

  return ThreadlineSpinnerView;

});
