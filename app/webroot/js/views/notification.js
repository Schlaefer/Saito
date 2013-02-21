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

            this.listenTo(this.eventBus, 'errorMsg', this._error);

        },

        _error: function(title, message) {
            var not;

            not = Humane.create({
                baseCls: 'humane-jackedup',
                addnCls: "humane-jackedup-error"
            })
            not.log(message, title);
        }

    });

    return NotificationView;

});
