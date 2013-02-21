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

if (typeof SaitoApp.app.runJsTests === 'undefined') {
    // run app

    require(
        ['domReady', 'views/app', 'backbone', 'bootstrap', 'jqueryhelpers'],
        function(domReady, AppView, Backbone) {
        // fallback if dom does not get ready for some reason to show the content eventually
        var contentTimer = {
            show: function() {
                $('#content').show();
                console.log('Dom ready timed out: show content fallback used.');
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

        // @td
        Backbone.View.prototype.initCollectionFromDom = function(element, collection, view) {
            var createElement = function(collection, id, element) {
                collection.add({
                    id: id
                });
                new view({
                    el: element,
                    model: collection.get(id)
                })
            };

            $(element).each(function(){
                    createElement(collection, $(this).data('id'), this);
                }
            );
        };

        domReady(function () {
            var App = new AppView({
                SaitoApp: SaitoApp,
                contentTimer: contentTimer
            });
        });


    });

} else {
    // run javascript tests

    window.store = "TestStore"; // override local storage store name - for testing

    require(['underscore', 'jquery', 'backbone'], function(_, $, Backbone){

        Backbone.View.prototype.initCollectionFromDom = function(element, collection, view) {
            var createElement = function(collection, id, element) {
                collection.add({
                    id: id
                });
                new view({
                    el: element,
                    model: collection.get(id)
                })
            };

            $(element).each(function(){
                    createElement(collection, $(this).data('id'), this);
                }
            );
        };

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

    });

}
