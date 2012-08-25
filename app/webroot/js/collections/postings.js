define([
	'underscore',
	'backbone',
	'models/posting'
	], function(_, Backbone, PostingModel) {
		var PostingCollection = Backbone.Collection.extend({
			model: PostingModel
		});
		return PostingCollection;
	});