import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import ModalDialog from 'modules/modalDialog/modalDialog';
import * as _ from 'underscore';
import tpl from './template/linkView.html';

class LinkView extends View<Model> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            events: {
                'click @ui.submit': 'insert',
                'keypress @ui.title': 'onKeypress',
                'keypress @ui.url': 'onKeypress',
            },
            template: tpl,
            ui: {
                submit: '.js-submit',
                title: '.link-title',
                url: '.link-url',
            },
        });
        super(options);
    }

    public onRender() {
        this.showDialog();
    }

    public onKeypress(event) {
        if (event.keyCode === 13) {
            this.insert();
        }
    }

    private closeDialog() {
        ModalDialog.hide();
        this.destroy();
    }

    private showDialog() {
        ModalDialog.once('shown', () => {
            if (!this.model.get('title')) {
                this.getUI('title').focus();
                return;
            }
            this.getUI('url').focus();
        });
        ModalDialog.show(this, { title: $.i18n.__('editor.link.t') });
    }

    private insert() {
        let title: string = String(this.getUI('title').val());
        const url: string = String(this.getUI('url').val());

        if (url === '') {
            ModalDialog.invalidInput();
            return;
        }

        title = title || url;

        this.trigger('link', {title, url});
        this.closeDialog();
    }
}

export { LinkView };
