define(
  ['jquery', 'underscore', 'marionette', 'models/app', 'text!templates/postingActionDelete.html'],
  function ($, _, Marionette, App, template) {
    'use strict';
    /**
     * Dialog for deleteing a posting.
     */
    return Marionette.ItemView.extend({
      ui: {
        abort: '.js-abort',
        submit: '.js-delete'
      },

      events: {
        'click @ui.abort': '_onAbort',
        'click @ui.submit': '_onSubmit'
      },

      template: _.template(template),

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
          title: $.i18n.__('Delete')
        });
      }
    });
  });
