define([
    'underscore',
    'backbone'
], function(_, Backbone) {

    /**
     * Singleton which holds the app settings
     */
    var AppSettingModel = Backbone.Model.extend({

    });

    return new AppSettingModel();
});
