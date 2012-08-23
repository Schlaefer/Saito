define([
	'underscore',
	'backbone',
	], function(_, Backbone) {
		// @td if everything is migrated to require/bb set var again
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

		return ThreadLineModel;

	});
