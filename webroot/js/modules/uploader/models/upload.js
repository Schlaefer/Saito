define([
  'underscore',
  'backbone',
], function (
  _,
  Backbone,
  ) {
    'use strict';
    return Backbone.Model.extend({
      /**
       * Bb respone parser
       */
      parse: function (response, options) {
        return response.attributes;
      },
    });
  });
