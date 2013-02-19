define([
    'jquery',
    'underscore',
    'backbone',
    'collections/uploads', 'views/upload',
    'text!templates/uploads.html'
], function($, _, Backbone,
    UploadsCollection, UploadView,
    uploadsTpl
    ) {

    var UploadsView = Backbone.View.extend({

        // textarea the upload view will insert text into
        textarea: null,

        initialize: function(options) {
            this.webroot = options.webroot;
            this.textarea = this.textarea;

            this.collection = new UploadsCollection({
                url: this.webroot
            });

            this.listenTo(this.collection, "reset", this._addAll);

            this.render();

            this.collection.fetch();
        },

        _getHtml: function() {
            $.ajax({
                url: this.webroot + 'uploads/index',
                success:_.bind(function(data) {
                    this.html = data;
                    this.render();
                }, this)
            });
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
            this.$('.body').html(_.template(uploadsTpl))
            this.$el.dialog({
                title: $.i18n.__("Upload"),
                autoOpen: true,
                modal: true,
                width: 850,
                draggable: false,
                resizable: false,
                height: $(window).height(),
                position: {
                    at: "center top"
                },
                hide: 'fade'
            });
            return this;
        }

    });

    return UploadsView;

});
