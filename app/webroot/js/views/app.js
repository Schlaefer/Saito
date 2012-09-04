define([
	'jquery',
	'underscore',
	'backbone',
	'collections/threadlines',
	'views/threadlines',
	'collections/threads',
	'views/threads',
	'collections/postings',
	'views/postings',
	], function(
		$, _, Backbone,
		ThreadLineCollection, ThreadLineView,
		ThreadCollection, ThreadView,
		PostingCollection, PostingView
		) {

		// App
		var AppView = Backbone.View.extend({
			el: $('body'),

			events: {
				'click #showLoginForm': 'showLoginForm',
				'focus #EntrySearchTerm': 'widenSearchField'
			},

			initialize: function () {

				// @td if everything is migrated to require/bb set var again
				threads = new ThreadCollection;
				threads.fetch();

				$('.thread_box').each(function(element) {
					var threadId = parseInt($(this).attr('data-id'));
					if (!threads.get(threadId)) {
						threads.add([{
							id: threadId
						}], {silent: true});
					}
					new ThreadView({
						el: $(this),
						model: threads.get(threadId)
					});
				});

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
							isAlwaysShownInline: User_Settings_user_show_inline
						}], {silent: true});
						new_model = threads.get(threadId).threadlines.get(threadLineId);
					} else {
						threadLines.add([{
							id: threadLineId,
							isNewToUser: isNew,
							isAlwaysShownInline: User_Settings_user_show_inline
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
					position: ['center', 120]
				});
			}
		});

		return AppView;

	});