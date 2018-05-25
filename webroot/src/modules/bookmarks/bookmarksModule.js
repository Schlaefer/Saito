import _ from 'underscore';
import $ from 'jquery';
import Bb from 'backbone';
import Mn from 'backbone.marionette';
import App from 'models/app';
import BookmarksView from './views/bookmarksVw';
import EmptyView from 'views/noContentYetVw';
import SpinnerVw from 'views/spinnerVw';

export default Mn.View.extend({
  template: _.template(`
<div class="panel">
    <div class="panel-content">
        <div id="bookmarks">
          <div class="list-group">
            <div class="js-rgCollection"></div>
          </div>
        </div>
    </div>
</div>
  `),
  regions: {
    rgBookmarks: {
      el: '.js-rgCollection',
      replaceElement: true,
    },
  },
  onRender: function () {
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
  },
});
