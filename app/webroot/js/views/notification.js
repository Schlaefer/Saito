define([
    'jquery',
    'underscore',
    'backbone',
    'models/app',
    'lib/jquery.pnotify'
], function($, _, Backbone,
            App,
            Humane
    ) {

    "use strict";

    var NotificationView = Backbone.View.extend({

        initialize: function() {
            this.listenTo(App.eventBus, 'notification', this._showNotifications);
        },

        /**
         * Handles notification output
         *
         * options can be a single message:
         *
         * {
         *  message: "message",
         *  title: "title (optional)",
         *  type: "success|warning|error|notice (optional)"
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
        _showNotifications: function(options) {
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
                this._showNotification(msg);
            }, this);
        },

        /**
         * Renders a single notification message
         *
         * @param options single message
         * @private
         */
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

            switch(options.type) {
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
