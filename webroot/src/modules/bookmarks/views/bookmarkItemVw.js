import $ from 'jquery';
import _ from 'underscore';
import Mn from 'backbone.marionette';
import App from 'models/app';
import Tpl from '../templates/bookmarkItemTpl.html';
import AppView from 'views/app';

/**
 * Comment as text
 */
const CommentTextView = Mn.View.extend({
  template: _.template('<%- comment %>'),
  className: 'm-1',
});

/**
 * Comment as input
 */
const CommentInputView = Mn.View.extend({
  template: _.template('<input type="text" value="<%- comment %>">'),
  className: 'm-1',
  ui: {
    text: 'input',
  },
  events: {
    'keyup @ui.text': 'handleKeypress',
  },
  handleKeypress: function (event) {
    event.preventDefault();
    this.model.set('comment', this.getUI('text').val());
  },
  onRender: function() {
    this.getUI('text').focus();
  },
});

/**
 * Bookmark Item View
 */
export default Mn.View.extend({
  className: 'list-group-item flex-column align-items-start',

  regions: {
    rgComment: '.js-comment',
  },

  ui: {
    btnDelete: '.js-delete',
    btnEdit: '.js-edit',
    btnSave: '.js-save',
    comment: '.js-comment',
  },

  events: {
    'click @ui.btnDelete': 'handleDelete',
    'click @ui.btnEdit': 'handleEdit',
    'click @ui.btnSave': 'handleSave',
  },

  template: Tpl,

  onRender: function () {
    this.showChildView('rgComment', new CommentTextView({ model: this.model }));
    const av = new AppView();
    av._initThreadLeafs(this.$('.threadLeaf'));
  },

  handleEdit: function () {
    this.showChildView('rgComment', new CommentInputView({ model: this.model }));
    this.getUI('btnEdit').hide();
    this.getUI('btnSave').show();
  },

  handleSave: function () {
    this._deactivateInteractions();
    this.model.save(null, {
      success: () => {
        this.showChildView('rgComment', new CommentTextView({ model: this.model }));
        this._activateInteractions();
        this.getUI('btnSave').hide();
        this.getUI('btnEdit').show();
      },
      error: () => {
        this._activateInteractions();
        const notification = {
          message: $.i18n.__('bkm.save.failure'),
          code: 1527271165,
          type: 'error',
        };
        App.eventBus.trigger('notification', notification);
      },
    });
  },

  handleDelete: function () {
    this._deactivateInteractions();
    this.$el.hide('slide', null, 500);
    this.model.destroy({
      wait: true,
      error: () => {
        this._activateInteractions();
        this.$el.show('slide', null, 500);
        const notification = {
          message: $.i18n.__('bkm.delete.failure'),
          code: 1527277946,
          type: 'error',
        };
        App.eventBus.trigger('notification', notification);
      },
    });
  },

  /**
   * Deactivates all interaction buttons
   *
   * @private
   */
  _deactivateInteractions: function () {
    this.getUI('btnDelete').attr('disabled', 'disabled');
    this.getUI('btnEdit').attr('disabled', 'disabled');
    this.getUI('btnSave').attr('disabled', 'disabled');
  },

  /**
   * Activates all interaction buttons
   *
   * @private
   */
  _activateInteractions: function () {
    this.getUI('btnDelete').removeAttr('disabled');
    this.getUI('btnEdit').removeAttr('disabled');
    this.getUI('btnSave').removeAttr('disabled');

  },
});
