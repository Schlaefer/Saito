define([
    'jquery',
    'underscore',
    'backbone',
    'models/app',
    'lib/humane'
], function($, _, Backbone,
            App,
            Humane
    ) {

    var NotificationView = Backbone.View.extend({

        initialize: function() {

            this.listenTo(App.eventBus, 'notification', this._showNotification);

        },

        _showNotification: function(options) {
            var logOptions,
                notification;

            options.type = options.type || 'info';

            logOptions = {
                    baseCls: "humane-jackedup",
                    addnCls: "flash flash-" + options.type,
                    clickToClose: true,
                    timeout: 4000
                };

            switch(options.type) {
                case 'error':
                    logOptions.clickToClose =  true;
                    logOptions.timeOut =  36000000;
                    break;
                default:
                    break;
            }

            notification = Humane.create(logOptions);
            notification.log(options.message, options.title);

        }

    });

    return NotificationView;

});
