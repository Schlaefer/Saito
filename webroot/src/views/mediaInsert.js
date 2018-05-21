import $ from 'jquery';
import _ from 'underscore';
import Marionette from 'backbone.marionette';
import App from 'models/app';
import MarkItUpMedia from 'lib/saito/markItUp.media';
import ModalDialog from 'modules/modalDialog/modalDialog';
import mediaInsertTpl from 'templates/mediaInsert.html';

export default Marionette.View.extend({
  ui: {
    message: '#markitup_media_message',
    submit: '#markitup_media_btn',
    textarea: '#markitup_media_txta',
  },

  template: mediaInsertTpl,

  events: {
    "click @ui.submit": "_insert"
  },

  initialize: function () {
    if (this.model !== undefined && this.model !== null) {
      this.listenTo(this.model, 'change:isAnsweringFormShown', this.remove);
    }
  },

  _insert: function (event) {
    event.preventDefault();

    const markItUpMedia = MarkItUpMedia;
    const out = markItUpMedia.multimedia(
      this.getUI('textarea').val(),
      { embedlyEnabled: App.settings.get('embedly_enabled') === true }
    );

    if (out === '') {
      this._invalidInput();
    } else {
      $.markItUp({ replaceWith: out });
      this._closeDialog();
    }
  },

  _invalidInput: function () {
    this.getUI('message').show();
    ModalDialog.$el.effect('shake', { times: 2 }, 250);
  },

  _closeDialog: function () {
    ModalDialog.hide();
    this.destroy();
  },

  _showDialog: function () {
    ModalDialog.on('shown', () => { this.$('textarea').focus(); })
    ModalDialog.show(this, { title: $.i18n.__('Multimedia') });
  },

  onRender: function () {
    this._showDialog();
  }
});
