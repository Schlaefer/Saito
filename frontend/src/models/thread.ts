import { Model } from 'backbone';
import ThreadLinesCollection from 'collections/threadlines';
import * as _ from 'underscore';

class ThreadModel extends Model {
    protected threadlines;

    public constructor(attributes, options) {
        _.defaults(options, {
            defaults: {
                isThreadCollapsed: false,
            },
        });
        super(attributes, options);
    }

    public initialize() {
        this.threadlines = new ThreadLinesCollection();
    }
}

export { ThreadModel };
