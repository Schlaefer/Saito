define(['underscore', 'backbone', 'models/app'], function(_, Backbone, App) {
  "use strict";

  var ShoutboxControlModel = Backbone.Model.extend({

    defaults: {
      // last shout-id rendered during this request
      lastId: 0,
      mar: 0,
      notify: false
    },

    initialize: function() {
      this._restore(['notify', 'mar']);

      this.listenTo(this, 'change:notify', this._onChangeNotify);

      App.commands.setHandler("shoutbox:mar", this._mar, this);
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
      if ('localStorage' in window) {
        this.set(key, JSON.parse(localStorage.getItem('shoutbox-' + key)));
      }
    },

    _save: function(key) {
      if ('localStorage' in window) {
        localStorage.setItem('shoutbox-' + key, JSON.stringify(this.get(key)));
      }
    }
  });

  return new ShoutboxControlModel();
});
