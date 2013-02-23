define([
	'jquery',
	'underscore',
	'backbone',
    'models/app',
    'lib/jquery.i18n/jquery.i18n.extend',
	'collections/threadlines', 'views/threadlines',
	'collections/threads', 'views/threads',
	'collections/postings', 'views/postings',
    'collections/bookmarks', 'views/bookmarks',
    'views/notification',
    'views/helps',
    'collections/slidetabs', 'views/slidetabs',
    'views/answering'
	], function(
		$, _, Backbone,
        App,
        i18n,
		ThreadLineCollection, ThreadLineView,
		ThreadCollection, ThreadView,
		PostingCollection, PostingsView,
        BookmarksCollection, BookmarksView,
        NotificationView,
        HelpsView,
        SlidetabsCollection, SlidetabsView,
        AnsweringView
		) {

		var AppView = Backbone.View.extend({

			el: $('body'),
			settings: {},
			request: {},

            autoPageReloadTimer: false,

			events: {
				'click #showLoginForm': 'showLoginForm',
				'focus #header-searchField': 'widenSearchField',
                'click #btn-scrollToTop': 'scrollToTop',
                'click #btn-manuallyMarkAsRead': 'manuallyMarkAsRead'
			},

			initialize: function (options) {

                App.settings.set(options.SaitoApp.app.settings);
                App.currentUser.set(options.SaitoApp.currentUser);
                App.request = options.SaitoApp.request;


                this.initNotifications();
                this.initMessagesOnEventBus(options.SaitoApp.msg);

                this.listenTo(App.eventBus, 'initAutoreload', this.initAutoreload);
                this.listenTo(App.eventBus, 'breakAutoreload', this.breakAutoreload);

                // init i18n
                $.i18n.setUrl(App.settings.get('webroot') + "tools/langJs");

				// @td if everything is migrated to require/bb set var again
				threads = new ThreadCollection;
				if (this.request.controller === 'entries' && this.request.action === 'index') {
					threads.fetch();
				}

				$('.thread_box').each(_.bind(function(index, element) {
					var threadId = parseInt($(element).attr('data-id'));
					if (!threads.get(threadId)) {
						threads.add([{
							id: threadId,
							isThreadCollapsed: this.request.controller === 'entries' && this.request.action === 'index' && App.currentUser.get('user_show_thread_collapsed')
						}], {silent: true});
					}
					new ThreadView({
						el: $(element),
						model: threads.get(threadId)
					});
				}, this));

				// @td if everything is migrated to require/bb set var again
				threadLines = new ThreadLineCollection;
				$('.js-thread_line').each(function(element) {
					var el = $(this);
					var threadLineId = parseInt(el[0].getAttribute('data-id'));
					var threadId = parseInt(el[0].getAttribute('data-tid'));
					var isNew = el[0].getAttribute('data-new') == true;
					var new_model;
					if(threads.get(threadId)) {
						threads.get(threadId).threadlines.add([{
							id: threadLineId,
							isNewToUser: isNew,
							isAlwaysShownInline: App.currentUser.get('user_show_inline')
						}], {silent: true});
						new_model = threads.get(threadId).threadlines.get(threadLineId);
					} else {
						threadLines.add([{
							id: threadLineId,
							isNewToUser: isNew,
							isAlwaysShownInline: App.currentUser.get('user_show_inline')
						}], {silent: true});
						new_model = threadLines.get(threadLineId);
					}
					new ThreadLineView({
						el: $(this),
						model: new_model
					});
				});

				// @td if everything is migrated to require/bb set var again
				postings = new PostingCollection;
				$('.js-entry-view-core').each(_.bind(function(a,element) {
					var id = parseInt(element.getAttribute('data-id'));
					postings.add([{
						id: id
					}], {silent: true});
					new PostingView({
						el: $(element),
						model: postings.get(id)
					});
				}, this));

				// initiate page reload
				// @td make App property instead of global

                this.initAutoreload();
                this.initBookmarks('#bookmarks');
                this.initHelp('.shp');
                this.initSlidetabs('#slidetabs')

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

				if (this.request.isMobile || (new Date().getTime() - options.SaitoApp.timeAppStart) > 1500) {
					$('#content').show();
				} else {
					$('#content').fadeIn(150, 'easeInOutQuart');
				}
				window.clearTimeout(options.contentTimer.cancel());

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

            initBookmarks: function(element_n) {
                if ($(element_n).length) {
                    var bookmarks = new BookmarksCollection();
                    new BookmarksView({
                        el: element_n,
                        collection: bookmarks
                    });
                }
            },

            initSlidetabs: function(element_n) {
                var slidetabs = new SlidetabsCollection();
                new SlidetabsView({
                    el: element_n,
                    collection: slidetabs
                });
            },

            initNotifications: function() {
                new NotificationView();
            },

            initMessagesOnEventBus: function(msges) {
                var i = 0,
                    send;

                send = _.bind(function(msg) {
                    App.eventBus.trigger(
                        'notification',
                        msg
                    );
                }, this);

                _.each(msges, function(msg) {
                    _.delay(send, i * 5000, msg)
                    i++;
                }, this)
            },

            initHelp: function(element_n) {
                var helps = new HelpsView({
                    el: 'body',
                    elementName: element_n,
                    indicatorName: '#shp-show'
                });
            },

			scrollToThread: function(tid) {
				scrollToTop($('.thread_box.' + tid));
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
				if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i))) {
					return;
				}

				event.preventDefault();
				$('#modalLoginDialog').height('auto');
				var title= event.currentTarget.title;
				$('#modalLoginDialog').dialog({
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