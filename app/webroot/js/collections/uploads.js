define([
    'underscore',
    'backbone',
    'models/upload'
], function(_, Backbone, UploadModel) {
    var UploadsCollection = Backbone.Collection.extend({

        model: UploadModel,

        initialize: function(options) {
           this.url = options.url + 'uploads/index/';
        }
    });

    return UploadsCollection;
});
