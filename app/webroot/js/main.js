require.config({
  // paths necessary until file is migrated into common.js
  paths: {
    // Comment to load all common.js files separately from
    // bower_components/ or vendors/.
    // Run `grunt dev-setup` to install bower components first.
    common: '../dist/common'
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
                'lib/saito/backbone.modelHelper', 'fastclick'
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

    // jquery is already included in the page when require.js starts
    define('jquery', function() { return jQuery; });

  require(['common'], function() {
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
  });

})(this, SaitoApp, contentTimer, jasmine);