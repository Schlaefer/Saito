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
		backbone: 'lib/backbone/backbone',
        underscore: 'lib/underscore/underscore',
		backboneLocalStorage: 'lib/backbone/backbone.localStorage',
		bootstrap: 'bootstrap/bootstrap',
		domReady: 'lib/require/domReady',
        jqueryAutosize: 'lib/jquery.autosize',
        cakeRest: 'lib/saito/backbone.cakeRest',
		text: 'lib/require/text'
		// @td include scrollTo after _app.js is gone
	}
});

/**
 * Redirects current page to a new url destination without changing browser history
 *
 * This also is also the mock to test redirects
 *
 * @param destination url to redirect to
 */
window.redirect = function(destination) {
    document.location.replace(destination);
}

// Camino doesn't support console at all
if (typeof console === "undefined") {
    var console = {};
    console.log = function(message) {
        return;
    };
    console.error = console.debug = console.info =  console.log;
}

require(
    ['domReady', 'views/app', 'backbone', 'bootstrap', 'jqueryhelpers', 'lib/saito/backbone.initHelper'],
    function(domReady, AppView, Backbone) {

    if (typeof SaitoApp.app.runJsTests === 'undefined') {
        // run app

        // fallback if dom does not get ready for some reason to show the content eventually
        var contentTimer = {
            show: function() {
                $('#content').show();
                console.warn('DOM ready timed out: show content fallback used.');
                delete this.timeoutID;
            },

            setup: function() {
                this.cancel();
                var self = this;
                this.timeoutID = window.setTimeout(function() {self.show();}, 5000, "Wake up!");
            },

            cancel: function() {
                if(typeof this.timeoutID == "number") {
                    window.clearTimeout(this.timeoutID);
                    delete this.timeoutID;
                }
            }
        };
        contentTimer.setup();

        domReady(function () {
            var App = new AppView({
                SaitoApp: SaitoApp,
                contentTimer: contentTimer
            });
        });

    } else {

        window.store = "TestStore"; // override local storage store name - for testing

        var jasmineEnv = jasmine.getEnv();
        jasmineEnv.updateInterval = 1000;

        var htmlReporter = new jasmine.HtmlReporter();

        jasmineEnv.addReporter(htmlReporter);

        jasmineEnv.specFilter = function(spec) {
            return htmlReporter.specFilter(spec);
        };

        var specs = [
            'lib/MarkItUpSpec.js',
            'lib/jquery.i18n.extendSpec.js',
            'views/AppViewSpec.js'
            // 'views/BookmarkViewSpec.js'
        ];

        specs = _.map(specs, function(value){
            return SaitoApp.app.settings.webroot + 'js/tests/' + value;
        });

        $(function(){
            require(specs, function(){
                jasmineEnv.execute();
            });
        });


    }
});
