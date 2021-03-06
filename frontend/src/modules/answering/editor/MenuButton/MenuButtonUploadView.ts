/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import BbcodeTag from 'lib/saito/Editor/Bbcode/BbcodeTag';
import App from 'models/app';
import ModalDialog from 'modules/modalDialog/modalDialog';
import UploaderVw from 'modules/uploader/uploader';
import { defaults, template } from 'underscore';
import { AbstractMenuButtonView } from './AbstractMenuButtonView';

/**
 * Button on upload-card which inserts the upload into the posting textfield
 */
class InsertVw extends View<Model> {
    /**
     * Constructor
     *
     * @param options marionette init
     */
    public constructor(options: object = {}) {
        options = defaults(options, {
            events: { 'click @ui.button': 'onButtonClick' },
            template: template(`
                <button class="btn btn-primary imageUploader-action-btn">
                    <%- $.i18n.__("upl.btn.insert") %>
                </button>`),
            ui: { button: 'button' },
        });
        super(options);
    }

    /**
     * Insert upload-BBCode into answering textarea
     */
    private onButtonClick() {
        const mime: string = this.model.get('mime').match('^(.*)?/')[1];
        let tag: string;

        switch (mime) {
            case('audio'):
            case('video'):
                tag = mime;
                break;
            case('image'):
                tag = 'img';
                break;
            default:
                tag = 'file';
        }

        const Tag = new BbcodeTag({
            attributes: 'src=upload',
            content: this.model.get('name'),
            tag,
        });
        this.getOption('channel').request('insert:text', Tag);

        ModalDialog.hide();
    }
}

class MenuButtonUploadView extends AbstractMenuButtonView {
    protected handleButton() {
        App.eventBus.reply(
            'uploader:item:action',
            () => {
                return new InsertVw({ channel: this.channel });
            });
        const uploadsView = new UploaderVw({
            permission: {
                'saito.plugin.uploader.add': true,
                'saito.plugin.uploader.delete': true,
                'saito.plugin.uploader.view': true,
            },
            userId: App.currentUser.get('id'),
        });

        ModalDialog.show(uploadsView, {
            title: $.i18n.__('upl.title'),
            trailing: true,
            width: 'max',
        });
    }
}

export { InsertVw, MenuButtonUploadView };
