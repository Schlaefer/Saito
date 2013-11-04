define([
	'underscore',
	'backbone',
	'backboneLocalStorage',
	'models/thread'
	], function(_, Backbone, Store, ThreadModel) {
    'use strict';

		var ThreadCollection = Backbone.Collection.extend({
			model: ThreadModel,
			localStorage: new Store('Threads')
		});

		return ThreadCollection;

	});