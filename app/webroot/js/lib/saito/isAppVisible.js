define(['jquery', 'underscore', 'models/app'], function($, _, App) {
  'use strict';

  var IsAppVisible = function() {
    this.initialize.apply(this, arguments);
  };

  _.extend(IsAppVisible.prototype, {

    _isVisibleOldSchool: true,

    initialize: function() {
      App.reqres.setHandler('isAppVisible', this.isVisible, this);
      // if the page is on autoreload and it's not visible we can catch that here
      // @todo but alas not if it's visible and on autoreload
      this._isVisibleOldSchool = this._isAppVisibleHtml5();
      this._initAppVisibleOldSchool();
    },

    isVisible: function() {
      return this._isVisibleOldSchool;
    },

    _initAppVisibleOldSchool: function() {
      $(window).blur(_.bind(function() {
        this._isVisibleOldSchool = false;
      }, this));
      $(window).focus(_.bind(function() {
        this._isVisibleOldSchool = true;
      }, this));
    },

    /**
     * Detects visibility with Page Visibility API
     *
     * Not used at the moment because it can't detect if the tab is active,
     * but the _application_ is in the background OS wise.
     *
     * @returns {boolean}
     * @private
     */
    _isAppVisibleHtml5: function() {
      // @todo browser support
      var hidden, isHidden = false;
      if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support
        hidden = "hidden";
      } else if (typeof document.webkitHidden !== "undefined") {
        hidden = "webkitHidden";
      }
      if (document[hidden]) {
        isHidden = document[hidden];
      }
      return !isHidden;
    }

  });

  return new IsAppVisible();
});