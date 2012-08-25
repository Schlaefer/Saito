define([
	'underscore',
	'backbone',
	], function(_, Backbone) {
		var ThreadModel = Backbone.Model.extend({

			defaults: {
				isThreadCollapsed: false
			},

			toggleCollapseThread: function() {
				this.set({
					isThreadCollapsed: !this.get('isThreadCollapsed')
				});
			}

		});
		return ThreadModel;
	});