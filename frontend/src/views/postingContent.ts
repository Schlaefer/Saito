import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as _ from 'underscore';
import { PostingRichtextView } from './postingRichtext';

class PostingContentView extends View<Model> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            regions: {
                postingBody: '.postingBody-text',
            },
        });
        super(options);
        this.listenTo(this.model, 'change:isAnsweringFormShown', this._toggleAnsweringForm);
        this.listenTo(this.model, 'change:html', this.render);

        this.initRichtext();
    }

    public onRender() {
        this.$el.html(this.model.get('html'));
        this.initRichtext();
    }

    private initRichtext() {
        const a = new PostingRichtextView({ el: this.$('.postingBody-text .richtext') });
        this.showChildView('postingBody', a);
        a.render();
    }

    private _toggleAnsweringForm() {
        if (this.model.get('isAnsweringFormShown')) {
            // hide signature
            this.$('.postingBody-signature').slideUp('fast');
        } else {
            // show signature
            this.$('.postingBody-signature').slideDown('fast');
        }
    }
}

export { PostingContentView };
