define([
  'jquery',
  'underscore',
  'backbone',
  '../../dev/vendors/jquery-filedrop/jquery.filedrop',
  'models/app',
  'text!templates/uploadNew.html',
  'text!templates/spinner.html',
  'humanize'
], function($, _, Backbone, Filedrop, App, uploadNewTpl, spinnerTpl, humanize) {

  "use strict";

  var UploadNewView = Backbone.View.extend({

    className: "panel upload_box upload-new",

    wasChild: 'unset',

    events: {
      "change #Upload0File": "_uploadManual"
    },

    initialize: function(options) {
      this.uploadUrl = App.settings.get('webroot') + 'uploads/add';
      this.collection = options.collection;
    },

    _initDropUploader: function() {

      if (this._browserSupportsDragAndDrop() && window.FileReader) {
        this.$('.upload-layer').filedrop({
          maxfiles: 1,
          maxfilesize: App.settings.get('upload_max_img_size') / 1024,
          url: this.uploadUrl,
          paramname: "data[Upload][0][file]",
          allowedfiletypes: [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif'
          ],
          dragOver: _.bind(function() {this._showDragIndicator();}, this),
          dragLeave: _.bind(function() {this._hideDragIndicator();}, this),
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

            switch (err) {
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

    _browserSupportsDragAndDrop: function() {
      var div = this.$('.upload-layer')[0];
      return ('draggable' in div) || ('ondragstart' in div && 'ondrop' in div);
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
      var useAjax = true,
          formData,
          input;

      event.preventDefault();

      try {
        formData = new FormData();
        input = this.$('#Upload0File')[0];
        formData.append(
            input.name,
            input.files[0]
        );
      } catch (e) {
        useAjax = false;
      }

      this._setUploadSpinner();

      if (useAjax) {
        this._uploadAjax(formData);
      } else {
        this._uploadIFrame();
      }
    },

    // compatibility for
    // - iCab Mobile custom uploader on iOS
    // - <= IE 9
    _uploadIFrame: function() {
      var form = this.$('form'),
          iframe = this.$('#uploadIFrame');

      iframe.load(_.bind(function() {
        this._postUpload(iframe.contents().find('body').html());
        iframe.off('load');
      }, this));

      form.submit();
    },

    _uploadAjax: function(formData) {
      var xhr = new XMLHttpRequest();
      xhr.open(
          'POST',
          this.uploadUrl
      );
      xhr.onloadend = _.bind(function(request) {
        this._postUpload(request.target.response);
      }, this);
      xhr.onerror = this._onUploadError;
      xhr.send(formData);
    },

    _onUploadError: function() {
      App.eventBus.trigger('notification', {
        type: "error",
        message: $.i18n.__("upload_genericError")
      });
    },

    _postUpload: function(data) {
      if (_.isString(data)) {
        try {
          data = JSON.parse(data);
        } catch (e) {
          this._onUploadError();
        }
      }
      App.eventBus.trigger('notification', data);
      this.collection.fetch({reset: true});
      this.render();

    },

    render: function() {
      this.$el.html(_.template(uploadNewTpl)({
        url: this.uploadUrl,
        upload_size: humanize
            .filesize(App.settings.get('upload_max_img_size'))

      }));
      this._initDropUploader();
      return this;
    }
  });

  return UploadNewView;

});
