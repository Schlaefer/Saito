import NavigationBreak from 'app/NavigationBreak';
import Bb, { Model } from 'backbone';
import { View } from 'backbone.marionette';
import PostingCollection from 'collections/postings';
import ThreadLineCollection from 'collections/threadlines';
import $ from 'jquery';
import 'lib/jquery-ui/jquery-ui.custom.min';
import App from 'models/app';
import AnsweringView from 'modules/answering/answering';
import AnswerModel from 'modules/answering/models/AnswerModel';
import ModalDialog from 'modules/modalDialog/modalDialog';
import NotificationView from 'modules/notification/notification';
import { PostingModel } from 'modules/posting/models/PostingModel';
import { PostingLayoutView as PostingLayout } from 'modules/posting/postingLayout';
import { SlidetabsView } from 'modules/slidetabs/slidetabs';
import { ThreadCollection } from 'modules/thread/thread';
import UserVw from 'modules/user/userVw';
import _ from 'underscore';
import CategoryChooserVw from 'views/categoryChooserVw';
import { SaitoHelpView } from 'views/helps';
import LoginVw from 'views/loginVw';
import ThreadView from 'views/thread';
import { ThreadLineView } from 'views/ThreadLineView';
import ContentTimer from '../app/ContentTimer';

class AppView extends View<Model> {
    private domInitializers: Array<{ el: string, clb: keyof AppView }>;

    private threads!: ThreadCollection;

    private postings!: PostingCollection;

    private threadLines!: ThreadLineCollection;

    public constructor(options: any = {}) {
        _.defaults(options, {
            events: {
                'click #btn-manuallyMarkAsRead': 'manuallyMarkAsRead',
                'click #showLoginForm': 'showLoginForm',
                'click .js-scrollToTop': 'scrollToTop',
                'click @ui.btnCategoryChooser': 'toggleCategoryChooser',
                'click @ui.btnLogout': 'handleLogout',
            },
            regions: {
                modalDialog: '#saito-modal-dialog',
                slidetabs: '#slidetabs',
            },
            template: _.noop,
            ui: {
                btnCategoryChooser: '#btn-category-chooser',
                btnLogout: '#js-btnLogout',
            },
        });

        super(options);

        this.domInitializers = [
            { el: '.js-answer-wrapper', clb: '_initAnsweringNotInlined' },
            { el: '#slidetabs', clb: '_initSlideTabs' },
            { el: '.js-entry-view-core', clb: '_initPostings' },
            { el: '.threadBox', clb: '_initThreadBoxes' },
            { el: '.threadLeaf', clb: '_initThreadLeafs' },
            { el: '.js-rgUser', clb: '_initUser' },
        ];
    }

    public initialize() {
        this._initNotifications();
        const nv = new NavigationBreak();

        this.threads = new ThreadCollection();
        if (App.request.getController() === 'Entries' && App.request.getAction() === 'index') {
            this.threads.fetch();
        }
        this.postings = new PostingCollection();

        // collection of threadlines not bound to thread (bookmarks, search results …)
        this.threadLines = new ThreadLineCollection();
    }

