import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import _ from 'underscore';
import { SpinnerView } from 'views/SpinnerView';
import UploadsCollection from './collections/uploads';
import Tpl from './templates/uploaderTpl.html';
import UploaderAddVw from './views/uploaderAddVw';
import UploaderClVw from './views/uploaderCollectionVw';

class UploaderVw extends View<Model> {
    public constructor(options: object = {}) {
        _.defaults(options, {
            className: 'imageUploader',
            regions: {
                addRegion: '.js-imageUploader-add',
                collectionRegion: '.js-imageUploader-list',
            },
            template: Tpl,
        });
        super(options);
    }

    public initialize() {
        this.collection = new UploadsCollection();
    }

    public onRender() {
        this.showChildView('addRegion', new UploaderAddVw({ collection: this.collection }));
        this.showChildView('collectionRegion', new SpinnerView());

        this.collection.fetch({
            success: (collection) => {
                const clV = new UploaderClVw({ collection });
                this.showChildView('collectionRegion', clV);
            },
        });
    }
}

export default UploaderVw;
