define([
	'jquery',
	'underscore',
	'backbone',
	'backboneLocalStorage'
	], function($, _, Backbone, Store) {
		// if everything is migrated to require/bb set var again
		ThreadLineModel = Backbone.Model.extend({
			defaults: {
				isContentLoaded: false,
				isInlineOpened: false,
				isAlwaysShownInline: false
			},
			loadContent: function() {
				new ThreadLine(this.get('id')).load_inline_view();
			}
		});

		var ThreadLineCollection = Backbone.Collection.extend({
			model: ThreadLineModel
		});

		// if everything is migrated to require/bb set var again
		ThreadLineView = Backbone.View.extend({

			className: 'thread_line',

			events: {
				'click .btn_show_thread': 'toggleInlineOpen',
				'click .link_show_thread': 'toggleInlineOpenFromLink'
			},

			toggleInlineOpenFromLink: function(event) {
				if (this.model.get('isAlwaysShownInline')) {
					this.toggleInlineOpen(event);
				}
			},

			toggleInlineOpen: function(event) {
				event.preventDefault();
				if (!this.model.get('isInlineOpened')) {
					if (!this.model.get('isContentLoaded')) {
						this.model.loadContent();
					}
					this.model.set({
						isInlineOpened: true
					});
				} else {
					this.model.set({
						isInlineOpened: false
					});
				}
			}
		});

		var ThreadModel = Backbone.Model.extend({

			defaults: {
				isThreadCollapsed: false
			},

			toggleCollapseThread: function() {
				this.set({
					isThreadCollapsed: !this.get('isThreadCollapsed')
				});
			}

		});

		var ThreadCollection = Backbone.Collection.extend({
			model: ThreadModel,
			localStorage: new Store('Threads')
		})

		var ThreadView = Backbone.View.extend({

			className: 'thread_box',

			events: {
				"click .btn-threadCollapse":  "collapseThread"
			},

			initialize: function(){
				this.model.on('change:isThreadCollapsed', this.toggleCollapseThread, this);

				if (this.model.get('isThreadCollapsed')) {
					this.hide();
				}
			},

			collapseThread: function(event) {
				event.preventDefault();
				this.model.toggleCollapseThread();
				this.model.save();
			},

			toggleCollapseThread: function(model, isThreadCollapsed) {
				if(isThreadCollapsed) {
					this.slideUp();
				} else {
					this.slideDown();
				}
			},

			slideUp: function() {
				$(this.el).find('.tree_thread > ul > li:not(:first-child)').slideUp('100');
				$(this.el).find('.ico-threadCollapse').removeClass('ico-threadCollapse').addClass('ico-threadOpen');
			},

			slideDown: function() {
				$(this.el).find('.tree_thread > ul > li:not(:first-child)').slideDown('100');
				$(this.el).find('.ico-threadOpen').removeClass('ico-threadOpen').addClass('ico-threadCollapse');
			},

			hide: function() {
				$(this.el).find('.tree_thread > ul > li:not(:first-child)').hide();
				$(this.el).find('.ico-threadCollapse').removeClass('ico-threadCollapse').addClass('ico-threadOpen');
			}
		});

		//	})(jQuery);
		var threads = new ThreadCollection;
		threads.fetch();

		$('.thread_box').each(function(element) {
			var threadId = parseInt($(this).attr('data-id'));
			if (!threads.get(threadId)) {
				threads.add(new ThreadModel({
					id: threadId
				}));
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
			threadLines.add(new ThreadLineModel({
				id: threadLineId,
				isAlwaysShownInline: User_Settings_user_show_inline
			}));
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