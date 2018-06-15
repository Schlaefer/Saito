import { Model } from 'backbone';
import ThreadLinesCollection from 'collections/threadlines';
import * as _ from 'underscore';

import { Collection } from 'backbone';
import { LocalStorage } from 'backbone.localstorage';
import 'lib/saito/localStorageHelper';
import App from 'models/app';

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

class ThreadCollection extends Collection<ThreadModel> {
    public model = ThreadModel;

    public localStorage: LocalStorage = (() => {
        const key = App.eventBus.request('app:localStorage:key', 'Threads');
        return new LocalStorage(key);
    })();

    /*
    fetch: function (options) {
      if (App.eventBus.request('app:localStorage:available')) {
        return Backbone.Model.prototype.fetch.call(this, options);
      }
    }
    */

}

export { ThreadCollection };
