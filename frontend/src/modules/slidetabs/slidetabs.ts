import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import { InitFromDom } from 'lib/saito/InitFromDom';
import App from 'models/app';
import { SlidetabCollection, SlidetabView } from './slidetab';

class SlidetabsView extends View<Model> {
    public initialize() {
        new SlidetabCollection().fetch({
            success: (collection) => {
                InitFromDom.initCollectionFromDom('.slidetab', collection, SlidetabView);
                // Display slidetabs after they are open/closed. Avoids ugly UI rearengment.
                this.$el.css('visibility', '');
            },
        });
        this.makeSortable();
    }

    /**
     * Make slidetabs sortable and send order to server for sync
     */
    private makeSortable() {
        const sortable = this.$el.sortable({
            handle: '.slidetab-tab',
            start: (event, ui) => {
                this.$el.css('overflow', 'visible');
            },
            stop: (event, ui) => {
                this.$el.css('overflow', 'hidden');
            },
        });

        sortable.on('sortupdate', function(event, ui) {
            let slidetabsOrder = $(this).sortable('toArray', { attribute: 'data-id' });
            slidetabsOrder = slidetabsOrder.map((name) => {
                return 'slidetab_' + name;
            });
            // @todo make model/collection
            $.post(
                App.settings.get('webroot') + 'users/slidetabOrder',
                { slidetabOrder: slidetabsOrder },
            );
        });
    }
}

export { SlidetabsView };
