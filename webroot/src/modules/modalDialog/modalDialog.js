import _ from 'underscore';
import Backbone from 'backbone';
import Marionette from 'backbone.marionette';
import Tpl from 'modules/modalDialog/templates/modalDialog.html';

const dialog = Marionette.View.extend({
  // el: '#saito-modal-dialog',

  defaults: {
    width: 'normal',
  },

  template: Tpl,

  regions: {
    content: '#saito-modal-dialog-content',
  },

  initialize: function () {
    this.model = new Backbone.Model({ title: '' });
  },

  /**
   * Shows modal dialog with content
   *
   * @param {Marionette.View} content
   * @param {Object}
   */
  show: function (content, options) {
    options = _.defaults(options, this.defaults);
    this.model.set('title', options['title'] || '');
    this.render();

    // puts content into dialog
    this.showChildView('content', content);

    this.setWidth(options.width);

    // shows BS dialog
    this.$el.parent().modal('show');
  },

  hide: function () {
    this.$el.parent().modal('hide');
  },

  setWidth: function (width) {
    switch (width) {
      case 'max':
        this.$('.modal-dialog').css('max-width', '95%');
        break;
      default:
        this.$('.modal-dialog').css('max-width', '');
    }
  },
});

export default new dialog();
