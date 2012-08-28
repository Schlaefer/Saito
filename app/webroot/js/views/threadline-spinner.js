define([
	'jquery',
	'underscore',
	'backbone',
	'spin'
	], function($, _, Backbone) {

		var ThreadlineSpinnerView = Backbone.View.extend({

			initialize: function() {
				this.opts = {
					lines: 9, // The number of lines to draw
					length: 2, // The length of each line
					width: 2, // The line thickness
					radius: 2, // The radius of the inner circle
					corners: 0, // Corner roundness (0..1)
					rotate: 0, // The rotation offset
					color: '#000', // #rgb or #rrggbb
					speed: 1.5, // Rounds per second
					trail: 40, // Afterglow percentage
					shadow: false, // Whether to render a shadow
					hwaccel: true, // Whether to use hardware acceleration
					className: 'js-spinner', // The CSS class to assign to the spinner
					zIndex: 2e9, // The z-index (defaults to 2000000000)
					top: 'auto', // Top position relative to parent in px
					left: 'auto' // Left position relative to parent in px
				};
				this.cl = this.$el.attr('class')
			},

			show: function() {
				var spinner = new Spinner(this.opts).spin();
				this.$el.attr('class', '').html($(spinner.el).css('margin-top', '9px').css('margin-left', '7px'));
			},

			hide: function() {
				this.$el.attr('class', this.cl);
				this.$el.html('');
			}

		});
		return ThreadlineSpinnerView;
	});