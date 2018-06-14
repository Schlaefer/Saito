import $ from 'jquery';
import _ from 'underscore';
import Bb, { Collection } from 'backbone';
import Mn from 'backbone.marionette';
import App from 'models/app';
import ModalDialog from 'modules/modalDialog/modalDialog';
import UploaderView from 'modules/uploader/uploader';
import MediaInsertView from 'views/mediaInsert';
import EditCountdown from 'views/editCountdown';
import PreviewModel from 'models/preview.ts';
import PreviewView from './preview.ts';
import { SmiliesCollectionView } from 'modules/answering/smilies.ts';
import autosize from 'autosize';
import 'lib/saito/jquery.scrollIntoView';
import 'lib/saito/jquery.insertAtCaret';
import { CakeFormErrorView } from 'lib/saito/CakeFormErrorView.ts';

export default Mn.View.extend({
  regions: {
    smilies: '.js-rgSmilies',
  },

  template: _.noop,

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
    'click .btn-markItUp-Smilies': '_handleSmilies',
    'keypress .js-subject': '_onKeyPressSubject',
    'input .js-subject': '_updateSubjectCharCount'
  },

  /**
   * Mn child region event listener
   */
  childViewEvents: {
    'answering:insert': '_insert',
  },

  initialize: function (options) {
    _.defaults(options, {
      // answering form was loaded via ajax request
      ajax: true,
      // answering form is in posting which is inline-opened
      parentThreadline: null
    });
    this.options = options;

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

  _handleSmilies: function (event) {
    event.preventDefault;

    const region = this.getRegion('smilies');
    if (!region.hasView()) {
      const view = new SmiliesCollectionView();
      view.collection.add(window.smiliesData);
      this.showChildView('smilies', view);
    }
    region.$el.collapse('toggle');
  },

  /**
   * Update char counter for remaining subject length
   *
   * @private
   */
  _updateSubjectCharCount: function () {
    const $count = this.$('.postingform-subject-count');
    const max = App.settings.get('subject_maxlength');
    const subject = this.$('.js-subject').val();
    // Should be _.chars(subject) for counting multibyte chars as one char only, but
    // <input> maxlength attribute also counts all bytes in multibyte char.
    // This shortends the allowed subject by one byte-char per multibyte char,
    // but we can life with that.
    const count = subject.length;
    const remaining = max - count;
    $count.html(remaining);

    const percent = count === 0 ? 0 : count/max * 100;
    const $progress = this.$('.js-progress');
    $progress.css('width', percent + '%');

    const setProgress = (cssClass) => {
      $progress
        .removeClass('bg-success bg-warning bg-danger')
        .addClass(cssClass);
    }

    let cssClass = 'bg-success';
    if (remaining === 0) {
      setProgress('bg-danger');
      _.delay(setProgress, 250, 'bg-warning');
      return;
    } else if (remaining < 20) {
      cssClass = 'bg-warning';
    }

    setProgress(cssClass);
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
          title: $.i18n.__('upl.btn.insert'),
        }
      },
      handleInsert: function (event) {
        event.preventDefault();
        const text = "[upload]" + this.model.get('name') + "[/upload]";
        answering._insert(text, { focus: false });
        ModalDialog.hide();
      },
    });

    const uploadsView = new UploaderView({
      el: '#markitup_upload',
      className: 'imageUploader',
      InsertVw: InsertVw,
    });

    ModalDialog.show(uploadsView, { title: $.i18n.__('upl.title'), width: 'max' });
    uploadsView.render();
  },

  /**
   * Inserts text at current cursor position in textfield.
   *
   * @param {string} text text to insert
   * @param {object} options addiontal options
   * - {bool} focus focus textfield after insertion
   * @private
   */
  _insert: function (text, options) {
    options = _.defaults(options, { focus: true });
    const textarea = this.$('textarea');
    textarea.insertAtCaret(text);
    autosize.update(textarea);

    options.focus ? textarea.focus() : textarea.blur();
  },

  _media: function (event) {
    event.preventDefault();

    this.mediaView = new MediaInsertView({ model: this.model });
    this.mediaView.render();
  },

  _showPreview: function (event) {
    var previewModel;
    event.preventDefault();
    this.$('.preview-wrapper').slideDown('fast');
    if (this.preview === false) {
      previewModel = new PreviewModel();
      this.preview = new PreviewView({
        el: this.$('.preview'),
        model: previewModel
      });
    }

    if (!this.errorVw) {
      this.errorVw = new CakeFormErrorView({
          el: this.$('form'),
          collection: new Collection(),
      });
    }

    this.preview.model.save(
      {
        category_id: this.$('#category-id').val(),
        pid: this.$('input[name=pid]').val(),
        html: null,
        subject: this.$('.js-subject').val(),
        text: this.$('textarea').val(),
      },
      {
        error: (model, response, options) => {
          // render preview empty
          model.set('html', '');

          if (!('errors' in response.responseJSON)) {
            App.eventBus.trigger('notification', {
              // @todo l10n
              message: 'Error while fetching preview.',
              type: 'error',
            });

            return;
          }

          this.errorVw.collection.reset(response.responseJSON.errors);
        },
        success: (mode, response, options) => {
          this.errorVw.collection.reset();
        }
      }
    ).always(() => {
          this.errorVw.render();
          this.preview.render();
    });
  },

  _closePreview: function (event) {
    event.preventDefault();
    this.$('.preview-wrapper').slideUp('fast');
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
    var _entry;
    this._setupTextArea();
    this._updateSubjectCharCount();

    const data = this.$('.js-data').data();
    if (_.property(['meta', 'action'], data) && data.meta.action === 'edit') {
      this.model.set('time', data.entry.time);
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
    event.preventDefault();

    const data = this.$('#EntryAddForm').serialize();

    const success = _.bind(function (data) {
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

    const fail = _.bind(function (jqXHR, text) {
      this.sendInProgress = false;
      this._enable();
      App.eventBus.trigger('notification', {
        title: text,
        type: 'error',
        message: jqXHR.responseText
      });
    }, this);

    const disable = _.bind(this._disable, this);

    $.ajax({
      url: this._requestUrl,
      type: 'POST',
      dataType: 'json',
      data: data, beforeSend: disable
    }).done(success).fail(fail);
  },

  onRender: function () {
    // create new thread on /entries/add
    if (this.options.ajax === false) {
      this._onFormReady();
    } else if (this.answeringForm === false) {
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
