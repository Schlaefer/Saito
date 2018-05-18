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

  start: function() {
    this._setWebroot(this.settings.get('webroot'));
    // Don't use SSE by default on unknown server-configs
    /*
    if (!!window.EventSource) {
      this._eventStream();
      return;
    }
    */
    // disabled: nothing to poll at the moment without shoutbox
    // this._poll();
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
   * Requests status by polling with classic http request
   *
   * @private
   */
  _poll: function() {
    var resetRefreshTime,
        updateAppStatus,
        setTimer,
        timerId,
        stopTimer,
        refreshTimeAct,
        refreshTimeBase = 10000,
        refreshTimeMax = 90000;

    stopTimer = function() {
      if (timerId !== undefined) {
        clearTimeout(timerId);
      }
    };

    resetRefreshTime = function() {
      stopTimer();
      refreshTimeAct = refreshTimeBase;
    };

    setTimer = function() {
      timerId = setTimeout(
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

    updateAppStatus();
    resetRefreshTime();
    setTimer();
  }

});

_.extend(AppStatusModel.prototype, cakeRest);

export default AppStatusModel;
