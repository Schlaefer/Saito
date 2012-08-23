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

		var threads = new ThreadCollection;
		threads.fetch();

		$('.thread_box').each(function(element) {
			var threadId = parseInt($(this).attr('data-id'));
			if (!threads.get(threadId)) {
				threads.add([{
					id: threadId
				}]);
			}
			new ThreadView({
				el: $(this),
				model: threads.get(threadId)
			});
		});

		// if everything is migrated to require/bb set var again
		threadLines = new ThreadLineCollection;
		$('.thread_line').each(function(element) {
			var threadLineId = parseInt($(this).attr('data-id'));
			threadLines.add([{
				id: threadLineId,
				isAlwaysShownInline: User_Settings_user_show_inline
			}]);
			new ThreadLineView({
				el: $(this),
				model: threadLines.get(threadLineId)
			});
		});

		// if everything is migrated to require/bb set var again
		postings = new PostingCollection;
		$('.js-entry-view-core').each(function(element) {
			var id = parseInt($(this).attr('data-id'));
			postings.add([{
				id: id
			}]);
			new PostingView({
				el: $(this),
				model: postings.get(id)
			});
		});

		// App
		var AppView = Backbone.View.extend({
			el: $('body'),

			events: {
				'click #showLoginForm': 'showLoginForm',
				'focus #EntrySearchTerm': 'widenSearchField'
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