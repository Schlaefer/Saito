import EventBus from 'app/vent';
import { View, ViewOptions } from 'backbone.marionette';
import { PostingModel } from 'modules/posting/models/PostingModel';
import * as _ from 'underscore';
import ActionView from 'views/postingAction';
import SliderView from 'views/PostingSliderView';
import { PostingContentView } from './postingContent';

interface IPostingLayoutViewOptions extends ViewOptions<PostingModel> {
    parentThreadline: PostingModel;
}

class PostingLayoutView extends View<PostingModel> {
    private parentThreadline: PostingModel;

    public constructor(options: IPostingLayoutViewOptions) {
        _.defaults(options, {
            parentThreadline: false,
            template: _.noop,
        });
        super(options);
    }

    public initialize(options) {
        this.parentThreadline = options.parentThreadline;
    }

    public onRender() {
        // grabs inline-data from html content
        const entry = this.$('.js-data').data('entry');
        this.model.set(entry, { silent: true });

        this.addRegion('body', '.postingBody');
        this.showChildView('body', new PostingContentView({
            el: this.$('.postingBody'),
            model: this.model,
        }));

        const answering = this.$('.postingLayout-actions').length;
        if (answering) {
            this.addRegion('actions', '.postingLayout-actions');
            this.showChildView('actions', new ActionView({
                el: this.$('.postingLayout-actions'),
                model: this.model,
            }));

            this.addRegion('slider', '.postingLayout-slider');
            this.showChildView('slider', new SliderView({
                collection: this.collection,
                el: this.$('.postingLayout-slider'),
                model: this.model,
                parentThreadline: this.parentThreadline,
            }));
        }

        EventBus.vent.trigger('Vent.Posting.View.afterRender', { $el: this.$el });
    }
}

export { PostingLayoutView };
