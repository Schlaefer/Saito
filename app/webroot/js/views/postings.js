define([
	'jquery',
	'underscore',
	'backbone',
	], function($, _, Backbone) {
		// @td if everything is migrated to require/bb set var again
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
				// @td disable autoreload timeout from App
				clearTimeout(autoPageReloadTimer);
			},
			_hideAnsweringForm: function() {
				var html = '<div class="spinner"></div>';
				$(this.el).find('.posting_formular_slider').slideUp('fast', _.bind(function() {
					$(this.el).find('.posting_formular_slider').html(html);
				}, this));
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