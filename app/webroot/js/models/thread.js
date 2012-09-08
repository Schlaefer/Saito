define([
	'underscore',
	'backbone',
	'collections/threadlines'
	], function(_, Backbone, ThreadLinesCollection) {
		var ThreadModel = Backbone.Model.extend({

			defaults: {
				isThreadCollapsed: false
			},

			initialize: function() {
				this.threadlines = new ThreadLinesCollection;
			},

			toggleCollapseThread: function() {
				this.set({
					isThreadCollapsed: !this.get('isThreadCollapsed')
				});
			}

		});
		return ThreadModel;
	});