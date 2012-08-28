define([
	'jquery',
	'underscore',
	'backbone',
	], function($, _, Backbone) {

		var ThreadlineSpinnerView = Backbone.View.extend({

			show: function() {
				this.$el.attr('class', 'icon-chevron-down');
			},

			hide: function() {
				this.$el.attr('class', 'icon-chevron-right');
			}

		});
		return ThreadlineSpinnerView;
	});