define(['underscore', 'backbone', 'models/app'], function(_, Backbone, App) {

  'use strict';

  var NotificationView = Backbone.View.extend({
    /**
     * hides notification after this seconds
     */
    _hideAfter: 10,

    initialize: function() {
      this.listenTo(App.eventBus, 'html5-notification', this.notification);
      App.commands.setHandler('app:html5-notification:activate', this._activate);
      App.reqres.setHandler('app:html5-notification:available', this._isEnabled, this);
    },

    notification: function(data) {
      var _isAppHidden = !App.reqres.request('isAppVisible');
      data = _.defaults(data, {
        // @todo
        icon: 'http://macnemo.de/wiki/uploads/Main/macnemo_iphone2.png',
        always: false
      });

      if (data.always || _isAppHidden) {
        // @todo browser support
        var notification = window.webkitNotifications.createNotification(
            data.icon,
            data.title,
            data.message
        );
        notification.show();

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
      // @todo browser support
      if (window.webkitNotifications.checkPermission() !== 0) {
        window.webkitNotifications.requestPermission();
      }
    },

    _isEnabled: function() {
      // @todo browser support
      if (window.webkitNotifications) {
        return true;
      } else {
        return false;
      }
    }

  });

  return NotificationView;

});