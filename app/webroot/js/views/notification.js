define([
  'jquery',
  'underscore',
  'backbone',
  'models/app',
  '../../dev/vendors/pnotify/jquery.pnotify'
], function($, _, Backbone, App) {

  "use strict";

  var NotificationView = Backbone.View.extend({

    initialize: function() {
      this.listenTo(App.eventBus, 'notification', this._showMessages);
      this.listenTo(App.eventBus, 'notificationUnset', this._unset);
    },

    /**
     * Handles message rendering
     *
     * options can be a single message:
     *
     * {
         *  `message` message to display,
         *  `title` "title (optional)",
         *  `type` "error|notice(default)|warning|success",
         *  `channel` "notification(default)|form"
         *  `element` ".input_selector" if `channel` is "form"
         * }
     *
     * or array with a msg property and a message list:
     *
     * {
         *  msg: [{message:…}, {message:…}]
         *  }
     *
     * @param options
     * @private
     */
    _showMessages: function(options) {
      if (options === undefined) {
        return;
      }
      if (options.msg === undefined) {
        if (options.message === undefined) {
          return;
        }
        options = {
          msg: [options]
        };
      } else if (options.msg.length === 0) {
        return;
      }

      _.each(options.msg, function(msg) {
        this._showMessage(msg);
      }, this);
    },

    /**
     * Renders a single message
     *
     * @param options single message
     * @private
     */
    _showMessage: function(msg) {
      msg.channel = msg.channel || "notification";
      // msg.title = msg.title || $.i18n.__(msg.type);

      switch (msg.channel) {
        case "form":
          this._form(msg);
          break;
        case "popover":
          this._popover(msg);
          break;
        default:
          this._showNotification(msg);
          break;
      }

    },

    _unset: function(msg) {
      if (msg === 'all') {
        $('.error-message').remove();
      }
    },

    _form: function(msg) {
      var tpl;
      tpl = _.template('<div class="error-message"><%= message %></div>');
      $(msg.element).after(tpl({message: msg.message}));
    },

    _showNotification: function(options) {
      var logOptions,
          delay;

      delay = 5000;

      logOptions = {
        title: options.title,
        text: options.message,
        icon: false,
        history: false,
        addclass: "flash",
        delay: delay
      };

      switch (options.type) {
        case 'success':
          logOptions.addclass += " flash-success";
          break;
        case 'warning':
          logOptions.addclass += " flash-warning";
          break;
        case 'error':
          logOptions.addclass += " flash-error";
          logOptions.delay = delay * 2;
          // logOptions.hide = false;
          break;
        default:
          logOptions.addclass += " flash-notice";
          break;
      }

      $.pnotify(logOptions);

    }

  });

  return NotificationView;

});
