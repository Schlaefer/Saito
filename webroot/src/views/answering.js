import $ from 'jquery';
import _ from 'underscore';
import Backbone from 'backbone';
import Marionette from 'backbone.marionette';
import App from 'models/app';
import ModalDialog from 'modules/modalDialog/modalDialog';
import UploaderView from 'modules/uploader/uploader';
import MediaInsertView from 'views/mediaInsert';
import EditCountdown from 'views/editCountdown';
import PreviewModel from 'models/preview';
import PreviewView from 'views/preview';
import autosize from 'autosize';
import 'lib/saito/jquery.scrollIntoView';
import 'lib/saito/jquery.insertAtCaret';

export default Backbone.View.extend({

  _requestUrl: null,

  rendered: false,

  answeringForm: false,

  preview: false,

  sendInProgress: false,

  /**
   * current action
   *
   * either 'edit' or 'add'
   */
  _action: null,

  /**
   * same model as the parent PostingView
   */
  model: null,

  events: {
    'click .js-btnCite': '_handleCite',
    'click .js-btnPreviewClose': '_closePreview',
    'click .btn-preview': '_showPreview',
    'click .btn-markItUp-Upload': '_upload',
    'click .btn-markItUp-Media': '_media',
    'click .btn-primary': '_send',
    'keypress .js-subject': '_onKeyPressSubject',
    'input .js-subject': '_updateSubjectCharCount'
  },

  initialize: function (options) {
    _.defaults(options, {
      // answering form was loaded via ajax request
      ajax: true,
      // answering form is in posting which is inline-opened
      parentThreadline: null
    });
    this.options = options;

    if (this.options.ajax === false) {
      this._onFormReady();
    }

    this._requestUrl = App.settings.get('webroot') +
      'entries/add/' + this.model.get('id');

    // focus can only be set after element is visible in page
    this.listenTo(App.eventBus, 'isAppVisible', this._focusSubject);

    // auto-open upload view for easy developing
    // this._upload();
  },

  _disable: function () {
    this.$('.btn.btn-primary').attr('disabled', 'disabled');
  },

  _enable: function () {
    this.$('.btn.btn-primary').removeAttr('disabled');
  },

  /**
   * Quote parent posting
   *
   * @private
   */
  _handleCite: function (event) {
    event.preventDefault();
    const parentText = this.$('.js-btnCite').data('text');
    this._insert(parentText);
  },

  _onKeyPressSubject: function (event) {
    // intercepts sending to form's action url when inline answering
    if (event.keyCode === 13) {
      this._send(event);
    }
  },

  /**
   * Update char counter for remaining subject length
   *
   * @private
   */
  _updateSubjectCharCount: function () {
    var count, $count, max, subject;
    $count = this.$('.postingform-subject-count');
    max = App.settings.get('subject_maxlength');
    subject = this.$('.js-subject').val();
    // Should be _.chars(subject) for counting multibyte chars as one char only, but
    // <input> maxlength attribute also counts all bytes in multibyte char.
    // This shortends the allowed subject by one byte-char per multibyte char,
    // but we can life with that.
    count = max - subject.length;
    $count.html(count);
  },

  _upload: function (event) {
    if (event) {
      event.preventDefault();
    }

    const answering = this;
    const InsertVw = Marionette.View.extend({
      template: _.template('<button class="btn btn-primary"><%- title %></button>'),
      events: {
        'click button': 'handleInsert',
      },
      templateContext: () => {
        return {
          title: $.i18n.__('upload_btn_insert_into_posting'),
        }
      },
      handleInsert: function (event) {
        event.preventDefault();
        answering._insert("[upload]" + this.model.get('name') + "[/upload]");
        ModalDialog.hide();
      },
    });

    const uploadsView = new UploaderView({
      el: '#markitup_upload',
      className: 'imageUploader',
      textarea: this.$textarea[0],
      InsertVw: InsertVw,
    });

    ModalDialog.show(uploadsView, { title: $.i18n.__('Upload'), width: 'max' });
    uploadsView.render();
  },

  /**
   * Inserts text at current cursor position in textfield.
   *
   * @param {string} text text to insert
   * @private
   */
  _insert: function (text) {
    const textarea = this.$('textarea');
    textarea.insertAtCaret(text);
    autosize.update(textarea);
    textarea.focus();
  },

  _media: function (event) {
    event.preventDefault();

    this.mediaView = new MediaInsertView({
      model: this.model
    });
    this.mediaView.render();
  },

  _showPreview: function (event) {
    var previewModel;
    event.preventDefault();
    this.$('.preview').slideDown('fast');
    if (this.preview === false) {
      previewModel = new PreviewModel();
      this.preview = new PreviewView({
        el: this.$('.preview .panel-content'),
        model: previewModel
      });
    }
    this.preview.model.set('data', this.$('form').serialize());
  },

  _closePreview: function (event) {
    event.preventDefault();
    this.$('.preview').slideUp('fast');
  },

  _setupTextArea: function () {
    this.$textarea = this.$('textarea');
    autosize(this.$textarea);
  },

  _requestAnsweringForm: function () {
    $.ajax({
      // don't append timestamp to _requestUrl or Cake's
      // SecurityComponent will blackhole the ajax call in _sendInline()
      cache: true,
      url: this._requestUrl,
      success: _.bind(function (data) {
        this.answeringForm = data;
        this.render();
      }, this)
    });
  },

  _postRendering: function () {
    this.$el.scrollIntoView('bottom');
    this._focusSubject();
    this._onFormReady();
  },

  _onFormReady: function () {
    var _$data, _entry;
    this._setupTextArea();
    this._updateSubjectCharCount();

    _$data = this.$('.js-data');
    if (_$data.length > 0 && _$data.data('meta').action === 'edit') {
      _entry = this.$('.js-data').data('entry');
      this.model.set(_entry, { silent: true });
      this._addCountdown();
    }
    App.eventBus.trigger('change:DOM');
  },

  /**
   * Adds countdown to Submit button
   *
   * @private
   */
  _addCountdown: function () {
    var _$submitButton = this.$('.js-btn-primary');
    var editCountdown = new EditCountdown({
      el: _$submitButton,
      model: this.model,
      editPeriod: App.settings.get('editPeriod'),
      done: 'disable'
    });
  },

  _focusSubject: function () {
    // focus is broken in Mobile Safari iOS 8
    var iOS = window.navigator.userAgent.match('iPad|iPhone');
    if (iOS) {
      return;
    }

    this.$('.postingform input[type=text]:first').focus();
  },

  _send: function (event) {
    if (this.sendInProgress) {
      event.preventDefault();
      return;
    }
    this.sendInProgress = true;
    if (this.options.parentThreadline) {
      this._sendInline(event);
    } else {
      this._sendRedirect(event);
    }
  },

  _sendRedirect: function (event) {
    var submit;
    var button = this.$('.btn-primary')[0];
    event.preventDefault();
    if (typeof button.validity === 'object' &&
      button.form.checkValidity() === false) {
      // we can't trigger JS validation messages via form.submit()
      // so we create and click this hidden dummy submit button
      submit = _.bind(function () {
        if (!this.checkValidityDummy) {
          this.checkValidityDummy = $('<button></button>', {
            type: 'submit',
            style: 'display: none;'
          });
          $(button).after(this.checkValidityDummy);
        }
        this.checkValidityDummy.click();
      }, this);

      submit();
      this.sendInProgress = false;
    } else {
      button.disabled = true;
      button.form.submit();
    }
  },

  _sendInline: function (event) {
    var data, disable, fail, success;
    event.preventDefault();
    data = this.$('#EntryAddForm').serialize();
    success = _.bind(function (data) {
      this.model.set({ isAnsweringFormShown: false });
      if (this.options.parentThreadline !== null) {
        this.options.parentThreadline.set('isInlineOpened', false);
      }
      App.eventBus.trigger('newEntry', {
        tid: data.tid,
        pid: this.model.get('id'),
        id: data.id,
        isNewToUser: true
      });
    }, this);
    fail = _.bind(function (jqXHR, text) {
      this.sendInProgress = false;
      this._enable();
      App.eventBus.trigger('notification', {
        title: text,
        type: 'error',
        message: jqXHR.responseText
      });
    }, this);
    disable = _.bind(this._disable, this);

    $.ajax({
      url: this._requestUrl,
      type: 'POST',
      dataType: 'json',
      data: data, beforeSend: disable
    }).done(success).fail(fail);
  },

  render: function () {
    if (this.answeringForm === false) {
      this._requestAnsweringForm();
    } else if (this.rendered === false) {
      this.rendered = true;
      this.$el.html(this.answeringForm);
      _.defer(function (caller) {
        caller._postRendering();
      }, this);
    } else {
      App.eventBus.trigger('change:DOM');
    }
    return this;
  }

});
