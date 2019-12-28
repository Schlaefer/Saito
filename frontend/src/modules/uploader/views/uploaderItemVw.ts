/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import App from 'models/app';
import { defaults } from 'underscore';
import AudioTpl from '../templates/uploadItemAudioTpl.html';
import GenericTpl from '../templates/uploadItemGenericTpl.html';
import ImageTpl from '../templates/uploadItemImageTpl.html';
import VideoTpl from '../templates/uploadItemVideoTpl.html';
import { IUploaderOptions } from '../uploader';
import UploaderItemFooterVw from './uploaderItemFooterVw';

class UploaderItemVw extends View<Model> {
    public constructor(options: IUploaderOptions) {
        options = defaults(options, {
            className: 'card imageUploader-card',
            regions: {
                footer: '.js-footer',
                rgForm: '.js-rgForm',
            },
        });
        super(options);
    }

    public getTemplate() {
        const type = this.model.get('mime').match('^(.*)?/')[1];

        switch (type) {
            case ('audio'):
                return AudioTpl;
            case ('image'):
                return ImageTpl;
            case ('video'):
                return VideoTpl;
            default:
                return GenericTpl;
        }
    }

    public onRender() {
        this.showChildView('footer', new UploaderItemFooterVw({
            model: this.model,
            permission: this.getOption('permission'),
            userId: this.getOption('userId'),
        }));

        const actionView = App.eventBus.request('uploader:item:action');
        if (actionView) {
            actionView.model = this.model;
            this.showChildView('rgForm', actionView);
        }
    }
}

export default UploaderItemVw;
