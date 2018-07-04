import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import ModalDialog from 'modules/modalDialog/modalDialog';
import UploaderView from 'modules/uploader/uploader';
import * as _ from 'underscore';
import { AbstractMenuButtonView } from './AbstractMenuButtonView';

class MenuButtonUploadView extends AbstractMenuButtonView {
    protected handleButton() {
        const channel = this.channel;

        class InsertVw extends View<Model> {
            public constructor(options: object = {}) {
                _.defaults(options, {
                    events: { 'click button': 'handleInsert' },
                    template: _.template('<button class="btn btn-primary"><%- $.i18n.__("upl.btn.insert") %></button>'),
                });
                super(options);
            }
            private handleInsert() {
                const text = '[upload]' + this.model.get('name') + '[/upload]';
                channel.request('insert:text', text);
                ModalDialog.hide();
            }
        }

        const uploadsView = new UploaderView({
            InsertVw,
            className: 'imageUploader',
        });

        ModalDialog.show(uploadsView, { title: $.i18n.__('upl.title'), width: 'max' });
        uploadsView.render();
    }
}

export { MenuButtonUploadView };
