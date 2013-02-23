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
		// @td if everything is migrated to require/bb set var again
		PostingView = Backbone.View.extend({

			className: 'js-entry-view-core',
            answeringForm: false,

            events: {
                "click .js-btn-setAnsweringForm": "setAnsweringForm",
                "click .btn-answeringClose": "setAnsweringForm"
            },

			initialize: function(options) {
				this.listenTo(this.model, 'change:isAnsweringFormShown', this.toggleAnsweringForm);
                this.eventBus = options.eventBus;

                this.initGeshi('.c_bbc_code-wrapper');
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
                        })
                    });
                }
            },

            setAnsweringForm: function(event) {
                event.preventDefault();
                this.model.toggle('isAnsweringFormShown')
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
                this.eventBus.trigger('breakAutoreload');
                if (this.answeringForm === false) {
                    this.$('.posting_formular_slider').html(spinnerTpl);
                }
                this.$('.posting_formular_slider').slideDown('fast');
                if (this.answeringForm === false){
                    this.answeringForm = new AnsweringView({
                        el: this.$('.posting_formular_slider'),
                        id: this.model.get('id'),
                        eventBus: this.eventBus
                    });
                }
                this.answeringForm.render();
            },

			_hideAnsweringForm: function() {
                var parent;
				$(this.el).find('.posting_formular_slider').slideUp('fast')

                // @td @bogus
                parent = $(this.el).find('.posting_formular_slider').parent();
                this.answeringForm.remove();
                this.answeringForm.undelegateEvents();
                this.answeringForm = false;
                parent.append('<div class="posting_formular_slider"></div>');
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

		return PostingView;

	});