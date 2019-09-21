import { Model } from 'backbone';

import { Collection } from 'backbone';
import { LocalStorage } from 'backbone.localstorage';
import App from 'models/app';
import * as _ from 'underscore';

import { View } from 'backbone.marionette';
import * as $ from 'jquery';

class SlidetabModel extends Model {
    public localStorage: LocalStorage = (() => {
        const key = App.eventBus.request('app:localStorage:key', 'Slidetab');
        return new LocalStorage(key);
    })();

    public defaults() {
        return {
            isOpen: false,
        };
    }
}

class SlidetabCollection extends Collection<SlidetabModel> {

    public localStorage: LocalStorage = (() => {
        const key = App.eventBus.request('app:localStorage:key', 'Slidetab');
        return new LocalStorage(key);
    })();

    public model = SlidetabModel;
}

class SlidetabView extends View<SlidetabModel> {
    public constructor(options: any) {
        _.defaults(options, {
            modelEvents: {
                'change:isOpen': 'toggleSlidetab',
            },
        });
        super(options);

        // apply initial open/close state without animation
        $.fx.off = true;
        this.toggleSlidetab();
        $.fx.off = false;
    }

    public events() {
        return {
            'click .slidetab-tab': 'clickSlidetab',
        };
    }

    private clickSlidetab() {
        this.model.save({ isOpen: !this.model.get('isOpen') });
    }

    private toggleSlidetab() {
        if (this.model.get('isOpen')) {
            this.show();
        } else {
            this.hide();
        }
    }

    private show() {
        this.$el.animate({ width: 280 });
        this.$el.addClass('is-open');
        this.$el.find('.slidetab-tab-info').hide();
    }

    private hide() {
        this.$el.animate(
            { width: 28 },
            () => { this.$el.removeClass('is-open'); },
        );
        this.$el.find('.slidetab-tab-info').show();
    }
}

export {
    SlidetabCollection,
    SlidetabView,
};
