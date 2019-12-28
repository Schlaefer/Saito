/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Collection, Model } from 'backbone';
import { CollectionViewOptions, View } from 'backbone.marionette';
import { debounce, defaults, template, throttle } from 'underscore';
import { SpinnerView } from 'views/SpinnerView';
import LeftToRenderCl from './collections/leftToRenderCl';
import UploadsCollection from './collections/uploads';
import UploaderMenuMdl from './views/menu/uploaderMenuMdl';
import UploaderMenuVw from './views/menu/uploaderMenuVw';
import UploaderAddVw from './views/uploaderAddVw';
import UploaderClVw from './views/uploaderCollectionVw';

interface IUploaderPermissions {
    'saito.plugin.uploader.add': boolean;
    'saito.plugin.uploader.delete': boolean;
    'saito.plugin.uploader.view': boolean;
}

interface IUploaderOptions extends CollectionViewOptions<Model> {
    userId: string;
    permission: IUploaderPermissions;
}

class UploaderVw extends View<Model> {
    public collection!: UploadsCollection;
    /**
     * Temporary bucket filled with views left to render with lazy loading
     */
    protected collectionLeftToRender: LeftToRenderCl;

    protected throttledRender: () => void;

    protected uploaderClVw!: UploaderClVw;

    protected uploaderMenuMdl!: UploaderMenuMdl;

    public constructor(options: IUploaderOptions) {
        options = defaults(options, {
            className: 'imageUploader',
            collection: new UploadsCollection(),
            modelEvents: {
                'change:open': 'onChangeOpen',
            },
            regions: {
                collectionRegion: '.js-imageUploader-list',
                menuRg: '.js-menuRg',
            },
            template: template(`
                <div class="js-menuRg"></div>
                <div class="js-imageUploader-list"></div>
            `),
        });
        super(options);

        this.collectionLeftToRender = new LeftToRenderCl();

        // Debounce filter reset when many filter criteria change at once
        this.renderCollection = debounce(this.renderCollection, 20);
        this.throttledRender = throttle(() => this.iterativeRender(), 500);
    }

    public onRender() {
        this.showChildView('collectionRegion', new SpinnerView());

        this.collection.fetch({
            data: {
                id: this.getOption('userId'),
            },
            success: (collection) => {
                this.renderMenu();
                this.uploaderClVw = new UploaderClVw({
                    collection: new Collection(),
                    permission: this.getOption('permission'),
                    userId: this.getOption('userId'),
                });
                // Preset before first rendering (prevents "no content" display)
                this.resetCollectionToRender();
                this.showChildView('collectionRegion', this.uploaderClVw);
                // Render without delay
                this.iterativeRender();
                this.initLazyLoading();
            },
        });
    }

    public onDestroy() {
        document.removeEventListener('scroll', this.throttledRender);
        window.removeEventListener('resize', this.throttledRender);
    }

    protected initLazyLoading() {
        this.listenTo(this.collection, 'add', () => {
            this.uploaderMenuMdl.reset();
            this.renderCollection();
        });
        document.addEventListener('scroll', this.throttledRender, true);
        window.addEventListener('resize', this.throttledRender, true);
    }

    protected iterativeRender() {
        const shouldRenderAnother = (minimum: number = 5, belowFold: number = 10) => {
            const children = this.uploaderClVw.children as unknown as any;
            if (children.length <= minimum) {
                return true;
            }
            const view = children.last(belowFold + 1)[0];
            const rect = view.$el[0].getBoundingClientRect();
            const windowBottom = window.innerHeight;
            return rect.top < windowBottom;
        };

        while (shouldRenderAnother()) {
            if (this.collectionLeftToRender.length === 0) {
                break;
            }
            this.uploaderClVw.collection.add(this.collectionLeftToRender.shift());
        }
    }

    protected renderCollection() {
        this.resetCollectionToRender();
        this.iterativeRender();
    }

    protected resetCollectionToRender() {
        this.uploaderClVw.collection.reset();
        if (this.getOption('permission')['saito.plugin.uploader.add']) {
            const addView = new UploaderAddVw({
                collection: this.collection,
                userId: this.getOption('userId'),
            });
            this.uploaderClVw.addChildView(addView, 0);
        }
        this.collectionLeftToRender.reset(this.collection.getFiltered());
    }

    protected renderMenu() {
        this.uploaderMenuMdl = new UploaderMenuMdl();

        this.showChildView('menuRg', new UploaderMenuVw({
            collection: this.collection,
            model: this.uploaderMenuMdl,
        }));

        this.listenTo(this.uploaderMenuMdl, 'change:open', this.onChangeOpen);
        this.listenTo(this.uploaderMenuMdl, 'change:filterTitle', this.onChangeFilterTitle);
        this.listenTo(this.uploaderMenuMdl, 'change:filterType', this.onChangeFilterType);
        this.listenTo(this.uploaderMenuMdl, 'change:sort', this.onChangeSort);
    }

    protected onChangeOpen(model: UploaderMenuMdl) {
        if (!model.get('open')) {
            model.reset();
        }
    }

    protected onChangeFilterType(model: UploaderMenuMdl) {
        this.collection.uploadFilter.setMime(model.get('filterType'));
        this.renderCollection();
    }

    protected onChangeFilterTitle(model: UploaderMenuMdl) {
        this.collection.uploadFilter.setTitle(model.get('filterTitle'));
        this.renderCollection();
    }

    protected onChangeSort(model: UploaderMenuMdl) {
        this.collectionLeftToRender.setComparator(model.get('sort'));
        this.renderCollection();
    }

}

export default UploaderVw;
export {
    IUploaderOptions,
    UploaderVw,
};
