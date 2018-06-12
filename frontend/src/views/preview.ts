import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import spinnerTpl from 'templates/spinner.html';
import * as _ from 'underscore';
import { PostingRichtextView } from './postingRichtext';

export default class extends View<Model> {
    protected template;

    constructor(options: any = {}) {
        options = _.extend(options, {
            modelEvents: {
                'change:html': 'render',
            },
            regions: {
                postingBody: '.postingBody-text',
            },
            template: _.template('<%= html %>'),
        });
        super(options);
    }

    public onRender() {
        if (!this.model.get('html')) {
            return;
        }
        const a = new PostingRichtextView({ el: this.$('.richtext') });
        this.showChildView('postingBody', a);
        a.render();
    }

    public getTemplate() {
        if (this.model.get('html') === null) {
            return spinnerTpl;
        }
        return this.template;
    }
}
