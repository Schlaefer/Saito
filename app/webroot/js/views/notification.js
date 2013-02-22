define([
    'jquery',
    'underscore',
    'backbone',
    'lib/humane'
], function($, _, Backbone,
            Humane
    ) {

    var NotificationView = Backbone.View.extend({

        initialize: function(options) {

            this.eventBus = options.eventBus;

            this.listenTo(this.eventBus, 'notification', this._showNotification);

        },

        _showNotification: function(options) {
            var logOptions = {
                    baseCls: "humane-jackedup",
                    timeout: 5000
                },
                notification;

            options.type = options.type || 'info';

            switch(options.type) {
                case 'warning':
                    logOptions.addnCls = 'humane-jackedup-warning';
                    break;
                case 'info':
                    logOptions.addnCls =  'humane-jackedup-success';
                    break;
                case 'error':
                    logOptions.addnCls =  'humane-jackedup-error';
                    logOptions.clickToClose =  true;
                    break;
                default:
                    break
            }

            notification = Humane.create(logOptions);
            notification.log(options.message, options.title);

        }

    });

    return NotificationView;

});
