define([
	'jquery',
	'underscore',
	'backbone',
	'views/threadline-spinner',
	'text!templates/threadline-spinner.html',
	], function($, _, Backbone, ThreadlineSpinnerView, threadlineSpinnerTpl) {
		// @td if everything is migrated to require/bb set var again
		ThreadLineView = Backbone.View.extend({

			className: 'js-thread_line',

			spinnerTpl: _.template(threadlineSpinnerTpl),

			events: {
					'click .btn_show_thread': 'toggleInlineOpen',
					'click .link_show_thread': 'toggleInlineOpenFromLink'

					// is bound manualy after dom insert  in _toggleInlineOpened
					// to hightlight the correct click target in iOS
					// 'click .btn-strip-top': 'toggleInlineOpen'
			},

			initialize: function(){
				this.model.on('change:isInlineOpened', this._toggleInlineOpened, this);
				
				this.scroll = false;
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
				this.scroll = true;
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
					var id = this.model.id;

					if (!this.model.get('isContentLoaded')) {
						this.tlsV = new ThreadlineSpinnerView({
							el: this.$el.find('.thread_line-pre i')
						});
						this.tlsV.show();

						this.$el.find('.js-thread_line-content').after(this.spinnerTpl({
							id: id
						}));
						this.$el.find('.btn-strip-top').on('click', _.bind(this.toggleInlineOpen, this))	;

						this.model.loadContent({
							success: _.bind(this._showInlineView, this, {
								tslV: 'hide',
								scroll: this.scroll
							})
						});
					} else {
						this._showInlineView({scroll: this.scroll});
					}
				} else {
					this._closeInlineView();
				}
				this.scroll = false;
			},

			_showInlineView: function (options) {
				options || (options = {});
				var scroll = options.scroll || false;
				var id = this.model.id;

				this.$el.find('.js-thread_line-content').fadeOut(
					100,
					_.bind(
						function() {
							// performance: show instead slide
							//						$($('.js-thread_inline.' + id)).slideDown(0,

							$($('.js-thread_inline.' + id)).show(0,
								_.bind(
									function() {
										// @td eliminate external functions pattern
										if (scroll && !_isScrolledIntoView(this.$el.find('#posting_formular_slider_bottom_' + this.model.id))) {
											if(_isHeigherThanView(this.$el)) {
												scrollToTop(this.$el);
											}
											else {
												scrollToBottom(this.$el.find('#posting_formular_slider_bottom_' + this.model.id));
											}
										}
										if (options['tlsV'] !== 'undefined'){
											this.tlsV.hide();
										}
									}
									, this)
								);
						}, this)
					);
			},

			_closeInlineView: function() {
				var scroll = this.scroll;
				var id = this.model.id;
				var p = this;
				// $('.js-thread_inline.' + id).slideUp('fast',
				$('.js-thread_inline.' + id).hide(0,
					_.bind(
						function() {
							this.$el.find('.js-thread_line-content').slideDown();
							if (scroll) {
								p._scrollLineIntoView();
							}
						},
						this
					)
				);
			},

			/**
		 * if the line is not in the browser windows at the moment
		 * scroll to that line and highlight it
		 */
			_scrollLineIntoView: function () {
				var thread_line = this.$el.find('.js-thread_line-content');
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