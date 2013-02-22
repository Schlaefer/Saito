define([
    'jquery',
    'underscore',
    'backbone',
    'lib/jquery.filedrop',
    'models/appSetting',
    'text!templates/uploadNew.html',
    'text!templates/spinner.html'
], function($, _, Backbone,
            Filedrop,
            AppSetting,
            uploadNewTpl,
            spinnerTpl
    ) {

    var UploadNewView = Backbone.View.extend({

        className: "box-content upload_box upload-new",

        wasChild: 'unset',

        events: {
            "change #Upload0File": "_uploadManual"
        },

        initialize: function(options) {
            this.collection = options.collection;
            this.eventBus = options.eventBus;
        },

        _initDropUploader: function() {

            this.$('.upload-layer').filedrop({
                maxfiles: 1,
                maxfilesize: AppSetting.get('upload_max_img_size'),
                url: AppSetting.get('webroot') + 'uploads/add',
                paramname: "data[Upload][0][file]",
                allowedfiletypes: [
                    'image/jpeg',
                    'image/jpg',
                    'image/png',
                    'image/gif'
                ],
                dragOver:_.bind(function(){this._showDragIndicator()}, this),
                dragLeave:_.bind(function(){this._hideDragIndicator()}, this),
                uploadFinished: _.bind(
                    function(i, file, response, time) {
                        this._postUpload();
                    },
                    this),
                beforeSend: _.bind(
                    function(file, i, done) {
                        this._hideDragIndicator();
                        this._setUploadSpinner();
                        done();
                    },
                    this),
                error: _.bind(function(err) {

                    this._hideDragIndicator();

                    this.eventBus.trigger(
                        'notification',
                        {
                            title: 'Error',
                            message: err,
                            type: 'error'
                        }
                    );

                    switch(err) {
                        case 'FileTypeNotAllowed':

                            break;
                        default:
                            break;
                    }
                }, this)
            });

        },

        _showDragIndicator: function() {
            this.$('.upload-drag-indicator').fadeIn();
        },

        _hideDragIndicator: function() {
            this.$('.upload-drag-indicator').fadeOut();
        },

        _setUploadSpinner: function() {
            this.$('.upload_box_header')
                .html(spinnerTpl);
        },

        _uploadManual: function(event) {
            event.preventDefault()

            this._setUploadSpinner();

            var formData = new FormData();
            formData.append(
                $('.dropbox input[type="file"]')[0].name,
                $('.dropbox input[type="file"]')[0].files[0]
            );

            var xhr = new XMLHttpRequest();
            xhr.open(
                'POST',
                AppSetting.get('webroot') + 'uploads/add',
                true
            );
            xhr.onload = _.bind(function() {
                this._postUpload();
            }, this);
            xhr.send(formData);
        },

        _postUpload: function() {
            this.collection.fetch();
            this.render();
        },

        render: function() {
            this.$el.html(_.template(uploadNewTpl)({
                upload_size: AppSetting.get('upload_max_img_size')
            }));
            this._initDropUploader();
            return this;
        }
    });

    return UploadNewView;

});
