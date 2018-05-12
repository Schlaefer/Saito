define([
  'underscore',
  'backbone',
  'models/app',
  'modules/uploader/models/upload'
], function (
  _,
  Backbone,
  App,
  UploadModel,
  ) {
    'use strict';
    return Backbone.Collection.extend({
      /** Bb collection model */
      model: UploadModel,

      /**
       * Bb initializer
       */
      initialize: function (options) {
        this.url = App.settings.get('apiroot') + 'uploads/';
      },

      /**
       * Bb comparator
       */
      comparator: function (model) {
        // sort by latest firest (negate ID for DESC)
        return -1 * model.get('id');
      },

      /**
       * Bb response parser
       */
      parse: function (response, options) {
        return response.data;
      },
    });
  });
