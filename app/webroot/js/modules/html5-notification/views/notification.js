define(['underscore', 'backbone', 'models/app'], function(_, Backbone, App) {

  'use strict';

  var NotificationView = Backbone.View.extend({
    // @todo test browser support
    _enabled: true,

    /**
     * hide notification after this seconds
     */
    _hideAfter: 4,

    initialize: function() {
      this.listenTo(App.eventBus, 'html5-notification', this.notification);
      App.commands.setHandler('app:html5-notification:activate', this._activate);
      App.reqres.setHandler('app:html5-notification:available', _.bind(this._isEnabled, this));
    },

    notification: function(data) {
      data = _.defaults(data, {
        // @todo
        icon: 'http://macnemo.de/wiki/uploads/Main/macnemo_iphone2.png',
        always: false
      });

      if (data.always || this._isAppHidden()) {
        // @todo browser support
        var notification = window.webkitNotifications.createNotification(
            data.icon,
            data.title,
            data.message
        );
        notification.show();
        // hide the notification after
        setTimeout(function(){
          notification.close();
        }, this._hideAfter * 1000);
      }
    },

    _isAppHidden: function() {
      // @todo browser support
      var hidden, isHidden = false;
      if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support
        hidden = "hidden";
      } else if (typeof document.webkitHidden !== "undefined") {
        hidden = "webkitHidden";
      }
      if (document[hidden]) {
        isHidden = document[hidden];
      }
      return isHidden;
    },

    _activate: function() {
      if (window.webkitNotifications.checkPermission() !== 0) {
        window.webkitNotifications.requestPermission();
      }
    },

    _isEnabled: function() {
      return this._enabled;
    }

  });

  return NotificationView;

});