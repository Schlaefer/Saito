import { Model } from 'backbone';
import { View, ViewOptions } from 'backbone.marionette';
import _ from 'underscore';
import { SpinnerView } from 'views/SpinnerView';
import UploadsCollection from './collections/uploads';
import UploaderAddVw from './views/uploaderAddVw';
import UploaderClVw from './views/uploaderCollectionVw';

interface IUploaderPermissions {
    'saito.plugin.uploader.add': boolean;
    'saito.plugin.uploader.delete': boolean;
    'saito.plugin.uploader.view': boolean;
}

interface IUploaderOptions extends ViewOptions<Model> {
    userId: string;
    permission: IUploaderPermissions;
}

class UploaderVw extends View<Model> {
    public constructor(options: IUploaderOptions) {
        _.defaults(options, {
            className: 'imageUploader',
            regions: {
                addRegion: '.js-imageUploader-add',
                collectionRegion: '.js-imageUploader-list',
            },
            template: _.template('<div class="js-imageUploader-list"></div>'),
        });
        super(options);
    }

    public initialize() {
        this.collection = new UploadsCollection();
    }

    public onRender() {
        this.showChildView('collectionRegion', new SpinnerView());

        this.collection.fetch({
            data: {
                id: this.getOption('userId'),
            },
            success: (collection) => {
                const clV = new UploaderClVw({
                    collection,
                    permission: this.getOption('permission'),
                    userId: this.getOption('userId'),
                });
                if (this.getOption('permission')['saito.plugin.uploader.add']) {
                    const addVw = new UploaderAddVw({userId: this.getOption('userId'), collection});
                    clV.addChildView(addVw, 0);
                }
                this.showChildView('collectionRegion', clV);
            },
        });
    }
}

export default UploaderVw;
export {
    IUploaderOptions,
    UploaderVw,
};
