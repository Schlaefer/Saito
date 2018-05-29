import * as _ from 'underscore';
import * as $ from 'jquery';
import * as Bb from 'backbone';
import * as Mn from 'backbone.marionette';
import App from 'models/app';
import BookmarksView from './views/bookmarksVw';
import EmptyView from 'views/noContentYetVw';
import SpinnerVw from 'views/spinnerVw';

export default class extends Mn.View<any> {
    constructor(options: any = {}) {
        options.template = _.template(`
<div class="panel">
    <div class="panel-content">
        <div id="bookmarks">
          <div class="list-group">
            <div class="js-rgCollection"></div>
          </div>
        </div>
    </div>
</div>
        `);
        options.regions = {
            rgBookmarks: {
                el: '.js-rgCollection',
                replaceElement: true,
            },
        };
        super(options);
    };

    public onRender() {
        this.showChildView('rgBookmarks', new SpinnerVw());
        App.currentUser.getBookmarks({
            success: collection => {
                const clV = new BookmarksView({ collection: collection });
                this.showChildView('rgBookmarks', clV);
            },
            error: () => {
                this.removeRegion('rgBookmarks');
                const notification = {
                    message: $.i18n.__('bkm.failure.loading'),
                    code: 1527230914,
                    type: 'error',
                };
                App.eventBus.trigger('notification', notification);
            }
        })
    };
};
