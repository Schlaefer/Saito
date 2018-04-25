define(['underscore', 'app/vent'], function(_, EventBus) {
  'use strict';

  var LocalStorageHelper = function() { };

  _.extend(LocalStorageHelper.prototype, {

    _available: null,
    _prefix: 'saito-',

    available: function() {
      if (this._available === null) {
        this._available = this._isAvailable();
      }
      return this._available;
    },

    /**
     * Clears out all localStorage belonging to Saito FE
     *
     * @private
     */
    _clear: function() {
      if (!this.available()) {
        return;
      }
      var keys = Object.keys(localStorage);
      _.each(keys, function(key) {
        if (key.indexOf(this._prefix) === 0) {
          localStorage.removeItem(key);
        }
      }, this);
    },

    _key: function(key) {
      return this._prefix + key;
    },

    _isAvailable: function() {
      if (!('localStorage' in window)) {
        return false;
      }
      try {
        var testKey = 'localStorageAvailableTestKey',
            storage = window.localStorage;
        storage.setItem(testKey, '1');
        storage.removeItem(testKey);
        return true;
      } catch (error) {
        return false;
      }
      return false;
    }

  });

  var lSH = new LocalStorageHelper();

  EventBus.vent.reply('app:localStorage:available', lSH.available, lSH);
  EventBus.vent.reply('app:localStorage:key', lSH._key, lSH);
  EventBus.vent.on('app:localStorage:clear', lSH._clear, lSH);

});