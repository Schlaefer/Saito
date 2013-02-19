define([
    'jquery',
    'underscore',
    'backbone',
    'models/appSetting',
    'collections/uploads', 'views/upload',
    'text!templates/uploads.html'
], function($, _, Backbone,
            AppSetting,
    UploadsCollection, UploadView,
    uploadsTpl
    ) {

    var UploadsView = Backbone.View.extend({

        // textarea the upload view will insert text into
        textarea: null,

        initialize: function(options) {
            this.textarea = this.textarea;
            console.log(AppSetting);

            this.collection = new UploadsCollection({
                url: AppSetting.get('webroot')
            });

            this.listenTo(this.collection, "reset", this._addAll);

            this.render();

            this.collection.fetch();
        },

        _addOne: function(upload) {
            var uploadView = new UploadView({
                model: upload
            })
            this.$(".content").append(uploadView.render().el);
        },

        _addAll: function() {
            this.collection.each(this._addOne, this);
        },

        render: function() {
            this.$('.body').html(
                _.template(uploadsTpl)({
                        upload_size: AppSetting.get('upload_max_img_size')
                    }
                )
            );
            this.$el.dialog({
                title: $.i18n.__("Upload"),
                autoOpen: true,
                modal: true,
                width: 830,
                draggable: false,
                resizable: false,
                height: $(window).height() - 40,
                hide: 'fade'
            });
            return this;
        }

    });

    return UploadsView;

});
