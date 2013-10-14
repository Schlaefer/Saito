require.config({
    shim: {
        underscore: {
            exports: '_'
        },
        backbone: {
            deps: ['underscore', 'jquery'],
            exports: 'Backbone'
        },
        backboneLocalStorage: {
            deps: ['backbone'],
            exports: 'Store'
        },
        marionette: {
            deps: ['underscore', 'backbone', 'jquery'],
            exports: 'Marionette'
        }
	},
	paths: {
        underscore: 'lib/underscore/underscore',
        bootstrap: 'bootstrap/bootstrap',
		jquery: 'lib/jquery/jquery-require',
        marionette: '../dev/bower_components/marionette/backbone.marionette',
        jqueryUi: 'lib/jquery-ui/jquery-ui-1.9.2.custom.min',
		jqueryhelpers: 'lib/jqueryhelpers',
		backbone: 'lib/backbone/backbone',
		backboneLocalStorage: 'lib/backbone/backbone.localStorage',
		domReady: 'lib/require/domReady',
        jqueryAutosize: 'lib/jquery.autosize',
        cakeRest: 'lib/saito/backbone.cakeRest',
		text: 'lib/require/text',
        cs: 'lib/require/cs',
        "coffee-script": 'lib/coffee-script',
        humanize: "lib/humanize/humanize"
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
    $.ajaxSetup({cache: false});

    var app = {
        bootstrapApp: function(options) {
            require([
                'domReady', 'views/app', 'backbone', 'jquery', 'models/app',
                'views/notification',

                'lib/jquery.i18n/jquery.i18n.extend',
                'bootstrap', 'lib/saito/backbone.initHelper',
                'lib/saito/backbone.modelHelper', 'lib/fastclick'
            ],
                function(domReady, AppView, Backbone, $, App, NotificationView) {
                    var appView,
                        appReady;

                    App.settings.set(options.SaitoApp.app.settings);
                    App.currentUser.set(options.SaitoApp.currentUser);
                    App.request = options.SaitoApp.request;

                    new NotificationView();

                    window.addEventListener('load', function() {
                        new FastClick(document.body);
                    }, false);

                    // init i18n
                    $.i18n.setUrl(App.settings.get('webroot') + "saitos/langJs");

                    appView = new AppView();

                    appReady = function() {
                        appView.initFromDom({
                            SaitoApp: options.SaitoApp,
                            contentTimer: options.contentTimer
                        });
                    };

                    if ($.isReady) {
                        appReady();
                    } else {
                        domReady(function() {
                            appReady();
                        });
                    }

                }
            );
        },

        bootstrapTest: function(options) {
            require(['domReady', 'views/app', 'backbone', 'jquery'],
                function(domReady, AppView, Backbone, $) {
                    // prevent appending of ?_<timestamp> requested urls
                    $.ajaxSetup({ cache: true });
                    // override local storage store name - for testing
                    window.store = "TestStore";

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
                        return options.SaitoApp.app.settings.webroot + 'js/tests/' + value;
                    });

                    $(function() {
                        require(specs, function() {
                            jasmineEnv.execute();
                        });
                    });
                }
            );
        }
    };

    require(['marionette'], function(Marionette) {
        var Application = new Marionette.Application();
        if (SaitoApp.app.runJsTests === undefined) {
            Application.addInitializer(app.bootstrapApp);
        } else {
            Application.addInitializer(app.bootstrapTest);
        }
        Application.start({
            contentTimer: contentTimer,
            SaitoApp: SaitoApp
        });
    });

})(this, SaitoApp, contentTimer, jasmine);