// config used in dev mode (loading each file)
require.config({
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
    jqueryAutosize: '../dev/bower_components/jquery-autosize/js/jquery.autosize',
    jqueryDropdown: '../dev/bower_components/jquery-dropdown/jquery.dropdown',
    jqueryTinyTimer: '../dev/bower_components/jquery-tinytimer/jquery.tinytimer',
    jqueryUi: 'lib/jquery-ui/jquery-ui.custom.min',
    templateHelpers: 'lib/saito/templateHelpers',
    tether: '../dev/bower_components/tether/tether',
    text: '../dev/bower_components/requirejs-text/js/text',
    underscore: '../dev/bower_components/underscore/js/underscore',
    // marionette
    marionette: '../dev/bower_components/marionette/backbone.marionette',
    "backbone.babysitter": '../dev/bower_components/backbone.babysitter/js/backbone.babysitter',
    "backbone.wreqr": '../dev/bower_components/backbone.wreqr/js/backbone.wreqr'
  }
});
