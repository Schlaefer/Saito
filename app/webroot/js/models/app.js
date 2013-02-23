define([
    'underscore',
    'backbone',
    'models/appSetting'
], function(_, Backbone,
    AppSettingModel
    ) {

    var AppModel = Backbone.Model.extend({

        initialize: function(options) {

            this.settings = new AppSettingModel();

        }

    });

    return new AppModel();
});
