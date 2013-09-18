define([
	'jquery',
	'underscore',
	'backbone',
    'models/app',
	'collections/threadlines', 'views/threadlines',
	'collections/threads', 'views/thread',
	'collections/postings', 'views/postings',
    'collections/bookmarks', 'views/bookmarks',
    'views/helps', 'views/categoryChooser',
    'collections/slidetabs', 'views/slidetabs',
    'views/answering',
    'jqueryUi'
	], function(
		$, _, Backbone,
        App,
		ThreadLineCollection, ThreadLineView,
		ThreadCollection, ThreadView,
		PostingCollection, PostingView,
        BookmarksCollection, BookmarksView,
        HelpsView, CategoryChooserView,
        SlidetabsCollection, SlidetabsView,
        AnsweringView
		) {

        "use strict";

		var AppView = Backbone.View.extend({

			el: $('body'),

            autoPageReloadTimer: false,

			events: {
				'click #showLoginForm': 'showLoginForm',
				'focus #header-searchField': 'widenSearchField',
                'click #btn-scrollToTop': 'scrollToTop',
                'click #btn-manuallyMarkAsRead': 'manuallyMarkAsRead',
                "click #btn-category-chooser": "toggleCategoryChooser"
			},

			initialize: function() {
				this.threads = new ThreadCollection();
				if (App.request.controller === 'entries' && App.request.action === 'index') {
					this.threads.fetch();
				}
                this.postings = new PostingCollection();
                // collection of threadlines not bound to thread (bookmarks, search results …)
				this.threadLines = new ThreadLineCollection();

                this.listenTo(App.eventBus, 'initAutoreload', this.initAutoreload);
                this.listenTo(App.eventBus, 'breakAutoreload', this.breakAutoreload);
                this.$el.on('dialogopen', this.fixJqueryUiDialog);
			},

            initFromDom: function(options) {
                $('.thread_box').each(_.bind(function(index, element) {
                    var threadView,
                        threadId;

                    threadId = parseInt($(element).attr('data-id'), 10);
                    if (!this.threads.get(threadId)) {
                        this.threads.add([{
                            id: threadId,
                            isThreadCollapsed: App.request.controller === 'entries' && App.request.action === 'index' && App.currentUser.get('user_show_thread_collapsed')
                        }], {silent: true});
                    }
                    threadView = new ThreadView({
                        el: $(element),
                        postings: this.postings,
                        model: this.threads.get(threadId)
                    });
                }, this));

                $('.js-entry-view-core').each(_.bind(function(a,element) {
                    var id,
                        postingView;

                    id = parseInt(element.getAttribute('data-id'), 10);
                    this.postings.add([{
                        id: id
                    }], {silent: true});
                    postingView = new PostingView({
                        el: $(element),
                        model: this.postings.get(id),
                        collection: this.postings
                    });
                }, this));

                $('.js-thread_line').each(_.bind(function(index, element) {
                    var threadLineView,
                        threadId,
                        threadLineId,
                        currentCollection;

                    threadId = parseInt(element.getAttribute('data-tid'), 10);

                    if(this.threads.get(threadId)) {
                        currentCollection = this.threads.get(threadId).threadlines;
                    } else {
                        currentCollection = this.threadLines;
                    }

                    threadLineId = parseInt(element.getAttribute('data-id'), 10);
                    threadLineView = new ThreadLineView({
                        el: $(element),
                        id: threadLineId,
                        postings: this.postings,
                        collection: currentCollection
                    });
                }, this));

                this.initAutoreload();
                this.initBookmarks('#bookmarks');
                this.initHelp('.shp');
                this.initSlidetabs('#slidetabs');
                this.initCategoryChooser('#category-chooser');

                if($('.entry.add-not-inline').length > 0) {
                    // init the entries/add form where answering is not
                    // appended to a posting
                    this.answeringForm = new AnsweringView({
                        el: this.$('.entry.add-not-inline'),
                        id: 'foo'
                    });
                }

                /*** All elements initialized, show page ***/

                App.initAppStatusUpdate();
                this._showPage(options.SaitoApp.timeAppStart, options.contentTimer);
                App.eventBus.trigger('notification', options.SaitoApp);

                // scroll to thread
                if (window.location.href.indexOf('/jump:') > -1) {
                    var results = /jump:(\d+)/.exec(window.location.href);
                    this.scrollToThread(results[1]);
                    window.history.replaceState(
                        'object or string',
                        'Title',
                        window.location.pathname.replace(/jump:\d+(\/)?/, '')
                    );
                }
            },

            _showPage: function(startTime, timer) {
                var triggerVisible = function() {
                    App.eventBus.trigger('isAppVisible', true);
                };

                if (App.request.isMobile || (new Date().getTime() - startTime) > 1500) {
                    $('#content').css('visibility', 'visible');
                    triggerVisible();
                } else {
                    $('#content')
                        .css({visibility: 'visible', opacity: 0})
                        .animate(
                        { opacity: 1 },
                        {
                            duration: 150,
                            easing: 'easeInOutQuart',
                            complete: triggerVisible
                        });
                }
                timer.cancel();
            },

            fixJqueryUiDialog: function(event, ui) {
                $('.ui-icon-closethick')
                    .attr('class', 'icon icon-close-widget icon-large')
                    .html('');
            },

            initBookmarks: function(element_n) {
                var bookmarksView;
                if ($(element_n).length) {
                    var bookmarks = new BookmarksCollection();
                    bookmarksView = new BookmarksView({
                        el: element_n,
                        collection: bookmarks
                    });
                }
            },

            initSlidetabs: function(element_n) {
                var slidetabs,
                    slidetabsView;
                slidetabs = new SlidetabsCollection();
                slidetabsView = new SlidetabsView({
                    el: element_n,
                    collection: slidetabs
                });
            },

            initCategoryChooser: function(element_n) {
                if ($(element_n).length > 0) {
                    this.categoryChooser = new CategoryChooserView({
                        el: element_n
                    });
                }
            },

            toggleCategoryChooser: function() {
               this.categoryChooser.toggle();
            },

            initHelp: function(element_n) {
                var helps = new HelpsView({
                    el: 'body',
                    elementName: element_n,
                    indicatorName: '#shp-show'
                });
            },

			scrollToThread: function(tid) {
                $('.thread_box[data-id=' + tid + ']')[0].scrollIntoView('top');
			},

            initAutoreload: function() {
                this.breakAutoreload();
                if (App.settings.get('autoPageReload')) {
                    this.autoPageReloadTimer = setTimeout(
                        _.bind(function() {
                            window.location = App.settings.get('webroot') + 'entries/';
                        }, this), App.settings.get('autoPageReload') * 1000);
                }

            },

            breakAutoreload: function() {
                if (this.autoPageReloadTimer !== false) {
                    clearTimeout(this.autoPageReloadTimer);
                    this.autoPageReloadTimer = false;
                }
            },

			/**
			* Widen search field
			*/
			widenSearchField: function(event) {
				var width = 350;
				event.preventDefault();
				if ($(event.currentTarget).width() < width) {
					$(event.currentTarget).animate({
						width: width + 'px'
					},
					"fast"
					);
				}
			},

			showLoginForm: function(event) {
                var modalLoginDialog;

				if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i))) {
					return;
				}

                modalLoginDialog =  $('#modalLoginDialog');

				event.preventDefault();
				modalLoginDialog.height('auto');
				var title= event.currentTarget.title;
				modalLoginDialog.dialog({
					modal: true,
					title: title,
					width: 420,
					show: 'fade',
					hide: 'fade',
					position: ['center', 120],
                    resizable: false
				});
			},

            scrollToTop: function(event) {
                event.preventDefault();
                window.scrollTo(0, 0);
            },

            manuallyMarkAsRead: function(event) {
                event.preventDefault();
                window.redirect(App.settings.get('webroot') + 'entries/update');
            }
		});

		return AppView;

	});