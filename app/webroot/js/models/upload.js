define([
    'underscore',
    'backbone',
    'cakeRest'
], function(_, Backbone) {

    var UploadModel = Backbone.Model.extend({

        initialize: function() {
            // Backbone.sync = Backbone.ajaxSync;
        }

    });

    _.extend(UploadModel.prototype, SaitoApp.Mixins.cakeRest)

    return UploadModel;
});
