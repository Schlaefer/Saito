define([
  'underscore',
  'marionette',
  'models/app',
  'text!modules/uploader/templates/uploadItemTpl.html',
], function (
  _,
  Marionette,
  App,
  Template,
  ) {
    'use strict';

    const ItemView = Marionette.View.extend({
      className: 'card',

      regions: {
        rgForm: '.js-rgForm',
      },

      template: _.template(Template),

      ui: {
        btnDelete: '.js-btnDelete',
      },

      events: {
        'click @ui.btnDelete': '_delete',
      },

      initialize: function(options) {
        this.InsertVw = options.InsertVw || null;
      },

      onRender: function () {
        if (this.InsertVw) {
          this.showChildView('rgForm', new this.InsertVw({ model: this.model }));
        }
      },

      /**
       * deletes upload
       */
      _delete: function (event) {
        event.preventDefault();

        this.model.destroy({
          error: (model, response) => {
            msg = response.responseJSON.errors[0];
            App.eventBus.trigger('notification', {message: msg, type: 'error'});
          },
        });
      },
    });

    return ItemView;
  });
