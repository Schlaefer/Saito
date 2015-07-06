define([
    'underscore',
    'backbone',
    'models/thread',
    'models/app',
    'backboneLocalStorage',
    'lib/saito/localStorageHelper'
], function (_, Backbone, ThreadModel, App) {
    'use strict';

    var ThreadCollection = Backbone.Collection.extend({

        model: ThreadModel,

        localStorage: (function () {
            var key = App.reqres.request('app:localStorage:key', 'Threads');
            return new Backbone.LocalStorage(key);
        })(),

        fetch: function (options) {
            if (App.reqres.request('app:localStorage:available')) {
                return Backbone.Model.prototype.fetch.call(this, options);
            }
        }

    });

    return ThreadCollection;

});
