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

    var NotificationView = Backbone.View.extend({

        initialize: function() {

            this.listenTo(App.eventBus, 'notification', this._showNotification);

        },

        _showNotification: function(options) {
            var logOptions;

            options.type = options.type || 'info';

            logOptions = {
                    title: options.title,
                    text: options.message,
                    icon: false,
                    history: false,
                    addclass: "flash"
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
                    logOptions.hide = false;
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
