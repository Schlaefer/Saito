import * as Mn from 'backbone.marionette';
import * as $ from 'jquery';
import App from 'models/app';
import * as _ from 'underscore';
import { SpinnerView } from 'views/SpinnerView';
import BookmarksCl from './collections/bookmarksCl';
import BookmarksView from './views/bookmarksVw';

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
    }

    public onRender() {
        this.showChildView('rgBookmarks', new SpinnerView());
        App.currentUser.getBookmarks({
            error: () => {
                this.removeRegion('rgBookmarks');
                const notification = {
                    code: 1527230914,
                    message: $.i18n.__('bkm.failure.loading'),
                    type: 'error',
                };
                App.eventBus.trigger('notification', notification);
            },
            success: (collection: BookmarksCl) => {
                const clV = new BookmarksView({ collection });
                this.showChildView('rgBookmarks', clV);
            },
        });
    }
}
