define([
    'jquery',
    'underscore',
    'backbone',
    'text!templates/upload.html'
], function($, _, Backbone,
            uploadTpl
    ) {

    var UploadView = Backbone.View.extend({

        render: function() {
            this.$el.html(_.template(uploadTpl, this.model.toJSON()));
            return this;
        }

    });

    return UploadView;

});
