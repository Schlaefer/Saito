import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as _ from 'underscore';
import { GeshiView } from './Geshi';
import { PostingRichtextEmbedModel, PostingRichtextEmbedView } from './postingRichtextEmbed';

class PostingRichtextView extends View<Model> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            template: _.noop,
        });
        super(options);
    }

    public onRender() {
        this.initGeshi('.geshi-wrapper');
        this.initEmbed('.js-embed');
    }

    private initGeshi(elementN: string) {
        const elements = this.$(elementN);
        if (!elements.length) {
            return;
        }
        elements.each((key, element) => {
            const view = new GeshiView({ el: element });
        });
    }

    private initEmbed(elementN: string) {
        const elements = this.$(elementN);
        if (!elements.length) {
            return;
        }
        elements.each((key, element) => {
            const id = element.getAttribute('id');
            const data = $(element).data('embed');

            this.addRegion(id, { el: '#' + id, replaceElement: true });
            const view = new PostingRichtextEmbedView({ model: new PostingRichtextEmbedModel(data) });
            this.showChildView(id, view);
        });
    }
}

export { PostingRichtextView };
