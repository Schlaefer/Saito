define([
  'underscore',
  'backbone',
  'backboneLocalStorage',
  'models/thread',
  'models/app',
  'lib/saito/localStorageHelper'
], function(_, Backbone, Store, ThreadModel, App) {
  'use strict';

  var ThreadCollection = Backbone.Collection.extend({

    model: ThreadModel,

    localStorage: (function() {
      if (App.reqres.request('app:localStorage:available')) {
        return new Store('Threads');
      }
    })(),

    fetch: function(options) {
      if (App.reqres.request('app:localStorage:available')) {
        return Backbone.Model.prototype.fetch.call(this, options);
      }
    }

  });

  return ThreadCollection;

});