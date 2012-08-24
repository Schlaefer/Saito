define([
	'jquery',
	'underscore',
	'backbone',
	], function($, _, Backbone) {
		// @td if everything is migrated to require/bb set var again
		ThreadLineView = Backbone.View.extend({

			className: 'thread_line',

			events: {
				'click .btn_show_thread': 'toggleInlineOpen',
				'click .link_show_thread': 'toggleInlineOpenFromLink'
			},

			initialize: function(){
				this.model.on('change:isInlineOpened', this._toggleInlineOpened, this);
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
			},

			_toggleInlineOpened: function(model, isInlineOpened) {
				if(isInlineOpened) {
//					this.slideUp();
				} else {
					this._closeInlineView();
				}
			},

			_closeInlineView: function() {
				var scroll = false;
				var id = this.model.get('id');
				$('.thread_inline.' + id).slideUp(
					'fast',
					function() {
						$('.thread_line.' + id).slideDown();
						if (scroll) {
						//					p.scrollLineIntoView();
						}
					}
					);
			}

		});

		return ThreadLineView;

	});