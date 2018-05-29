import _ from 'underscore';
import Mn from 'backbone.marionette';
import App from 'models/app';
import BookmarksVw from 'modules/bookmarks/bookmarksModule.ts';
import RecentPostsVw from './userRecentPostsVw.ts';
import UploaderVw from 'modules/uploader/uploader';

export default Mn.View.extend({
  template: _.template(`
    <ul class="nav nav-tabs" id="userTab" role="tablist">
      <li class="nav-item">
        <a class="js-btnRct nav-link active" id="recent-tab" data-toggle="tab" href="#recent" role="tab" aria-controls="home" aria-selected="true"><%- $.i18n.__('user.recentposts.t') %></a>
      </li>
      <% if (current) { %>
        <li class="nav-item">
          <a class="js-btnBkm nav-link" id="bookmarks-tab" data-toggle="tab" href="#bookmarks" role="tab" aria-controls="home" aria-selected="true"><%- $.i18n.__('bkm.title.pl') %></a>
        </li>
        <li class="nav-item">
          <a class="js-btnUpl nav-link" id="uploads-tab" data-toggle="tab" href="#uploads" role="tab" aria-controls="profile" aria-selected="false"><%- $.i18n.__('upl.title.pl') %></a>
        </li>
      <% } %>
    </ul>
    <div class="tab-content" id="userTabContent">
      <div class="js-rgRecentPosts tab-pane active" id="recent" role="tabpanel" aria-labelledby="home-tab"></div>
      <div class="js-rgBookmarks tab-pane" id="bookmarks" role="tabpanel" aria-labelledby="home-tab"></div>
      <div class="js-rgUploads tab-pane" id="uploads" role="tabpanel" aria-labelledby="profile-tab"></div>
    </div>
  `),
  templateContext: function () {
    return {
      current: App.currentUser.get('id') === this.model.get('id'),
    };
  },
  regions: {
    rgBookmarks: '.js-rgBookmarks',
    rgRecentPosts: '.js-rgRecentPosts',
    rgUploads: '.js-rgUploads',
  },
  ui: {
    btnBookmarks: '.js-btnBkm',
    btnRecentPosts: '.js-btnRct',
    btnUploads: '.js-btnUpl',
  },
  events: {
    'click @ui.btnBookmarks': 'handleBtnBookmarks',
    'click @ui.btnRecentPosts': 'scrollToTop',
    'click @ui.btnUploads': 'handleBtnUploads',
  },
  onRender: function () {
    this.showChildView('rgRecentPosts', new RecentPostsVw());
  },
  handleBtnBookmarks(event) {
    if (!this.getRegion('rgBookmarks').hasView()) {
      this.showChildView('rgBookmarks', new BookmarksVw());
    }
    this.scrollToTop();
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
