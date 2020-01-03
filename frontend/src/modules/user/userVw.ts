import * as Bb from 'backbone';
import * as Mn from 'backbone.marionette';
import App from 'models/app';
import BookmarksVw from 'modules/bookmarks/bookmarksModule';
import UploaderVw from 'modules/uploader/uploader';
import * as _ from 'underscore';
import RecentPostsVw from './userRecentPostsVw';

export default class extends Mn.View<Bb.Model> {
    public constructor(options: any = {}) {
        options.template = _.template(`
<ul class="nav nav-tabs" id="userTab" role="tablist">
<li class="nav-item">
    <a class="js-btnRct nav-link active" id="recent-tab" data-toggle="tab" href="#recent" role="tab">
        <%- $.i18n.__('user.recentposts.t') %>
    </a>
</li>
<% if (current) { %>
    <li class="nav-item">
    <a class="js-btnBkm nav-link" id="bookmarks-tab" data-toggle="tab" href="#bookmarks" role="tab">
        <%- $.i18n.__('bkm.title.pl') %>
    </a>
    </li>
<% } %>
<% if (permission['saito.plugin.uploader.view']) { %>
    <li class="nav-item">
    <a class="js-btnUpl nav-link" id="uploads-tab" data-toggle="tab" href="#uploads" role="tab">
        <%- $.i18n.__('upl.title.pl') %>
    </a>
    </li>
<% } %>
</ul>
<div class="tab-content user-tabs" id="userTabContent">
    <div class="js-rgRecentPosts tab-pane active" id="recent" role="tabpanel" aria-labelledby="home-tab"></div>
    <div class="js-rgBookmarks tab-pane" id="bookmarks" role="tabpanel" aria-labelledby="home-tab"></div>
    <div class="js-rgUploads tab-pane" id="uploads" role="tabpanel" aria-labelledby="profile-tab"></div>
</div>
        `);
        options.ui = {
            btnBookmarks: '.js-btnBkm',
            btnRecentPosts: '.js-btnRct',
            btnUploads: '.js-btnUpl',
        };
        options.regions = {
            rgBookmarks: '.js-rgBookmarks',
            rgRecentPosts: '.js-rgRecentPosts',
            rgUploads: '.js-rgUploads',
        };
        options.events = {
            'click @ui.btnBookmarks': 'handleBtnBookmarks',
            'click @ui.btnRecentPosts': 'scrollToTop',
            'click @ui.btnUploads': 'handleBtnUploads',
        };

        super(...arguments);
    }

    public onRender() {
        this.showChildView('rgRecentPosts', new RecentPostsVw());
    }

    private templateContext() {
        return {
            current: App.currentUser.get('id') === this.model.get('id'),
            permission: this.model.get('permission'),
        };
    }

    private handleBtnBookmarks() {
        if (!this.getRegion('rgBookmarks').hasView()) {
            this.showChildView('rgBookmarks', new BookmarksVw());
        }
        this.scrollToTop();
    }

    private handleBtnUploads() {
        if (!this.getRegion('rgUploads').hasView()) {
            this.showChildView('rgUploads', new UploaderVw({
                permission: this.model.get('permission'),
                userId: this.model.get('id'),
            }));
        }
        this.scrollToTop();
    }

    /**
     * Scroll tabs to top of page
     */
    private scrollToTop() {
        _.delay(() => {
            window.scrollTo({ top: this.$el[0].offsetTop - 20, behavior: 'smooth' });
        }, 150);
    }
}
