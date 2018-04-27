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

            template: _.template(Tpl),

            regions: {
                content: '#saito-modal-dialog-content',
            },

            events: {
                'click #saito-modal-dialog-close': 'closeDialog'
            },

            initialize: function () {
                this.model = new Backbone.Model({ title: '' });
            },

            /**
             * Closes the dialog
             */
            closeDialog: function (event) {
                if (event) event.preventDefault();
                $(document).unbind('keyup', this.escapeFct);
                // closes BS dialog
                this.$el.modal('hide');
                this.getRegion('content').empty();
                this.triggerMethod('close');
            },

            /**
             * Shows modal dialog with content
             * 
             * @param {Marionette.View} content
             * @param {Object} 
             */
            show: function (content, options) {
                this.model.set('title', options['title'] || '');
                this.render();
                this.escapeFct = _.bind(
                    function (event) {
                        if (event.keyCode !== 27) {
                            return;
                        }
                        this._closeClicked(event);
                    },
                    this
                );
                $(document).keyup(this.escapeFct);

                // puts content into dialog
                this.showChildView('content', content);
                // shows BS dialog
                this.$el.modal('show');
            },

            /*
            onClose: function () {
            }
            */
        });

        return new ModalDialogView();
    });