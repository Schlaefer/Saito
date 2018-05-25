import _ from 'underscore';
import Mn from 'backbone.marionette';
import BookmarksVw from 'modules/bookmarks/bookmarksModule';
import UploaderVw from 'modules/uploader/uploader';

export default Mn.View.extend({
  template: _.template(`
    <ul class="nav nav-tabs" id="userTab" role="tablist">
      <li class="nav-item">
        <a class="js-btnBkm nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true"><%- $.i18n.__('bkm.title.pl') %></a>
      </li>
      <li class="nav-item">
        <a class="js-btnUpl nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false"><%- $.i18n.__('upl.title.pl') %></a>
      </li>
    </ul>
    <div class="tab-content" id="userTabContent">
      <div class="js-rgBookmarks tab-pane active" id="home" role="tabpanel" aria-labelledby="home-tab"></div>
      <div class="js-rgUploads tab-pane" id="profile" role="tabpanel" aria-labelledby="profile-tab"></div>
    </div>
  `),
  regions: {
    rgBookmarks: '.js-rgBookmarks',
    rgUploads: '.js-rgUploads',
  },
  ui: {
    btnBookmarks: '.js-btnBkm',
    btnUploads: '.js-btnUpl',
  },
  events: {
    'click @ui.btnUploads': 'handleBtnUploads',
    'click @ui.btnBookmarks': 'scrollToTop',
  },
  onRender: function () {
    this.showChildView('rgBookmarks', new BookmarksVw());
  },
  handleBtnUploads(event) {
    if (!this.getRegion('rgUploads').hasView()) {
      this.showChildView('rgUploads', new UploaderVw());
    }
    this.scrollToTop();
  },
  /**
   * Scroll tabs to top of page
   *
   * @private
   */
  scrollToTop: function () {
    _.delay(() => {
      window.scrollTo({ top: this.$el[0].offsetTop - 20, behavior: 'smooth' });
    }, 150);
  },
});
