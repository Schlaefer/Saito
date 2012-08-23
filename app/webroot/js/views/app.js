define([
	'jquery',
	'underscore',
	'backbone',
	'collections/threadlines',
	'views/threadlines',
	'collections/threads',
	'views/threads',
	], function(
		$, _, Backbone,
		ThreadLineCollection, ThreadLineView,
		ThreadCollection, ThreadView) {

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

		/**
 * Posting
 */

		// if everything is migrated to require/bb set var again
		PostingModel = Backbone.Model.extend({
			defaults: {
				isAnsweringFormShown: false
			}
		});

		var PostingCollection = Backbone.Collection.extend({
			model: PostingModel
		});

		// if everything is migrated to require/bb set var again
		PostingView = Backbone.View.extend({
			className: 'js-entry-view-core',

			initialize: function() {
				this.model.on('change:isAnsweringFormShown', this.toggleAnsweringForm, this);
			},

			toggleAnsweringForm: function() {
				if (this.model.get('isAnsweringFormShown')) {
					this._hideAllAnsweringForms();
					this._hideSignature();
					this._showAnsweringForm();
					this._hideBoxActions();
				} else {
					this._showBoxActions();
					this._hideAnsweringForm();
					this._showSignature();
				}
			},

			_showAnsweringForm: function() {
				$(this.el).find('.posting_formular_slider').slideDown('fast');
			},
			_hideAnsweringForm: function() {
				var html = '<div id="spinner_' + this.model.get('id') +'" class="spinner"></div>';
				$(this.el).find('.posting_formular_slider').html(html);
				$(this.el).find('.posting_formular_slider').slideUp('fast');
			},
			_hideAllAnsweringForms: function() {
				// we have #id problems with more than one markItUp on a page
				postings.forEach(function(posting){
					if(posting.get('id') != this.model.get('id')) {
						posting.set('isAnsweringFormShown', false);
					}
				}, this);
			},

			_showSignature: function() {
				$(this.el).find('.signature').slideDown('fast');
			},
			_hideSignature: function() {
				$(this.el).find('.signature').slideUp('fast');
			},

			_showBoxActions: function() {
				$(this.el).find('.l-box-footer').slideDown('fast');
			},
			_hideBoxActions: function() {
				$(this.el).find('.l-box-footer').slideUp('fast');
			}
		});

		// if everything is migrated to require/bb set var again
		postings = new PostingCollection;
		$('.js-entry-view-core').each(function(element) {
			var id = parseInt($(this).attr('data-id'));
			postings.add(new PostingModel({
				id: id
			}));
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