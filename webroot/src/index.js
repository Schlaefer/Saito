import App from 'app/app';
import 'lib/saito/underscore.extend';
import 'exports';

__webpack_public_path__ = SaitoApp.app.settings.webroot + 'dist/';

(function (window) {
  'use strict';

  /**
   * Redirect helper
   *
   * @param {string} destination
   */
  window.redirect = function (destination) {
    document.location.replace(destination);
  };

  /**
   * Global content timer
   */
  var contentTimer = {
    show: function () {
      $('#content').css('visibility', 'visible');
      console.warn('DOM ready timed out: show content fallback used.');
      delete this.timeoutID;
    },

    setup: function () {
      this.cancel();
      var self = this;
      this.timeoutID = window.setTimeout(function () {
        self.show();
      }, 5000);
    },

    cancel: function () {
      if (typeof this.timeoutID === "number") {
        window.clearTimeout(this.timeoutID);
        delete this.timeoutID;
      }
    }
  };

  contentTimer.setup();

  App.start({
    contentTimer: contentTimer,
    SaitoApp: SaitoApp,
  });

})(window);
