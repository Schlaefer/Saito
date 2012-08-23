require.config({
	shim: {
		underscore: {
			exports: '_'
		},
		backbone: {
			deps: ["underscore", "jquery"],
			exports: "Backbone"
		},
		backboneLocalStorage: {
			deps:["backbone"],
			exports: "Store"
		}
	},
	paths: {
		jquery: 'lib/jquery/jquery-require',
		underscore: 'lib/underscore/underscore',
		backbone: 'lib/backbone/backbone',
		backboneLocalStorage: 'lib/backbone/backbone.localStorage'
	}
});

require(['views/app'], function(AppView){
	var App = new AppView;
});

