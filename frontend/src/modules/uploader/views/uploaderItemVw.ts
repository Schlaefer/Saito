import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import App from 'models/app';
import * as _ from 'underscore';
import AudioTpl from '../templates/uploadItemAudioTpl.html';
import GenericTpl from '../templates/uploadItemGenericTpl.html';
import ImageTpl from '../templates/uploadItemImageTpl.html';
import VideoTpl from '../templates/uploadItemVideoTpl.html';
import { IUploaderOptions } from '../uploader';
import UploaderItemFooterVw from './uploaderItemFooterVw';

class UploaderItemVw extends View<Model> {
    public constructor(options: IUploaderOptions) {
        _.defaults(options, {
            className: 'card imageUploader-card',
            regions: {
                footer: '.js-footer',
                rgForm: '.js-rgForm',
            },
        });
        super(...arguments);
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
        } else {
            this.removeRegion('rgForm');
            this.$('.js-rgForm').remove();
        }

        //// delay display of loading spinner
        this.$('.image-uploader-spinner').css('visibility', 'hidden');
        _.delay(() => { this.$('.image-uploader-spinner').css('visibility', 'visible'); }, 2000);
    }
}

export default UploaderItemVw;
