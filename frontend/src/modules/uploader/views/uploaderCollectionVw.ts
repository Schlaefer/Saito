import { Collection, Model } from 'backbone';
import { CollectionView, View } from 'backbone.marionette';
import Blazy from 'blazy';
import $ from 'jquery';
import App from 'models/app';
import * as _ from 'underscore';
import { NoContentView as EmptyView } from 'views/NoContentView';
import { IUploaderOptions } from '../uploader';
import UploaderItemVw from './uploaderItemVw';

class UploaderClVw extends CollectionView<Model, View<Model>, Collection> {
    private blazy!: BlazyInstance;

    private throttledLoader: any;

    public constructor(options: IUploaderOptions) {
        _.defaults(options, {
            childView: UploaderItemVw,
            childViewEvents: {
                // new image was uploaded and inserted
                'dom:refresh': 'initLazyLoading',
            },
            childViewOptions: {
                permission: options.permission,
                userId: options.userId,
            },
            className: 'imageUploader-cards',
            emptyView: EmptyView,
            emptyViewOptions: () => {
                return {
                    model: new Model({ content: $.i18n.__('upl.ncy') }),
                };
            },
        });
        super(...arguments);
    }
    public onRender() {
        this.listenTo(App.eventBus, 'app:modal:shown', this.initLazyLoading);
        this.initLazyLoading();
    }

    /**
     * Initializes the lazy loading
     *
     * Throttle the initial dom:refresh-events from exisiting uploads
     */
    public initLazyLoading() {
        if (!this.throttledLoader) {
            this.throttledLoader = _.throttle(() => {
                // Uploader is displayed in modal dialog which isn't fully shown yet.
                // Blazy doesn't see those images and wont load them.
                const isVisisble = $('.imageUploader:visible').length > 0;
                if (!isVisisble) {
                    return;
                }

                if (this.blazy) {
                    this.blazy.revalidate();
                    return;
                }

                this.blazy = new Blazy({
                    // lazy load elements inside a scrolling container: selector of the container
                    // if you change test that lazy loading in modal dialog and static page is working
                    container: '#saito-modal-dialog',
                    success: (el) => {
                        // ugly hack to get to the parent here
                        $(el).parent().parent().find('.image-uploader-spinner').remove();
                    },
                });
            }, 300);
        }

        this.throttledLoader();
    }
}

export default UploaderClVw;
