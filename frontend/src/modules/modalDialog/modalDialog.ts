/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';
import { View, ViewOptions } from 'backbone.marionette';
import App from 'models/app';
import Tpl from 'modules/modalDialog/templates/modalDialog.html';
import { defaults, delay } from 'underscore';

interface IModalDialogOptions {
    /**
     * Insert content after modal dialog is fully shown
     */
    trailing: boolean;
    title: string;
    width: string;
}

class ModalDialogView extends View<Model> {
    /**
     * View which will be rendered as main content into the modal dialog
     */
    protected contentView: View<Model>|undefined;

    private defaults: Partial<IModalDialogOptions>;

    public constructor(options: any = {}) {
        options = defaults(options, {
            regions: {
                content: '#saito-modal-dialog-content',
            },
            template: Tpl,
        });
        super(options);
        this.defaults = {
            trailing: false,
            width: 'normal',
        };
        this.contentView = undefined;
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
    public show(content: View<Model>, options: Partial<IModalDialogOptions> = {}) {
        const showOptions = defaults(options, this.defaults) as IModalDialogOptions;
        this.model.set('title', options.title || '');
        this.render();

        this.setWidth(showOptions.width);

        if (showOptions.trailing) {
            // Insert content after showing the modal
            this.contentView = content;
        } else {
            // Insert content before showing the modal
            this.showContent(content);
        }

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
        delay(() => {
            this.$el.removeClass('animation shake', 1000);
        });
    }

    /**
     * Called after the modal dialog is fully shown
     */
    protected onShown() {
        // Show trailing
        if (this.contentView !== undefined) {
            this.showContent(this.contentView);
            this.contentView = undefined;
        }
    }

    /**
     * Shows content
     */
    protected showContent(contentView: View<Model>) {
        this.showChildView('content', contentView);
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
