define(['underscore', 'backbone', 'models/app'],
    function(_, Backbone, App) {

  'use strict';

  var NotificationView = Backbone.View.extend({
    /**
     * hides notification after this seconds
     */
    _hideAfter: 10,

    _iconUrl: false,

    initialize: function(options) {
      this._iconUrl = options.iconUrl;
      this.listenTo(App.eventBus, 'html5-notification', this.notification);
      App.commands.setHandler('app:html5-notification:activate', this._activate, this);
      App.reqres.setHandler('app:html5-notification:available', this._isEnabled, this);
    },

    notification: function(data) {
      var _isAppHidden = !App.reqres.request('isAppVisible');
      data = _.defaults(data, {
        icon: this._iconUrl,
        always: false
      });

      if (data.always || _isAppHidden) {
        var notification = new window.Notification(data.title, {
          icon: data.icon,
          body: data.message
        });

        // prevents chrome to keep the notification on screen endlessly
        var isChrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
        if (isChrome) {
          setTimeout(function() {
            notification.close();
          }, this._hideAfter * 1000);
        }
      }
    },

    _activate: function() {
      // Chrome does not support window.Notification.permission as of Chrome 30
      if ("permission" in window.Notification && window.Notification.permission !== 'granted') {
        window.Notification.requestPermission();
        return;
      } else {
        window.Notification.requestPermission();
      }

    },

    _isEnabled: function() {
      if ("Notification" in window) {
        return true;
      } else {
        return false;
      }
    }

  });

  return NotificationView;

});