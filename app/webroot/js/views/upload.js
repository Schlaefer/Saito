define([
    'jquery',
    'underscore',
    'backbone',
    'text!templates/upload.html'
], function($, _, Backbone,
            uploadTpl
    ) {

    var UploadView = Backbone.View.extend({

        events: {
            "click .upload_box_delete": "_removeUpload"
        },

        initialize: function() {
            this.listenTo(this.model, "destroy", this._uploadRemoved )
        },

        _removeUpload: function(event) {
            event.preventDefault();
            this.model.destroy();
        },

        _uploadRemoved: function() {
            this.remove();
        },

        render: function() {
            this.$el.html(_.template(uploadTpl, this.model.toJSON()));
            return this;
        }

    });

    return UploadView;

});
