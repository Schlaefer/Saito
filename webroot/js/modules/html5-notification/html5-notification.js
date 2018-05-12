define([
  'models/app',
],
  function (
    App,
  ) {
    'use strict';

    return {
      /**
       * hides notification after this seconds
       */
      _hideAfter: 10,

      start: function () {
        App.eventBus.reply('app:html5-notification:activate', this._activate, this);
        App.eventBus.on('app:html5-notification:available', this._isEnabled, this);
        App.eventBus.on('html5-notification', this.notification);
      },

      notification: function (data) {
        var _isAppHidden = !App.eventBus.request('isAppVisible');
        data = _.defaults(data, {
          icon: App.settings.get('notificationIcon'),
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
            setTimeout(function () {
              notification.close();
            }, this._hideAfter * 1000);
          }
        }
      },

      _activate: function () {
        // Chrome does not support window.Notification.permission as of Chrome 30
        if ("permission" in window.Notification && window.Notification.permission !== 'granted') {
          window.Notification.requestPermission();
          return;
        } else {
          window.Notification.requestPermission();
        }

      },

      _isEnabled: function () {
        if ("Notification" in window) {
          return true;
        } else {
          return false;
        }
      }

    };

  });
