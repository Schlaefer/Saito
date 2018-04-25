define([
  'app/core',
  'marionette',
  'modules/html5-notification/views/notification'
],
    function(Application, Marionette, Notification) {
      "use strict";

      // @todo
      return;
      var Html5Notification = Application.module("html5-notification");

      Html5Notification.addInitializer(function(options) {
        var html5Notification = new Notification({
          iconUrl: options.SaitoApp.app.settings.notificationIcon
        });
      });

      return Html5Notification;
    });