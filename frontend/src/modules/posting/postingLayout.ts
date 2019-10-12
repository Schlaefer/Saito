import EventBus from 'app/vent';
import { View, ViewOptions } from 'backbone.marionette';
import ThreadLineModel from 'models/threadline';
import { PostingModel } from 'modules/posting/models/PostingModel';
import * as _ from 'underscore';
import ActionView from 'views/postingAction';
import SliderView from 'views/PostingSliderView';
import { PostingContentView } from './postingContent';

interface IPostingLayoutViewOptions extends ViewOptions<PostingModel> {
    model: PostingModel;
    parentThreadline?: ThreadLineModel | null;
}

class PostingLayoutView extends View<PostingModel> {
    private parentThreadline: ThreadLineModel | null;

    public constructor(options: IPostingLayoutViewOptions) {
        _.defaults(options, {
            parentThreadline: null,
            template: _.noop,
        });

        super(options);

        this.parentThreadline = options.parentThreadline || null;
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
