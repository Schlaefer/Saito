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
		jqueryhelpers: 'lib/jqueryhelpers',
		underscore: 'lib/underscore/underscore',
		backbone: 'lib/backbone/backbone',
		backboneLocalStorage: 'lib/backbone/backbone.localStorage',
		domReady: 'lib/domReady'
	}
});

require(['domReady', 'views/app', 'jqueryhelpers'], function(domReady, AppView){
	domReady(function () {
		var App = new AppView;
	});
});
