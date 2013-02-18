define([
	'jquery',
	'underscore',
	'backbone'
	], function($, _, Backbone) {

		var ThreadlineSpinnerView = Backbone.View.extend({

			running: false,

			show: function() {
				var effect = _.bind(function() {
					if (this.running === false) {
						this.$el.css({opacity: 1});
						return;
					}
					this.$el.animate({opacity:0.1}, 900, _.bind(function() {
						this.$el.animate({opacity:1}, 500, effect())
					}, this));
				}, this);
				this.running = true;
				effect();
			},

			hide: function() {
				this.running = false;
			}

		});
		return ThreadlineSpinnerView;
	});
