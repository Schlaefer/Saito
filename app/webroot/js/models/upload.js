define([
    'underscore',
    'backbone',
    'models/app',
    'cakeRest'
], function(_, Backbone, App, cakeRest) {

    'use strict';

    var UploadModel = Backbone.Model.extend({

        initialize: function() {
            this.webroot = App.settings.get('webroot') + 'uploads/';
        }

    });

    _.extend(UploadModel.prototype, cakeRest);

    return UploadModel;
});
