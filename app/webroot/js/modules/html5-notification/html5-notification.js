define([
  'app/app',
  'marionette',
  'modules/html5-notification/views/notification'
],
    function(Application, Marionette, Notification) {

      "use strict";

      var Html5Notification = Application.module("html5-notification");

      Html5Notification.addInitializer(function(options) {
        var html5Notification = new Notification();
      });

      return Html5Notification;
    });