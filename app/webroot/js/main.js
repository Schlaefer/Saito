require.config({
  // paths necessary until file is migrated into common.js
  paths: {
    // Comment to load all common.js files separately from
    // bower_components/ or vendors/.
    // Run `grunt dev-setup` to install bower components first.
    common: '../dist/common',
    // moment
    moment: '../dev/bower_components/momentjs/js/moment',
    'moment-de': '../dev/bower_components/momentjs/lang/de'
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

  define('jquery', function() { return jQuery; });


  require(['common'], function() {
    require(['app/app']);
  });

})(this, SaitoApp, contentTimer, jasmine);