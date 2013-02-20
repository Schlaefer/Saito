define([
    'jquery',
    'underscore',
    'backbone',
    'models/appSetting',
    'collections/uploads', 'views/upload',
    'views/uploadNew',
    'text!templates/uploads.html'
], function($, _, Backbone,
            AppSetting,
    UploadsCollection, UploadView,
    UploadNewView,
    uploadsTpl
    ) {

    var UploadsView = Backbone.View.extend({

        // textarea the upload view will insert text into
        textarea: null,

        initialize: function(options) {
            this.textarea = this.textarea;

            this.collection = new UploadsCollection({
                url: AppSetting.get('webroot')
            });

            this.listenTo(this.collection, "reset", this._addAll);

            this.$('.body').html(_.template(uploadsTpl));

            this.uploadNewView = new UploadNewView({
                el: this.$('.upload_new_c'),
                collection: this.collection
            });

            this.render();
            this.collection.fetch();
        },

        _addOne: function(upload) {
            var uploadView = new UploadView({
                model: upload
            })
            this.$(".uploads_c").append(uploadView.render().el);
        },

        _addAll: function() {
            this.$(".uploads_c").empty();
            this.collection.each(this._addOne, this);
        },

        _setDialogSize: function() {
            this.$el.dialog("option", "width", window.innerWidth - 80 );
            this.$el.dialog("option", "height", window.innerHeight - 80 );
        },

        render: function() {
            this.uploadNewView.render();
            this.$el.dialog({
                title: $.i18n.__("Upload"),
                modal: true,
                draggable: false,
                resizable: false,
                position: [40, 40],
                hide: 'fade'
            });

            this._setDialogSize();
            $(window).resize(_.bind(function() {
                this._setDialogSize();
            }, this));
            window.onorientationchange = _.bind(function() {
                this._setDialogSize();
            }, this);
            return this;
        }

    });

    return UploadsView;

});
