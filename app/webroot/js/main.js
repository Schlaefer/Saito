require.config({
  // paths necessary until file is migrated into common.js
  paths: {
    // Comment to load all common.js files separately from
    // bower_components/ or vendors/.
    // Run `grunt dev-setup` to install bower components first.
    common: '../dist/common.min',

    templateHelpers: 'lib/saito/templateHelpers'
  }
});

// fallback if dom does not get ready for some reason to show the content eventually
(function(window) {
  'use strict';

  window.contentTimer = {
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
  window.contentTimer.setup();
})(window);

require(['lib/bootstrapHelper', 'common'], function() {
  "use strict";
  require(['app/app']);
});
