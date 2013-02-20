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

        events: {
            "change #Upload0File": "_uploadManual"
        },

        initialize: function(options) {
            this._initDropUploader();
            this.collection = options.collection;
        },

        _initDropUploader: function() {

            this.$('.upload_box').filedrop({
                maxfiles: 1,
                maxfilesize: AppSetting.get('upload_max_img_size'),
                url: AppSetting.get('webroot') + 'uploads/add',
                paramname: "data[Upload][0][file]",
                uploadFinished: _.bind(function(i, file, response, time) {
                    this._postUpload();
                }, this),
                uploadStarted: _.bind(function(i, file, response, time) {
                    this._setUploadSpinner();
                }, this)
            });

        },

        _setUploadSpinner: function() {
            this.$('.upload_box')
                .html(spinnerTpl)
                .wrapInner('<div style="padding-top: 74px;"/>');
        },

        _uploadManual: function(event) {
            event.preventDefault()

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
            xhr.upload.onprogress = _.bind(function() {
                this._setUploadSpinner();
            }, this);
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
            return this;
        }
    });

    return UploadNewView;

});
