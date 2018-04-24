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
      marionette: {
        deps: ['underscore', 'backbone'],
        exports: 'Marionette'
      },
      drop: {
        deps: ['tether'],
        exports: 'Drop'
      },
      jqueryTinyTimer: {
        deps: [/* 'jquery' */]
      }
    },
    paths: {
      backbone: '../dev/node_modules/backbone/backbone',
      backboneLocalStorage: '../dev/node_modules/Backbone.localStorage/backbone.localStorage',
      cakeRest: 'lib/saito/backbone.cakeRest',
      domReady: '../dev/bower_components/requirejs-domready/domReady',
      drop: '../dev/bower_components/drop/drop',
      fastclick: '../dev/bower_components/fastclick/lib/fastclick',
      humanize: '../dev/bower_components/humanize/humanize',
      jquery: 'lib/jquery',
      jqueryAutosize: '../dev/bower_components/jquery-autosize/jquery.autosize',
      jqueryDropdown: '../dev/bower_components/jquery-dropdown/jquery.dropdown',
      jqueryTinyTimer: '../dev/bower_components/jquery-tinytimer/jquery.tinytimer',
      jqueryUi: 'lib/jquery-ui/jquery-ui.custom.min',
      tether: '../dev/bower_components/tether/tether',
      text: '../dev/bower_components/requirejs-text/text',
      underscore: '../dev/bower_components/lodash/lodash',
      // marionette
      marionette: '../dev/node_modules/backbone.marionette/lib/backbone.marionette',
      'backbone.babysitter': '../dev/node_modules/backbone.babysitter/lib/backbone.babysitter',
      'backbone.wreqr': '../dev/node_modules/backbone.wreqr/lib/backbone.wreqr',
      // moment
      moment: '../dev/bower_components/momentjs/moment',
      'moment-de': '../dev/bower_components/momentjs/lang/de',
      // coffeescript
      cs: '../dev/bower_components/require-cs/cs',
      'coffee-script': '../dev/bower_components/coffeescript/extras/coffee-script'
    }
  };

});
