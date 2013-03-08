define([
    'jquery',
    'underscore',
    'backbone',
    'lib/jquery.filedrop',
    'models/app',
    'text!templates/uploadNew.html',
    'text!templates/spinner.html',
    'humanize',
    'modernizr'
], function($, _, Backbone,
            Filedrop,
            App,
            uploadNewTpl, spinnerTpl,
            humanize
    ) {

    "use strict";

    var UploadNewView = Backbone.View.extend({

        className: "box-content upload_box upload-new",

        wasChild: 'unset',

        events: {
            "change #Upload0File": "_uploadManual"
        },

        initialize: function(options) {
            this.collection = options.collection;
        },

        _initDropUploader: function() {

            if (Modernizr.draganddrop && window.FileReader) {
                this.$('.upload-layer').filedrop({
                    maxfiles: 1,
                    maxfilesize: App.settings.get('upload_max_img_size') / 1024,
                    url: App.settings.get('webroot') + 'uploads/add',
                    paramname: "data[Upload][0][file]",
                    allowedfiletypes: [
                        'image/jpeg',
                        'image/jpg',
                        'image/png',
                        'image/gif'
                    ],
                    dragOver:_.bind(function(){this._showDragIndicator();}, this),
                    dragLeave:_.bind(function(){this._hideDragIndicator();}, this),
                    uploadFinished: _.bind(
                        function(i, file, response, time) {
                            this._postUpload(response);
                        },
                        this),
                    beforeSend: _.bind(
                        function(file, i, done) {
                            this._hideDragIndicator();
                            this._setUploadSpinner();
                            done();
                        },
                        this),
                    error: _.bind(function(err, file) {
                        var message;

                        this._hideDragIndicator();

                        switch(err) {
                            case 'FileTypeNotAllowed':
                                message = $.i18n.__('upload_fileTypeNotAllowed');
                                break;
                            case 'FileTooLarge':
                                message = $.i18n.__(
                                    'upload_fileToLarge',
                                    {name: file.name}
                                );
                                break;
                            case 'BrowserNotSupported':
                                message = $.i18n.__('upload_browserNotSupported');
                                break;
                            case 'TooManyFiles':
                                message = $.i18n.__('upload_toManyFiles');
                                break;
                            default:
                                message = err;
                                break;
                        }

                        App.eventBus.trigger(
                            'notification',
                            {
                                title: 'Error',
                                message: message,
                                type: 'error'
                            }
                        );
                    }, this)
                });
            } else {
                this.$('h2').html($.i18n.__('Upload'));
            }
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
            var formData,
                input;

            event.preventDefault();

            this._setUploadSpinner();

            formData = new FormData();
            input = $('.dropbox input[type="file"]')[0];
            formData.append(
                input.name,
                input.files[0]
            );

            var xhr = new XMLHttpRequest();
            xhr.open(
                'POST',
                App.settings.get('webroot') + 'uploads/add',
                true
            );
            xhr.onloadend = _.bind(function(request){
                var data;
                data = JSON.parse(request.target.response);
                this._postUpload(data);
            }, this);
            xhr.onload = _.bind(function() {
                this._postUpload();
            }, this);
            xhr.send(formData);
        },

        _postUpload: function(data) {
            App.eventBus.trigger('notification', data);
            this.collection.fetch();
            this.render();
        },

        render: function() {
            this.$el.html(_.template(uploadNewTpl)({
                upload_size: humanize
                    .filesize(App.settings.get('upload_max_img_size'))

            }));
            this._initDropUploader();
            return this;
        }
    });

    return UploadNewView;

});
