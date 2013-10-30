define([
	'jquery',
	'underscore',
	'backbone',
    'models/app',
    'collections/geshis', 'views/geshi',
    'views/answering',
    'text!templates/spinner.html'
	], function(
        $, _, Backbone,
        App,
        GeshisCollection, GeshiView,
        AnsweringView,
        spinnerTpl
    ) {

        "use strict";

		var PostingView = Backbone.View.extend({

			className: 'js-entry-view-core',
            answeringForm: false,

            events: {
                "click .js-btn-setAnsweringForm": "setAnsweringForm",
                "click .btn-answeringClose": "setAnsweringForm",
                "click .btn-solves": "onBtnSolves"
            },

			initialize: function(options) {
                this.collection = options.collection;
                this.parentThreadline = options.parentThreadline || null;

				this.listenTo(this.model, 'change:isAnsweringFormShown', this.toggleAnsweringForm);
                this.listenTo(this.model, 'change:html', this.render);

                // init geshi for entries/view when $el is already there
                this.initGeshi('.c_bbc_code-wrapper');

        this.model.set('isSolved',
            this.$('.btn-solves').hasClass('solves-isSolved'),
            {silent: true});
        this.listenTo(this.model, 'change:isSolved', this.toggleSolved);
      },

      onBtnSolves: function(event) {
        event.preventDefault();
        this.model.toggle('isSolved');
      },

      toggleSolved: function() {
        var _$el = this.$('.btn-solves'),
            _$globalIconHook = $('.solves.' + this.model.get('id')),
            _$badge = this.$('.solves'),
            _isSolved = this.model.get('isSolved'),
            _html = '';

        if (_isSolved) {
          _$el.addClass('solves-isSolved');
          // @todo sync with EntryHHelper
          _html = '<i class="icon-badge-solves solves-isSolved"></i>';
        } else {
          _$el.removeClass('solves-isSolved');
        }
        _$badge.html(_html);
        // Sets other badges on the page, prominently in thread-line.
        // @todo should be handled as state by global model for the entry
        _$globalIconHook.html(_html);
      },

            initGeshi: function(element_n) {
                var geshi_elements;

                geshi_elements = this.$(element_n);

                if (geshi_elements.length > 0) {
                    var geshis = new GeshisCollection();
                    geshi_elements.each(function(key, element) {
                        new GeshiView({
                            el: element,
                            collection: geshis
                        });
                    });
                }
            },

            setAnsweringForm: function(event) {
                event.preventDefault();
                this.model.toggle('isAnsweringFormShown');
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
                App.eventBus.trigger('breakAutoreload');
                if (this.answeringForm === false) {
                    this.$('.posting_formular_slider').html(spinnerTpl);
                }
                this.$('.posting_formular_slider').slideDown('fast');
                if (this.answeringForm === false){
                    this.answeringForm = new AnsweringView({
                        el: this.$('.posting_formular_slider'),
                        model: this.model,
                        parentThreadline: this.parentThreadline
                    });
                }
                this.answeringForm.render();
            },

			_hideAnsweringForm: function() {
                var parent;
				$(this.el).find('.posting_formular_slider').slideUp('fast');

                // @td @bogus
                parent = $(this.el).find('.posting_formular_slider').parent();
                // @td @bogus inline answer
                if (this.answeringForm !== false) {
                    this.answeringForm.remove();
                    this.answeringForm.undelegateEvents();
                    this.answeringForm = false;
                }
                parent.append('<div class="posting_formular_slider"></div>');
			},

			_hideAllAnsweringForms: function() {
				// we have #id problems with more than one markItUp on a page
				this.collection.forEach(function(posting){
					if(posting.get('id') !== this.model.get('id')) {
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
			},

            render: function() {
                this.$el.html(this.model.get('html'));
                // init geshi for entries opened inline
                this.initGeshi('.c_bbc_code-wrapper');
                return this;
            }

		});

		return PostingView;

	});