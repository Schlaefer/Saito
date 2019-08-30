/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import _ from 'underscore';
import Backbone from 'backbone';
import cakeRest from 'lib/saito/backbone.cakeRest';
import EventBus from 'app/vent';

const AppStatusModel = Backbone.Model.extend({

  stream: null,

  initialize: function(attributes, options) {
    this.settings = options.settings;
    this.methodToCakePhpUrl = _.clone(this.methodToCakePhpUrl);
    this.methodToCakePhpUrl.read = 'status/';
  },

  start: function(immediate = true) {
    this._setWebroot(this.settings.get('webroot'));
    // Don't use SSE by default on unknown server-configs
    /*
    if (!!window.EventSource) {
      this._eventStream();
      return;
    }
    */
    // slow polling just to keep the user online
    this._poll(90000, 180000, immediate);
  },

  _setWebroot: function(webroot) {
    this.webroot = webroot + 'status/';
  },

  /**
   * Request status by server-sent events
   *
   * @private
   */
  _eventStream: function() {
    this.stream = new EventSource(this.webroot + this.methodToCakePhpUrl.read);
    this.stream.addEventListener('message', _.bind(function(e) {
      /* @todo
        if (e.origin != 'http://example.com') {
        alert('Origin was not http://example.com');
        return;
        }
        */
      var data = JSON.parse(e.data);
      this.set(data);
    }, this), false);
  },

  /**
   * Requests status by polling with classic HTTP request.
   *
   * Adjust to sane values taking UserOnlineTable::setOnline() into account, so
   * that users wont get set offline.  Default current default values were great
   * for a shoutbox like feature with immediate and reasonbly fast polling.
   *
   * The time between requests increases if the data from the server is
   * unchanged.
   *
   * @param {int} refreshTimeBase - minimum and start time between request in ms
   * @param {int} refreshTimeMax - maximum time between requests in ms
   * @param {bool} immediate - first request immediately or after refreshTimeBase
   * @private
   */
  _poll: function(refreshTimeBase = 10000, refreshTimeMax = 90000, immediate = true) {
    var resetRefreshTime,
        updateAppStatus,
        setTimer,
        timerId,
        stopTimer,
        refreshTimeAct;

    stopTimer = function() {
      if (timerId !== undefined) {
        window.clearTimeout(timerId);
      }
    };

    resetRefreshTime = function() {
      stopTimer();
      refreshTimeAct = refreshTimeBase;
    };

    setTimer = function() {
      timerId = window.setTimeout(
          updateAppStatus,
          refreshTimeAct
      );
    };

    updateAppStatus = _.bind(function() {
      setTimer();
      this.fetch({
        success: function() {
          refreshTimeAct = Math.floor(
              refreshTimeAct * (1 + refreshTimeAct / 40000)
          );
          if (refreshTimeAct > refreshTimeMax) {
            refreshTimeAct = refreshTimeMax;
          }
        },
        error: stopTimer
      });
    }, this);

    this.listenTo(this, 'change', function() {
          resetRefreshTime();
          setTimer();
        }
    );

    if (immediate) {
      updateAppStatus();
    }

    resetRefreshTime();
    setTimer();
  }

});

_.extend(AppStatusModel.prototype, cakeRest);

export default AppStatusModel;
