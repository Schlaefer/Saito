define([
	'jquery',
	'underscore',
	'backbone',
	], function($, _, Backbone) {
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
					} else {
						new ThreadLine(this.model.id).toggle_inline_view(true);
					}
					this.model.set({
						isInlineOpened: true
					});
				} else {
					this.model.set({
						isInlineOpened: false
					});
					new ThreadLine(this.model.id).toggle_inline_view(true);
				}
			}
		});

		return ThreadLineView;

	});