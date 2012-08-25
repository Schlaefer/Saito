define([
	'underscore',
	'backbone',
	], function(_, Backbone) {
		var PostingModel = Backbone.Model.extend({
			defaults: {
				isAnsweringFormShown: false
			}
		});
		return PostingModel;
	});