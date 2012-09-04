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
		bootstrap: 'bootstrap/bootstrap',
		domReady: 'lib/domReady',
		text: 'lib/require/text'
		// @td include scrollTo after _app.js is gone
	}
});

require(['domReady', 'views/app', 'bootstrap', 'jqueryhelpers'], function(domReady, AppView){
	domReady(function () {
		var App = new AppView;
	});
});
