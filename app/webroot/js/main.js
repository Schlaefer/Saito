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
        jqueryUi: 'lib/jquery-ui/jquery-ui-1.9.2.custom.min',
		jqueryhelpers: 'lib/jqueryhelpers',
		backbone: 'lib/backbone/backbone',
        underscore: 'lib/underscore/underscore',
		backboneLocalStorage: 'lib/backbone/backbone.localStorage',
		bootstrap: 'bootstrap/bootstrap',
		domReady: 'lib/require/domReady',
        jqueryAutosize: 'lib/jquery.autosize',
        cakeRest: 'lib/saito/backbone.cakeRest',
		text: 'lib/require/text',
        cs: 'lib/require/cs',
        "coffee-script": 'lib/coffee-script',
        humanize: "lib/humanize/humanize",
        modernizr: "lib/modernizr.custom"
	}

});

if (typeof jasmine === "undefined") {
    jasmine = {};
}

// Camino doesn't support console at all
if (typeof console === "undefined") {
    console = {};
    console.log = function(message) {
        return;
    };
    console.error = console.debug = console.info =  console.log;
}

// fallback if dom does not get ready for some reason to show the content eventually
var contentTimer = {
    show: function() {
        $('#content').css('visibility', 'visible');
        console.warn('DOM ready timed out: show content fallback used.');
        delete this.timeoutID;
    },

    setup: function() {
        this.cancel();
        var self = this;
        this.timeoutID = window.setTimeout(function() {
            self.show();
        }, 5000);
    },

    cancel: function() {
        if(typeof this.timeoutID === "number") {
            window.clearTimeout(this.timeoutID);
            delete this.timeoutID;
        }
    }
};
contentTimer.setup();

(function(window, SaitoApp, contentTimer, jasmine) {

    "use strict";

    /**
     * Redirects current page to a new url destination without changing browser history
     *
     * This also is also the mock to test redirects
     *
     * @param destination url to redirect to
     */
    window.redirect = function(destination) {
        document.location.replace(destination);
    };

    // prevent caching of ajax results
    $.ajaxSetup({ cache: false });


    var app = {

        init: function(options) {
            this.contentTimer = options.contentTimer;
            this.SaitoApp = options.SaitoApp;
        },

        bootstrap: function() {
            var that = this;
            require([
                'domReady', 'views/app', 'backbone', 'jquery', 'models/app',
                'views/notification',

                'lib/jquery.i18n/jquery.i18n.extend',
                'bootstrap', 'lib/saito/backbone.initHelper',
                'lib/saito/backbone.modelHelper'
            ],
                function(domReady, AppView, Backbone, $, App, NotificationView) {
                    var appView,
                        notificationView;

                    App.settings.set(that.SaitoApp.app.settings);
                    App.currentUser.set(that.SaitoApp.currentUser);
                    App.request = that.SaitoApp.request;

                    notificationView = new NotificationView();

                    if (that.SaitoApp.app.runJsTests === undefined) { // run app

                        // init i18n
                        $.i18n.setUrl(App.settings.get('webroot') + "tools/langJs");

                        appView = new AppView();

                        domReady(function() {
                            appView.initFromDom({
                                SaitoApp: that.SaitoApp,
                                contentTimer: that.contentTimer
                            });
                        });
                    } else { // run tests

                        window.store = "TestStore"; // override local storage store name - for testing

                        var jasmineEnv = jasmine.getEnv();
                        jasmineEnv.updateInterval = 1000;

                        var htmlReporter = new jasmine.HtmlReporter();

                        jasmineEnv.addReporter(htmlReporter);

                        jasmineEnv.specFilter = function(spec) {
                            return htmlReporter.specFilter(spec);
                        };

                        var specs = [
                            'models/AppStatusModelSpec.js',
                            'models/BookmarkModelSpec.js',
                            'models/SlidetabModelSpec.js',
                            'models/StatusModelSpec.js',
                            'models/UploadModelSpec.js',
                            'lib/MarkItUpSpec.js',
                            'lib/jquery.i18n.extendSpec.js',
                            // 'views/AppViewSpec.js',
                            'views/ThreadViewSpec.js'
                        ];

                        specs = _.map(specs, function(value) {
                            return that.SaitoApp.app.settings.webroot + 'js/tests/' + value;
                        });

                        $(function() {
                            require(specs, function() {
                                jasmineEnv.execute();
                            });
                        });


                    }
                }
            );
        }
    };

    window.Application = app;
    window.Application.init({
        contentTimer: contentTimer,
        SaitoApp: SaitoApp
    });
    window.Application.bootstrap();

})(this, SaitoApp, contentTimer, jasmine);