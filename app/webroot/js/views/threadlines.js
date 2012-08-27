define([
	'jquery',
	'underscore',
	'backbone',
	'text!templates/threadline-spinner.html'
	], function($, _, Backbone, threadlineSpinnerTpl) {
		// @td if everything is migrated to require/bb set var again
		ThreadLineView = Backbone.View.extend({

			className: 'js-thread_line',

	 		spinnerTpl: _.template(threadlineSpinnerTpl),

			events: {
				'click .btn_show_thread': 'toggleInlineOpen',
				'click .link_show_thread': 'toggleInlineOpenFromLink',
				'click .btn-strip-top': 'toggleInlineOpen'
			},

			initialize: function(){
				this.model.on('change:isInlineOpened', this._toggleInlineOpened, this);

				if (typeof this.scroll == 'undefined' ) this.scroll = true;
			},

			toggleInlineOpenFromLink: function(event) {
				if (this.model.get('isAlwaysShownInline')) {
					this.toggleInlineOpen(event);
				}
			},

			/**
			 * shows and hides the element that contains an inline posting
			 */
			toggleInlineOpen: function(event) {
				event.preventDefault();
				if (!this.model.get('isInlineOpened')) {
					this.model.set({
						isInlineOpened: true
					});
				} else {
					this.model.set({
						isInlineOpened: false
					});
				}
			},

			_toggleInlineOpened: function(model, isInlineOpened) {
				if(isInlineOpened) {
					if (!this.model.get('isContentLoaded')) {
						var id = this.model.id;
						$('.js-thread_line-content.' + id).after(this.spinnerTpl({id: id}));
						this.model.loadContent();
					}
					this._showInlineView();
				} else {
					this._closeInlineView();
				}
			},

			_showInlineView: function () {
				var scroll = this.scroll;
				var id = this.model.id;

				$('.js-thread_line-content.' + id).fadeOut(
					100,
					function() {
						// performance: show instead slide
						// $(p.id_thread_inline).slideDown(null,

						$($('.js-thread_inline.' + id)).show(0,
							function() {
							// @td
							//								if (scroll && !_isScrolledIntoView(p.id_bottom)) {
							//									if(_isHeigherThanView(this)) {
							//										scrollToTop(this);
							//									}
							//									else {
							//										scrollToBottom(p.id_bottom);
							//									}
							//								}
							}
							);
					}
					);
			},

			_closeInlineView: function() {
				var scroll = this.scroll;
				var id = this.model.id;
				var p = this;
				$('.js-thread_inline.' + id).slideUp(
					'fast',
					function() {
						$('.js-thread_line-content.' + id).slideDown();
						if (scroll) {
							p._scrollLineIntoView();
						}
					}
					);
			},

			/**
			 * if the line is not in the browser windows at the moment
			 * scroll to that line and highlight it
			 */
			_scrollLineIntoView: function () {
				var thread_line = $('.js-thread_line-content.' + this.model.id);
				if (!thread_line.isScrolledIntoView()) {
					$(window).scrollTo(
						thread_line,
						400,
						{
							'offset': -40,
							easing: 'swing',
							onAfter: function() {
								thread_line.effect(
									"highlight",
									{
										times: 1
									},
									3000);
							} //end onAfter
						}
						);
				}
			}
		});


		return ThreadLineView;

	});