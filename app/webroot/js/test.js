require.config({
  // paths for running jasmine in browser
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

require(['lib/bootstrapHelper', 'common', 'tests/jasmineBootstrapHelper'], function() {
  require(['jquery', 'underscore'], function($, _) {
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
      'models/UploadModelSpec.js',
      'lib/MarkItUpSpec.js',
      'lib/jquery.i18n.extendSpec.js',
      'views/AppViewSpec.js',
      'views/PrerequisitesTesterSpec.js',
      'views/ThreadViewSpec.js'
    ];

    specs = _.map(specs, function(value) {
      return window.webroot + 'js/tests/' + value;
    });
    delete(window.webroot);

    $(function() {
      require(specs, function() {
        jasmineEnv.execute();
      });
    });
  });
});