    public initFromDom(options: { contentTimer: ContentTimer, SaitoApp: any }) {
        this.showChildView('modalDialog', ModalDialog);

        for (const item of this.domInitializers) {
            const $elements = $(item.el);
            if ($elements.length > 0) {
                this[item.clb]($elements);
            }
        }

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
            const url = window.location.href;
            const jumpTarget = /[\?\&]jump=(\d+)/.exec(url);
            if (!jumpTarget) {
                return;
            }
            try {
                this.scrollToThread(parseInt(jumpTarget[1], 10));
            } catch (error) {
                // do nothing
            } finally {
                const newLocation = url.replace(/[\?\&]jump=\d+/, '');
                window.history.replaceState(null, '', newLocation);
            }
        }
    }

    public _initUser(element: JQuery) {
        const model = new Bb.Model(element.data('user'));
        const User = new UserVw({ el: element, model });
        User.render();
    }

    public _initSlideTabs(element: JQuery) {
        this.showChildView('slidetabs', new SlidetabsView({ el: '#slidetabs' }));
    }

    /**
     * init the entries/add form where answering is not appended to a posting
     *
     * @param element
     * @private
     */
    public _initAnsweringNotInlined(element: JQuery) {
        const data: any = {};
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
    }

    public _initPostings(elements: JQuery) {
        _.each(elements, (element) => {
            const dataId = element.getAttribute('data-id');
            if (!dataId) {
                throw new Error();
            }
            const id = parseInt(dataId, 10);
            const postingModel = new PostingModel({ id });
            this.postings.add(postingModel, { silent: true });
            new PostingLayout({
                collection: this.postings,
                el: $(element),
                model: postingModel,
            }).render();
        });
    }

    public _initThreadBoxes(elements: JQuery) {
        _.each(elements, (element) => {
            const threadIdData = $(element).attr('data-id');
            if (!threadIdData) {
                throw new Error();
            }
            const threadId = parseInt(threadIdData, 10);

            if (!this.threads.get(threadId)) {
                this.threads.add([
                    {
                        id: threadId,
                        isThreadCollapsed: App.request.getController() === 'entries'
                            && App.request.getAction() === 'index'
                            && App.currentUser.get('user_show_thread_collapsed'),
                    },
                ], { silent: true });
            }
            const threadView = new ThreadView({
                el: $(element),
                model: this.threads.get(threadId),
                postings: this.postings,
            });
        });

    }

    public _initThreadLeafs(elements: JQuery) {
        _.each(elements, (element) => {
            const leafData = JSON.parse(element.getAttribute('data-leaf') as string);
            // 'new' is 'isNewToUser' in leaf model; also 'new' is JS-keyword
            leafData.isNewToUser = leafData.new;
            delete (leafData.new);

            const threadsCollection = this.threads.get(leafData.tid);
            let threadlineCollection;
            if (threadsCollection) {
                // leafData belongs to complete thread on page
                threadlineCollection = threadsCollection.threadlines;
            } else {
                // leafData is not shown in its complete thread context (e.g. bookmark)
                threadlineCollection = this.threadLines;
            }

            const threadLineView = new ThreadLineView({
                collection: threadlineCollection,
                el: $(element),
                leafData,
                postings: this.postings,
            });
        });
    }

    private _initNotifications() {
        //noinspection JSHint
        const notificationElement = $('<div id="#notifications" aria-live="polite" aria-atomic="true">');
        this.$el.prepend(notificationElement);
        new NotificationView({ el: notificationElement }).render();
    }

    /**
     * Handles user-logout
     *
     * @private
     */
    private handleLogout(event: JQueryEventObject, element: JQuery) {
        event.preventDefault();

        // clear JS-storage
        App.eventBus.trigger('app:localStorage:clear');

        // move on to server-logout
        _.defer(() => {
            const serverLogoutUrl = event.currentTarget.getAttribute('href');
            if (!serverLogoutUrl) {
                throw new Error();
            }
            window.redirect(serverLogoutUrl);
        });
    }

    private _showPage(startTime: number, timer: ContentTimer) {
        const triggerVisible = () => {
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
                        complete: triggerVisible,
                        // easing: 'easeInOutQuart',
                        duration: 150,
                    },
                );
        }
        timer.cancel();
    }

    private toggleCategoryChooser() {
        const categoryChooser = new CategoryChooserVw();
        categoryChooser.model.set('isOpen', !categoryChooser.model.get('isOpen'));
    }

    private initHelp() {
        new SaitoHelpView({
            el: '#shp-show',
            elementName: '.shp',
            webroot: App.settings.get('webroot'),
        }).render();
    }

    private scrollToThread(tid: number) {
        const box = $('.threadBox[data-id=' + tid + ']')[0].scrollIntoView(true);
    }

    private showLoginForm(event: JQueryEventObject) {
        event.preventDefault();
        const title = (event.currentTarget as HTMLLinkElement).title;
        ModalDialog.once('shown', () => { this.$('#tf-login-username').focus(); });
        ModalDialog.show(new LoginVw(), { title });
    }

    private scrollToTop(event: Event) {
        event.preventDefault();
        window.scrollTo({ behavior: 'smooth', top: 0 });
    }

    private manuallyMarkAsRead(event: Event) {
        if (event) {
            event.preventDefault();
        }
        window.redirect(App.settings.get('webroot') + 'entries/update');
    }
}

export default AppView;
