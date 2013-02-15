define([
	'jquery',
	'underscore',
	'backbone',
	'collections/threadlines', 'views/threadlines',
	'collections/threads', 'views/threads',
	'collections/postings', 'views/postings',
    'collections/bookmarks', 'views/bookmarks',
    'views/helps',
    'collections/slidetabs', 'views/slidetabs'
	], function(
		$, _, Backbone,
		ThreadLineCollection, ThreadLineView,
		ThreadCollection, ThreadView,
		PostingCollection, PostingView,
        BookmarksCollection, BookmarksView,
        HelpsView,
        SlidetabsCollection, SlidetabsView
		) {

		// App
		var AppView = Backbone.View.extend({
			el: $('body'),
			settings: {},
			request: {},

			events: {
				'click #showLoginForm': 'showLoginForm',
				'focus #header-searchField': 'widenSearchField',
                'click #btn-scrollToTop': 'scrollToTop',
                'click #btn-manuallyMarkAsRead': 'manuallyMarkAsRead'
			},

			initialize: function (options) {

				this.app = options.SaitoApp.app;
				this.settings = options.SaitoApp.app.settings;
				this.request = options.SaitoApp.request;
				this.currentUser = options.SaitoApp.currentUser;

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
							isThreadCollapsed: this.request.controller === 'entries' && this.request.action === 'index' && this.currentUser.user_show_thread_collapsed
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
							isAlwaysShownInline: SaitoApp.currentUser.user_show_inline
						}], {silent: true});
						new_model = threads.get(threadId).threadlines.get(threadLineId);
					} else {
						threadLines.add([{
							id: threadLineId,
							isNewToUser: isNew,
							isAlwaysShownInline: SaitoApp.currentUser.user_show_inline
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
				$('.js-entry-view-core').each(function(element) {
					var id = parseInt($(this)[0].getAttribute('data-id'));
					postings.add([{
						id: id
					}], {silent: true});
					new PostingView({
						el: $(this),
						model: postings.get(id)
					});
				});

				// initiate page reload
				// @td make App property instead of global
				autoPageReloadTimer = null;
				if (this.settings.autoPageReload) {
					autoPageReloadTimer = setTimeout(
						_.bind(function() {
							window.location = this.app.webroot + 'entries/noupdate/';
						}, this), this.settings.autoPageReload * 1000);
				}

                this.initBookmarks('#bookmarks');
                this.initHelp('.shp');
                this.initSlidetabs('#slidetabs')

                /*** Show Page ***/

				if (this.request.isMobile || (new Date().getTime() - options.SaitoApp.timeAppStart) > 1500) {
					$('#content').show();
				} else {
					$('#content').fadeIn(150, 'easeInOutQuart');
				}
				window.clearTimeout(options.contentTimer.cancel());

				// must be executed after everything is shown;
				if (typeof Saito_App_setFocus !== 'undefined') {
					$(Saito_App_setFocus).focus();
				}

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
                    collection: slidetabs,
                    webroot: this.app.webroot
                });
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
                document.location.replace(this.app.webroot + 'entries/update');
            }
		});

		return AppView;

	});