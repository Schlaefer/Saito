/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import $ from 'jquery';
import MarkupMultimedia from 'lib/saito/Editor/Bbcode/MediaInsert/markup.media';
import ModalDialog from 'modules/modalDialog/modalDialog';
import * as _ from 'underscore';
import mediaInsertTpl from './template/mediaInsert.html';

class MediaInsertView extends View<Model> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            events: {
                'click @ui.submit': '_insert',
            },
            template: mediaInsertTpl,
            ui: {
                message: '#markup_media_message',
                submit: '#markup_media_btn',
                textarea: '#markup_media_txta',
            },
        });
        super(options);
    }

    public onRender() {
        this._showDialog();
    }

    private _insert(event: Event) {
        event.preventDefault();

        const markupMedia = new MarkupMultimedia();
        const out = markupMedia.multimedia(String(this.getUI('textarea').val()));

        if (out === '') {
            this._invalidInput();

            return;
        }

        this.trigger('out', out);
        this._closeDialog();
    }

    private _invalidInput() {
        this.getUI('message').show();
        ModalDialog.invalidInput();
    }

    private _closeDialog() {
        ModalDialog.hide();
        this.destroy();
    }

    private _showDialog() {
        ModalDialog.once('shown', () => { this.$('textarea').focus(); });
        ModalDialog.show(this, { title: $.i18n.__('medins.title') });
    }

}

export { MediaInsertView };
