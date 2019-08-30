import $ from 'jquery';
import _ from 'underscore';
import Bb from 'backbone';
import Marionette from 'backbone.marionette';
import AnsweringView from 'modules/answering/answering.ts';
import AnswerModel from 'modules/answering/models/AnswerModel.ts';
import App from 'models/app';
import CategoryChooserVw from 'views/categoryChooserVw.ts';
import { SaitoHelpView } from 'views/helps.ts';
import LoginVw from 'views/loginVw.ts';
import ModalDialog from 'modules/modalDialog/modalDialog';
import NotificationView from 'modules/notification/notification.ts';
import PostingCollection from 'collections/postings';
import { PostingLayoutView as PostingLayout } from 'modules/posting/postingLayout.ts';
import { PostingModel } from 'modules/posting/models/PostingModel';
import { SlidetabsView } from 'modules/slidetabs/slidetabs.ts';
import { ThreadCollection } from 'modules/thread/thread.ts';
import ThreadLineCollection from 'collections/threadlines';
import { ThreadLineView } from 'views/ThreadLineView.ts';
import ThreadView from 'views/thread';
import UserVw from 'modules/user/userVw.ts';
import 'lib/jquery-ui/jquery-ui.custom.min';
import NavigationBreak from 'app/NavigationBreak';

export default Marionette.View.extend({
  regions: {
    modalDialog: '#saito-modal-dialog',
    slidetabs: '#slidetabs',
  },

  template: _.noop,

  _domInitializers: {
    '.js-answer-wrapper': '_initAnsweringNotInlined',
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
    this._initNotifications();
    const nv = new NavigationBreak();

    this.threads = new ThreadCollection();
    if (App.request.controller === 'Entries' && App.request.action === 'index') {
      this.threads.fetch();
    }
    this.postings = new PostingCollection();

    // collection of threadlines not bound to thread (bookmarks, search results …)
    this.threadLines = new ThreadLineCollection();
  },

  initFromDom: function (options) {
    this.showChildView('modalDialog', ModalDialog);

    _.each(this._domInitializers, (initializer, element) => {
      const $elements = $(element);
      if ($elements.length > 0) {
        this[initializer]($elements);
      }
    });

    this.initHelp();

    const autoPageReload = App.settings.get('autoPageReload');
    if (autoPageReload) {
      App.eventBus.request('app:autoreload:start', autoPageReload);
      const unread = $('.et-new').length;
      if (unread) {
        App.eventBus.request('app:favicon:badge', unread);
      }

    }

    /*** All elements initialized, show page ***/

    App.status.start(false);
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

  _initNotifications() {
      //noinspection JSHint
      const notificationElement = $('<div id="#notifications" aria-live="polite" aria-atomic="true">');
      this.$el.prepend(notificationElement)
      new NotificationView({el: notificationElement}).render();
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
    const data = {};
    const id = element.data('edit');
    if (id) {
      data.id = parseInt(id, 10);
    }
    const answeringForm = new AnsweringView({
      el: element,
      model: new AnswerModel(data),
    }).render();

    this.listenTo(answeringForm, 'answering:send:success', (model) => {
      const root = App.settings.get('webroot');
      window.redirect(root + 'entries/view/' + model.get('id'));
    });

    return answeringForm; // testing
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
      new PostingLayout({
        el: $(element),
        model: this.postings.get(id),
        collection: this.postings
      }).render();
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
    const categoryChooser = new CategoryChooserVw();
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

  showLoginForm: function (event) {
    event.preventDefault();
    const title = event.currentTarget.title;
    ModalDialog.once('shown', () => { this.$('#tf-login-username').focus(); });
    ModalDialog.show(new LoginVw(), { title: title });
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
