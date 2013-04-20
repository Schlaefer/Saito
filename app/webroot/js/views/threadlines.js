define([
	'jquery',
	'underscore',
	'backbone',
    'models/app',
    'models/threadline',
	'views/threadline-spinner',
    'text!templates/threadline-spinner.html',
    'views/postings', 'models/posting',
    'lib/saito/jquery.scrollIntoView'
	], function($, _, Backbone, App, ThreadLineModel, ThreadlineSpinnerView,
                threadlineSpinnerTpl, PostingView, PostingModel) {

        "use strict";

		var ThreadLineView = Backbone.View.extend({

			className: 'js-thread_line',
            tagName: 'li',

			spinnerTpl: _.template(threadlineSpinnerTpl),

            /**
             * Posting collection
             */
            postings: null,

			events: {
					'click .btn_show_thread': 'toggleInlineOpen',
					'click .link_show_thread': 'toggleInlineOpenFromLink'

					// is bound manualy after dom insert  in _toggleInlineOpened
					// to hightlight the correct click target in iOS
					// 'click .btn-strip-top': 'toggleInlineOpen'
			},

			initialize: function(options){
                this.postings = options.postings;

                this.model = new ThreadLineModel({id: options.id});
                if(options.el === undefined) {
                    this.model.fetch();
                } else {
                    this.model.set({html: this.el}, {silent: true});
                }
                this.collection.add(this.model, {silent: true});
                this.attributes = {'data-id': options.id};

				this.listenTo(this.model, 'change:isInlineOpened', this._toggleInlineOpened);
                this.listenTo(this.model, 'change:html', this.render);
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
						this.tlsV = new ThreadlineSpinnerView({
							el: this.$el.find('.thread_line-pre i')
						});
						this.tlsV.show();

						this.$el.find('.js-thread_line-content').after(this.spinnerTpl({
							id: id
						}));
                        // @bogus, why no listenTo?
						this.$el.find('.js-btn-strip').on('click', _.bind(this.toggleInlineOpen, this))	;

                        this._insertContent();
					} else {
						this._showInlineView();
					}
				} else {
					this._closeInlineView();
				}
			},

            _insertContent: function() {
                var id,
                    postingView;
                id = this.model.get('id');

                this.postingModel = new PostingModel({
                    id: id
                });
                this.postings.add(this.postingModel);

                postingView = new PostingView({
                    el: this.$('.t_s'),
                    model: this.postingModel,
                    collection: this.postings,
                    parentThreadline: this.model
                });

                this.postingModel.fetchHtml();

                this.model.set('isContentLoaded', true);
                this._showInlineView();
            },

			_showInlineView: function () {
                var postShow = _.bind(function () {
                    var shouldScrollOnInlineOpen = this.model.get('shouldScrollOnInlineOpen');
                    this.tlsV.hide();

                    if (shouldScrollOnInlineOpen && this.$el.scrollIntoView('isInView') === false) {
                        this.$el.scrollIntoView('bottom');
                    }
                    this.model.set('shouldScrollOnInlineOpen', true);
                }, this);

				this.$el.find('.js-thread_line-content').fadeOut(
					100,
					_.bind(
						function() {
							// performance: show() instead slide()
							// this.$('.js-thread_inline.' + id).slideDown(0,
                            this.$('.js-thread_inline').show(0, postShow);
						}, this)
					);
			},

			_closeInlineView: function() {
				// $('.js-thread_inline.' + id).slideUp('fast',
				this.$('.js-thread_inline').hide(0,
					_.bind(
						function() {
							this.$el.find('.js-thread_line-content').slideDown();
                            this._scrollLineIntoView();
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
                var thread_line = this.$('.js-thread_line-content');
                if (!thread_line.scrollIntoView('isInView')) {
                    thread_line.scrollIntoView('top')
                        .effect(
                            "highlight",
                            {
                                times: 1
                            },
                            3000);
                }
			},

            render: function() {
                var $oldEl,
                    newHtml,
                    $newEl;

                newHtml =  this.model.get('html');
                if (newHtml.length > 0) {
                    $oldEl = this.$el;
                    $newEl = $(this.model.get('html'));
                    this.setElement($newEl);
                    $oldEl.replaceWith($newEl);
                }
                return this;
            }
        });

		return ThreadLineView;

	});