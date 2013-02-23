define([
    'underscore',
    'backbone',
    'models/appSetting'
], function(_, Backbone,
    AppSettingModel
    ) {

    var AppModel = Backbone.Model.extend({

        /**
         * global event handler for the app
         */
        eventBus: null,

        initialize: function(options) {

            this.eventBus = _.extend({}, Backbone.Events);
            this.settings = new AppSettingModel();

        }


    });

    return new AppModel();
});
