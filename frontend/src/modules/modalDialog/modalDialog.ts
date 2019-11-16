import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import App from 'models/app';
import Tpl from 'modules/modalDialog/templates/modalDialog.html';
import _ from 'underscore';

class ModalDialogView extends View<Model> {
    // el: '#saito-modal-dialog',

    private defaults: object;

    public constructor(options: any = {}) {
        _.defaults(options, {
            regions: {
                content: '#saito-modal-dialog-content',
            },
            template: Tpl,
        });
        super(options);
        this.defaults = {
            width: 'normal',
        };
    }

    public initialize() {
        this.model = new Model({ title: '' });
    }

    /**
     * Shows modal dialog with content
     *
     * @param {Marionette.View} content
     * @param {Object}
     */
    public show(content: View<Model>, options: any) {
        options = _.defaults(options, this.defaults);
        this.model.set('title', options.title || '');
        this.render();

        // puts content into dialog
        this.showChildView('content', content);

        this.setWidth(options.width);

        // shows BS dialog
        this.$el.parent().on('shown.bs.modal', () => {
            App.eventBus.trigger('app:modal:shown');
            this.triggerMethod('shown');
        });
        this.$el.parent().on('hidden.bs.modal', () => {
            this.triggerMethod('hidden');
        });
        this.$el.parent().modal('show');
    }

    public hide() {
        this.$el.parent().modal('hide');
    }

    public onHidden() {
        this.getRegion('content').empty();
    }

    public invalidInput() {
        this.$el.addClass('animation shake');
        _.delay(() => {
            this.$el.removeClass('animation shake', 1000);
        });
    }

    private setWidth(width: string) {
        switch (width) {
            case 'max':
                this.$('.modal-dialog').css('max-width', '95%');
                break;
            default:
                this.$('.modal-dialog').css('max-width', '');
        }
    }
}

export default new ModalDialogView();
