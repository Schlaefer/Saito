define(
    [
        'backbone',
        'marionette',
        'text!modules/modalDialog/templates/modalDialog.html',
    ],
    function (
        Backbone,
        Marionette,
        Tpl,
    ) {
        'use strict';

        const ModalDialogView = Marionette.View.extend({
            el: '#saito-modal-dialog',

            defaults: {
              width: 'normal',
            },

            template: _.template(Tpl),

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
              this.$el.modal('show');
            },

            hide: function() {
              this.$el.modal('hide');
            },

            setWidth: function (width) {
                switch (width) {
                  case 'max':
                    this.$('.modal-dialog').css('max-width', '95%');
                    break;
                  default:
                    this.$('.modal-dialog').css('max-width',  '');
                }
            },
        });

        return new ModalDialogView();
    });
