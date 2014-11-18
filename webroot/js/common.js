(function(config) {
  if (typeof exports === 'object') {
    // node.js/grunt
    module.exports = config();
  } else if (typeof define === 'function' && define.amd) {
    // require.js
    require.config(config());
  }
})(function() {

  'use strict';

  return {
    shim: {
      drop: {
        deps: ['tether'],
        exports: 'Drop'
      },
      jqueryTinyTimer: {
        deps: [/* 'jquery' */]
      }
    },
    paths: {
      backbone: '../dev/bower_components/backbone/js/backbone',
      backboneLocalStorage: '../dev/bower_components/Backbone.localStorage/js/backbone.localStorage',
      cakeRest: 'lib/saito/backbone.cakeRest',
      domReady: '../dev/bower_components/requirejs-domready/js/domReady',
      drop: '../dev/bower_components/drop/drop',
      fastclick: '../dev/bower_components/fastclick/js/fastclick',
      humanize: '../dev/bower_components/humanize/js/humanize',
      jquery: 'lib/jquery',
      jqueryAutosize: '../dev/bower_components/jquery-autosize/js/jquery.autosize',
      jqueryDropdown: '../dev/bower_components/jquery-dropdown/jquery.dropdown',
      jqueryTinyTimer: '../dev/bower_components/jquery-tinytimer/jquery.tinytimer',
      jqueryUi: 'lib/jquery-ui/jquery-ui.custom.min',
      tether: '../dev/bower_components/tether/tether',
      text: '../dev/bower_components/requirejs-text/js/text',
      underscore: '../dev/bower_components/lodash/lodash',
      // marionette
      marionette: '../dev/bower_components/marionette/backbone.marionette',
      'backbone.babysitter': '../dev/bower_components/backbone.babysitter/backbone.babysitter',
      'backbone.wreqr': '../dev/bower_components/backbone.wreqr/backbone.wreqr',
      // moment
      moment: '../dev/bower_components/momentjs/js/moment',
      'moment-de': '../dev/bower_components/momentjs/lang/de',
      // coffeescript
      cs: '../dev/bower_components/require-cs/cs',
      'coffee-script': '../dev/bower_components/coffeescript/extras/coffee-script'
    }
  };

});
