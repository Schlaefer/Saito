import $ from 'jquery';
import _ from 'underscore';
import Marionette from 'backbone.marionette';
import App from 'models/app';
import template from 'templates/postingActionDelete.html';

/**
 * Dialog for deleteing a posting.
 */
export default Marionette.View.extend({
  ui: {
    abort: '.js-abort',
    submit: '.js-delete'
  },

  events: {
    'click @ui.abort': '_onAbort',
    'click @ui.submit': '_onSubmit'
  },

  template: template,

  _onAbort: function (event) {
    event.preventDefault();
    this.$el.dialog('close');
    this.close();
  },

  _onSubmit: function (event) {
    var id, url;
    event.preventDefault();
    id = this.model.get('id');
    url = App.settings.get('webroot') + '/entries/delete/' + id;
    window.location = url;
  },

  onBeforeClose: function () {
    this.$el.dialog('destroy');
  },

  onRender: function () {
    this.$el.dialog({
      modal: true,
      position: ['center', 120],
      resizable: false,
      title: $.i18n.__('posting.delete.title')
    });
  }
});
