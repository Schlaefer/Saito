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
    humanize: "lib/humanize/humanize",
    // moments
    moment: '../dev/bower_components/momentjs/js/moment',
    'moment-de': '../dev/bower_components/momentjs/js/de'
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
    require(['app/app']);
})(this, SaitoApp, contentTimer, jasmine);