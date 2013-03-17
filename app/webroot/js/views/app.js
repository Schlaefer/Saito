define([
	'jquery',
	'underscore',
	'backbone',
    'models/app',
    'lib/jquery.i18n/jquery.i18n.extend',
	'collections/threadlines', 'views/threadlines',
	'collections/threads', 'views/thread',
	'collections/postings', 'views/postings',
    'collections/bookmarks', 'views/bookmarks',
    'views/notification', 'views/helps', 'views/categoryChooser',
    'collections/slidetabs', 'views/slidetabs',
    'views/answering',
    'jqueryUi'
	], function(
		$, _, Backbone,
        App,
        i18n,
		ThreadLineCollection, ThreadLineView,
		ThreadCollection, ThreadView,
		PostingCollection, PostingView,
        BookmarksCollection, BookmarksView,
        NotificationView, HelpsView, CategoryChooserView,
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

			initialize: function (options) {
                var threads,
                    // collection of threadlines not bound to thread
                    // (bookmarks, search results …)
                    threadLines;

                App.settings.set(options.SaitoApp.app.settings);
                App.currentUser.set(options.SaitoApp.currentUser);
                App.request = options.SaitoApp.request;


                this.initNotifications();

                this.listenTo(App.eventBus, 'initAutoreload', this.initAutoreload);
                this.listenTo(App.eventBus, 'breakAutoreload', this.breakAutoreload);

                this.$el.on('dialogopen', this.fixJqueryUiDialog);

                // init i18n
                $.i18n.setUrl(App.settings.get('webroot') + "tools/langJs");

				threads = new ThreadCollection();
				if (App.request.controller === 'entries' && App.request.action === 'index') {
					threads.fetch();
				}

                this.postings = new PostingCollection();

				$('.thread_box').each(_.bind(function(index, element) {
                    var threadView,
                        threadId;

					threadId = parseInt($(element).attr('data-id'), 10);
					if (!threads.get(threadId)) {
						threads.add([{
							id: threadId,
							isThreadCollapsed: App.request.controller === 'entries' && App.request.action === 'index' && App.currentUser.get('user_show_thread_collapsed')
						}], {silent: true});
					}
					threadView = new ThreadView({
						el: $(element),
                        postings: this.postings,
						model: threads.get(threadId)
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

				threadLines = new ThreadLineCollection();
				$('.js-thread_line').each(_.bind(function(index, element) {
                    var threadLineView,
                        threadId,
                        currentCollection,
                        new_model;

					threadId = parseInt(element.getAttribute('data-tid'), 10);

                    if(threads.get(threadId)) {
                        currentCollection = threads.get(threadId).threadlines;
                    } else {
                        currentCollection = threadLines;
                    }

					threadLineView = new ThreadLineView({
						el: $(element),
                        postings: this.postings,
                        collection: currentCollection
					});
				}, this));

                this.initAutoreload();
                this.initBookmarks('#bookmarks');
                this.initHelp('.shp');
                this.initSlidetabs('#slidetabs');
                this.initCategoryChooser('#category-chooser');

                if($('.entry.add').length > 0) {
                    // init the entries/add form where answering is not
                    // appended to a posting
                    this.answeringForm = new AnsweringView({
                        el: this.$('.entry.add'),
                        id: 'foo'
                    });
                }

                /*** All elements initialized, show page ***/
                App.initAppStatusUpdate();

                this._showPage(options.SaitoApp.timeAppStart);
				window.clearTimeout(options.contentTimer.cancel());

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

            _showPage: function(startTime) {
                var triggerVisible = function() {
                    App.eventBus.trigger('isAppVisible', true);
                };

                if (App.request.isMobile || (new Date().getTime() - startTime) > 1500) {
                    $('#content').show();
                    triggerVisible();
                } else {
                    $('#content').fadeIn(150, 'easeInOutQuart', triggerVisible);
                }
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

            initNotifications: function() {
                var notificationView;
                notificationView = new NotificationView();
            },

            initHelp: function(element_n) {
                var helps = new HelpsView({
                    el: 'body',
                    elementName: element_n,
                    indicatorName: '#shp-show'
                });
            },

			scrollToThread: function(tid) {
                $('.thread_box.' + tid)[0].scrollIntoView('top');
			},

            initAutoreload: function() {
                this.breakAutoreload();
                if (App.settings.get('autoPageReload')) {
                    this.autoPageReloadTimer = setTimeout(
                        _.bind(function() {
                            window.location = App.settings.get('webroot') + 'entries/noupdate/';
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