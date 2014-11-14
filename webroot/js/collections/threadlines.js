define([
	'underscore',
	'backbone',
	'models/threadline'
	], function(_, Backbone, ThreadLineModel) {
		var ThreadLineCollection = Backbone.Collection.extend({
			model: ThreadLineModel
		});
		return ThreadLineCollection;
	});