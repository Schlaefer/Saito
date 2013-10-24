// define([], function() {

  // fallback for JS-engines who don't support `console` (e.g. Camino)
  if (typeof console === "undefined") {
    //noinspection JSHint
    console = {};
    console.log = function(message) {
      'use strict';
      return;
    };
    console.error = console.debug = console.info = console.log;
  }

  /**
   * Redirects current page to a new url destination without changing browser history
   *
   * This also is also the mock to test redirects
   *
   * @param destination url to redirect to
   */
  window.redirect = function(destination) {
    'use strict';
    document.location.replace(destination);
  };

  // prevent caching of ajax results
  $.ajaxSetup({cache: false});

  define('jquery', function() {
    'use strict';
    return jQuery;
  });
// });
