import $ from 'jquery';
import _ from 'underscore';
import Marionette from 'backbone.marionette';
import App from 'models/app';
import BmBtn from 'views/postingActionBookmark';
import DelModal from 'views/postingActionDelete';
import SolvesBtn from 'views/postingActionSolves';
import { EditCountdownView } from 'modules/answering/editCountdown';

export default Marionette.View.extend({

  ui: {
    'btnDelete': '.js-delete',
    'btnFixed': '.js-btn-toggle-fixed',
    'btnLocked': '.js-btn-toggle-locked',
  },

  events: {
    'click @ui.btnDelete': 'onBtnDelete',
    'click .js-btn-setAnsweringForm': 'onBtnAnswer',
    'click @ui.btnFixed': 'onToggleFixed',
    'click @ui.btnLocked': 'onToggleLocked'
  },

  _jsButtons: [BmBtn, SolvesBtn],

  initialize: function () {
    this._initFormElements();
    this.listenTo(this.model, 'change:isAnsweringFormShown', this._toggleAnsweringForm);
  },

  _initFormElements: function () {
    _.each(this._jsButtons, (View) => {
      this.$el.append(new View({ model: this.model }).$el);
    });
    var _$editButton = this.$('.js-btn-edit');
    if (_$editButton.length > 0) {
      var editCountdown = new EditCountdownView({
        el: _$editButton,
        model: this.model,
        editPeriod: App.settings.get('editPeriod')
      });
    }
  },

  onBtnAnswer: function (event) {
    event.preventDefault();
    this.model.set('isAnsweringFormShown', true);
  },

  /**
   * Delete posting button click
   */
  onBtnDelete: function (event) {
    var diag = new DelModal({ model: this.model }).render();
    event.preventDefault();
  },

  onToggleFixed: function (event) {
    event.preventDefault();
    this._sendToggle('fixed');
  },

  onToggleLocked: function (event) {
    event.preventDefault();
    this._sendToggle('locked');
  },

  // @todo move into model
  _sendToggle: function (key) {
    const id = this.model.get('id');
    const webroot = App.settings.get('webroot');
    const url = webroot +  'entries/ajaxToggle/' + id + '/' + key;

    $.ajax({ url: url, buffer: false })
      .done(function (data) {
        window.location.reload(true);
      });
  },

  _toggleAnsweringForm: function () {
    if (this.model.get('isAnsweringFormShown')) {
      this.$el.slideUp('fast');
    } else {
      this.$el.slideDown('fast');
    }
  }

});
