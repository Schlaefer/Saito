define([
    'underscore',
    'backbone',
    'models/appSetting',
    'cakeRest'
], function(_, Backbone,
    AppSettings
    ) {

    var UploadModel = Backbone.Model.extend({

        initialize: function() {
            this.webroot = AppSettings.get('webroot') + 'uploads/';
        }

    });

    _.extend(UploadModel.prototype, SaitoApp.Mixins.cakeRest)

    return UploadModel;
});
