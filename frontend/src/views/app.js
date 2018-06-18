import $ from 'jquery';
import _ from 'underscore';
import Bb from 'backbone';
import Marionette from 'backbone.marionette';
import { AnsweringView } from 'views/answering.ts';
import App from 'models/app';
import CategoryChooserView from 'views/categoryChooser';
import { SaitoHelpView } from 'views/helps.ts';
import LoginView from 'views/loginView';
import ModalDialog from 'modules/modalDialog/modalDialog';
import PostingCollection from 'collections/postings';
import PostingLayout from 'views/postingLayout';
import PostingModel from 'models/posting';
import { SlidetabsView } from 'modules/slidetabs/slidetabs.ts';
import { ThreadCollection } from 'modules/thread/thread.ts';
import ThreadLineCollection from 'collections/threadlines';
import ThreadLineView from 'views/threadlines';
import ThreadView from 'views/thread';
import UserVw from 'modules/user/userVw.ts';
import 'lib/jquery-ui/jquery-ui.custom.min'

export default Marionette.View.extend({
  regions: {
    modalDialog: '#saito-modal-dialog',
    slidetabs: '#slidetabs',
  },

  autoPageReloadTimer: false,

  template: _.noop,

  _domInitializers: {
    '.entry.add-not-inline': '_initAnsweringNotInlined',
    '#slidetabs': '_initSlideTabs',
    '.js-entry-view-core': '_initPostings',
    '.threadBox': '_initThreadBoxes',
    '.threadLeaf': '_initThreadLeafs',
    '.js-rgUser': '_initUser',
  },

  ui: {
    'btnLogout': '#js-btnLogout',
    'btnCategoryChooser': '#btn-category-chooser',
  },

  events: {
    'click #showLoginForm': 'showLoginForm',
    'click .js-scrollToTop': 'scrollToTop',
    'click #btn-manuallyMarkAsRead': 'manuallyMarkAsRead',
    'click @ui.btnCategoryChooser': 'toggleCategoryChooser',
    'click @ui.btnLogout': 'handleLogout',
  },

  initialize: function () {
    this.threads = new ThreadCollection();
    if (App.request.controller === 'Entries' && App.request.action === 'index') {
      this.threads.fetch();
    }
    this.postings = new PostingCollection();

    // collection of threadlines not bound to thread (bookmarks, search results …)
    this.threadLines = new ThreadLineCollection();

    this.listenTo(App.eventBus, 'initAutoreload', this.initAutoreload);
    this.listenTo(App.eventBus, 'breakAutoreload', this.breakAutoreload);
  },

  initFromDom: function (options) {
    this.showChildView('modalDialog', ModalDialog);

    _.each(this._domInitializers, (initializer, element) => {
      const $elements = $(element);
      if ($elements.length > 0) {
        this[initializer]($elements);
      }
    });

    this.initAutoreload();
    this.initHelp();

    /*** All elements initialized, show page ***/

    App.status.start();
    this._showPage(options.SaitoApp.timeAppStart, options.contentTimer);
    App.eventBus.trigger('notification', options.SaitoApp.msg);

    /**
     * Scroll to thread on entries/index if indicated by URL jump parameter
     */
    if (window.location.href.indexOf('jump=') > -1) {
      var url = window.location.href;
      var jumpTarget = /[\?\&]jump=(\d+)/.exec(url);
      try {
        this.scrollToThread(jumpTarget[1]);
      }
      catch (error) {
      }
      finally {
        var newLocation = url.replace(/[\?\&]jump=\d+/, '');
        window.history.replaceState(null, null, newLocation);
      }
    }
  },

  _initUser: function (element) {
    const id = Number.parseInt(element.data('id'));
    const model = new Bb.Model({ id: id });
    const User = new UserVw({ el: element, model: model, });
    User.render();
  },

  _initSlideTabs: function (element) {
    this.showChildView('slidetabs', new SlidetabsView({ el: '#slidetabs' }));
  },

  /**
   * init the entries/add form where answering is not appended to a posting
   *
   * @param element
   * @private
   */
  _initAnsweringNotInlined: function (element) {
    this.answeringForm = new AnsweringView({
      el: element,
      model: new PostingModel({ id: 'foo' }),
      ajax: false
    }).render();
  },

  /**
   * Handles user-logout
   *
   * @private
   */
  handleLogout: function (event, element) {
    event.preventDefault();

    // clear JS-storage
    App.eventBus.trigger('app:localStorage:clear');

    // move on to server-logout
    _.defer(function () {
      const serverLogoutUrl = event.currentTarget.getAttribute('href');
      window.redirect(serverLogoutUrl);
    });
  },

  _initPostings: function (elements) {
    _.each(elements, function (element) {
      var id,
        postingLayout,
        postingModel;

      id = parseInt(element.getAttribute('data-id'), 10);
      postingModel = new PostingModel({ id: id });
      this.postings.add(postingModel, { silent: true });
      postingLayout = new PostingLayout({
        el: $(element),
        model: this.postings.get(id),
        collection: this.postings
      });
    }, this);
  },

  _initThreadBoxes: function (elements) {
    _.each(elements, (element) => {
      var threadView, threadId;

      threadId = parseInt($(element).attr('data-id'), 10);
      if (!this.threads.get(threadId)) {
        this.threads.add([
          {
            id: threadId,
            isThreadCollapsed: App.request.controller === 'entries' && App.request.action === 'index' && App.currentUser.get('user_show_thread_collapsed')
          }
        ], { silent: true });
      }
      threadView = new ThreadView({
        el: $(element),
        postings: this.postings,
        model: this.threads.get(threadId)
      });
    });

  },

  _initThreadLeafs: function (elements) {
    _.each(elements, (element) => {
      var leafData = JSON.parse(element.getAttribute('data-leaf'));
      // 'new' is 'isNewToUser' in leaf model; also 'new' is JS-keyword
      leafData.isNewToUser = leafData['new'];
      delete (leafData['new']);

      var threadsCollection = this.threads.get(leafData.tid);
      var threadlineCollection;
      if (threadsCollection) {
        // leafData belongs to complete thread on page
        threadlineCollection = threadsCollection.threadlines;
      } else {
        // leafData is not shown in its complete thread context (e.g. bookmark)
        threadlineCollection = this.threadLines;
      }

      var threadLineView = new ThreadLineView({
        el: $(element),
        leafData: leafData,
        postings: this.postings,
        collection: threadlineCollection
      });
    }, this);
  },

  _showPage: function (startTime, timer) {
    var triggerVisible = function () {
      App.eventBus.trigger('isAppVisible', true);
    };

    if (App.request.isMobile || (new Date().getTime() - startTime) > 1500) {
      $('#content').css('visibility', 'visible');
      triggerVisible();
    } else {
      $('#content')
        .css({ visibility: 'visible', opacity: 0 })
        .animate(
          { opacity: 1 },
          {
            duration: 150,
            /*
            easing: 'easeInOutQuart',
            */
            complete: triggerVisible
          });
    }
    timer.cancel();
  },

  toggleCategoryChooser: function (event, ui) {
    const categoryChooser = new CategoryChooserView();
    categoryChooser.model.set('isOpen', !categoryChooser.model.get('isOpen'));
  },

  initHelp: function (element_n) {
    new SaitoHelpView({
      el: '#shp-show',
      elementName: '.shp',
      webroot: App.settings.get('webroot')
    }).render();
  },

  scrollToThread: function (tid) {
    $('.threadBox[data-id=' + tid + ']')[0].scrollIntoView('top');
  },

  /**
   * initialize page autoreload
   */
  initAutoreload: function () {
    var period, reload, url;

    url = window.location.pathname;
    reload = (function () {
      window.location = url;
    });

    if (!App.settings.get('autoPageReload')) {
      return;
    }
    this.breakAutoreload();
    period = App.settings.get('autoPageReload') * 1000;
    this.autoPageReloadTimer = setTimeout(reload, period);
  },

  /**
   * break autoreload by clearing timer
   */
  breakAutoreload: function () {
    if (this.autoPageReloadTimer === false) {
      return;
    }
    clearTimeout(this.autoPageReloadTimer);
    this.autoPageReloadTimer = false;
  },

  showLoginForm: function (event) {
    event.preventDefault();
    const title = event.currentTarget.title;
    ModalDialog.show(new LoginView(), { title: title });
  },

  scrollToTop: function (event) {
    event.preventDefault();
    window.scrollTo({ behavior: 'smooth', top: 0 });
  },

  manuallyMarkAsRead: function (event) {
    if (event) {
      event.preventDefault();
    }
    window.redirect(App.settings.get('webroot') + 'entries/update');
  },
});
