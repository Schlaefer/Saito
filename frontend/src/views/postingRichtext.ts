import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import GeshisCollection from 'collections/geshis';
import * as _ from 'underscore';
import GeshiView from 'views/geshi';

class PostingRichtextView extends View<Model> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            template: _.noop,
        });
        super(options);
    }

    public onRender() {
        this.initGeshi('.geshi-wrapper');
    }

    private initGeshi(elementN: string) {
        const geshiElements = this.$(elementN);
        if (!geshiElements.length) {
            return;
        }
        const geshis = new GeshisCollection();
        geshiElements.each((key, element) => {
            const view = new GeshiView({ el: element, collection: geshis });
        });
    }
}

export { PostingRichtextView };
