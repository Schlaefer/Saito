define([
	'underscore',
	'backbone',
	], function(_, Backbone) {
		var ThreadLineModel = Backbone.Model.extend({
			defaults: {
				isContentLoaded: false,
				isInlineOpened: false,
				isAlwaysShownInline: false
			},
			loadContent: function() {
				new ThreadLine(this.get('id')).load_inline_view();
				this.set('isContentLoaded', true);
			}
		});

		return ThreadLineModel;

	});