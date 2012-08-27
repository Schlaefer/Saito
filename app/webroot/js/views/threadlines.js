define([
	'jquery',
	'underscore',
	'backbone',
	'text!templates/threadline-spinner.html',
	'spin'
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
					var id = this.model.id;

					if (!this.model.get('isContentLoaded')) {
						var c = {
							pre: this.$el.find('.thread_line-pre i'),
							cls: this.$el.find('.thread_line-pre i').attr('class')
						};

						c.pre.attr('class', '');

						var opts = {
							lines: 9, // The number of lines to draw
							length: 2, // The length of each line
							width: 2, // The line thickness
							radius: 2, // The radius of the inner circle
							corners: 0, // Corner roundness (0..1)
							rotate: 0, // The rotation offset
							color: '#000', // #rgb or #rrggbb
							speed: 1.5, // Rounds per second
							trail: 40, // Afterglow percentage
							shadow: false, // Whether to render a shadow
							hwaccel: true, // Whether to use hardware acceleration
							className: 'js-spinner', // The CSS class to assign to the spinner
							zIndex: 2e9, // The z-index (defaults to 2000000000)
							top: 'auto', // Top position relative to parent in px
							left: 'auto' // Left position relative to parent in px
						};
						var spinner = new Spinner(opts).spin();
						$(c.pre).html($(spinner.el).css('margin-top', '9px').css('margin-left', '7px'));

						$('.js-thread_line-content.' + id).after(this.spinnerTpl({
							id: id
						}));
						this.model.loadContent({
							success: _.bind(this._showInlineView, this, c)
						});
					} else {
						this._showInlineView();
					}
				} else {
					this._closeInlineView();
				}
			},

			_showInlineView: function (c) {
				var scroll = this.scroll;
				var id = this.model.id;

				$('.js-thread_line-content.' + id).fadeOut(
					100,
					function() {
						// performance: show instead slide
						//						$($('.js-thread_inline.' + id)).slideDown(0,

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
								if (typeof c !== 'undefined' ){
									c.pre.attr('class', c.cls);
									c.pre.html('');
								}
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