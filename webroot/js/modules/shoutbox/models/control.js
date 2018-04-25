define(['underscore', 'backbone', 'models/app', 'lib/saito/localStorageHelper'], function(_, Backbone, App) {
  'use strict';

  var ShoutboxControlModel = Backbone.Model.extend({

    defaults: {
      // set an id to retrieve it again with Backbone.localStorage
      id: 1,
      // last shout-id rendered during this request
      lastId: 0,
      mar: 0,
      notify: false
    },

    localStorage: (function() {
      var key = App.eventBus.request('app:localStorage:key', 'shoutbox-control');
      return new Backbone.LocalStorage(key);
    })(),

    initialize: function() {
      this._restore(['notify', 'mar']);

      this.listenTo(this, 'change:notify', this._onChangeNotify);

      App.eventBus.on('shoutbox:mar', this._mar, this);
    },

    _mar: function(options) {
      options = options || {};
      _.defaults(options, {
        silent: false
      });
      this.set({mar: this.get('lastId')}, {silent: options.silent});
      this._save('mar');
    },

    setLastId: function(newLastId) {
      if (newLastId <= this.get('lastId')) {
        return;
      }
      this.set('lastId', newLastId);
    },

    _onChangeNotify: function() {
      this._save('notify');
    },

    _onChangeMar: function() {
      this._save('mar');
    },

    _restore: function(key) {
      if (_.isArray(key)) {
        _.each(key, function(e) { this._restore(e); }, this);
      }
      if (App.eventBus.request('app:localStorage:available')) {
        this.fetch();
      }
    },

    _save: function(key) {
      if (App.eventBus.request('app:localStorage:available')) {
        this.save();
      }
    }

  });

  return new ShoutboxControlModel();
});
