define([
  'jquery',
  'underscore',
  'marionette',
  'modules/modalDialog/modalDialog',
], function (
  $,
  _,
  Marionette,
  ModalDialog
) {
    'use strict';

    return Marionette.View.extend({
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
  });
