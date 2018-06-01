import $ from 'jquery';
import _ from 'underscore';
import Marionette from 'backbone.marionette';
import App from 'models/app';
import ModalDialog from 'modules/modalDialog/modalDialog';

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

  template: _.template(`
<div class="panel">
  <div class="panel-content">
      <p>
          <%- $.i18n.__('tree.delete.confirm') %>
      </p>
  </div>
  <div class="panel-footer panel-form">
      <button class="btn btn-primary js-abort"><%- $.i18n.__('posting.delete.abort.btn') %></button>
      &nbsp;
      <button class="btn btn-link js-delete"><%- $.i18n.__('posting.delete.title') %></button>
  </div>
</div>

  `),

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
    ModalDialog.show(this, { title: $.i18n.__('posting.delete.title') });
  }
});
