define([
    'underscore',
    'backbone',
    'models/app',
    'cakeRest'
], function(_, Backbone,
    App
    ) {

    var UploadModel = Backbone.Model.extend({

        initialize: function() {
            this.webroot = App.settings.get('webroot') + 'uploads/';
        }

    });

    _.extend(UploadModel.prototype, SaitoApp.Mixins.cakeRest)

    return UploadModel;
});
