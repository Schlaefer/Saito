import $ from 'jquery';
import _ from 'underscore';
import Marionette from 'backbone.marionette';
import ModalDialog from 'modules/modalDialog/modalDialog';

export default Marionette.View.extend({
  template: '#tpl-categoryChooser',

  /**
   * Marionette model events
   */
  modelEvents: {
    'change:isOpen': '_handleStateIsOpen',
  },

  /**
   * Backbone initializer
   */
  initialize: function () {
    this.model = new Backbone.Model({ defaults: { isOpen: false } });
  },

  /**
   * Handle state of isOpen
   *
   * @param {Backbone.Model} model this model
   * @param {bool} value is open
   */
  _handleStateIsOpen: function (model, value) {
    if (!value) {
      return;
    }
    // show dialog
    ModalDialog.show(this, { title: $.i18n.__('Categories') });
    ModalDialog.on('close', () => {
      this.model.set('isOpen', false);
    });
  },
});
