require.config({
    // baseUrl: "../../js/",
    // urlArgs: 'cb=' + Math.random(),
    paths: {
        jquery: 'lib/jquery/jquery-require',
        jqueryhelpers: 'lib/jqueryhelpers',
        underscore: 'lib/underscore/underscore',
        backbone: 'lib/backbone/backbone',
        backboneLocalStorage: 'lib/backbone/backbone.localStorage',
        bootstrap: 'bootstrap/bootstrap',
        domReady: 'lib/domReady',
        text: 'lib/require/text'
        // jasmine: '../test/lib/jasmine',
        // 'jasmine-html': '../test/lib/jasmine-html',
        // spec: '../test/jasmine/spec/'
    },
    shim: {
        underscore: {
            exports: "_"
        },
        backbone: {
            deps: ['underscore', 'jquery'],
            exports: 'Backbone'
        },
        'backbone.localStorage': {
            deps: ['backbone'],
            exports: 'Backbone'
        },
        jasmine: {
            exports: 'jasmine'
        },
        'jasmine-html': {
            deps: ['jasmine'],
            exports: 'jasmine'
        }
    }
});


window.store = "TestStore"; // override local storage store name - for testing

require(['underscore', 'jquery'], function(_, $){

    var jasmineEnv = jasmine.getEnv();
    jasmineEnv.updateInterval = 1000;

    var htmlReporter = new jasmine.HtmlReporter();

    jasmineEnv.addReporter(htmlReporter);

    jasmineEnv.specFilter = function(spec) {
        return htmlReporter.specFilter(spec);
    };

    var specs = [];

    specs.push('tests/BookmarkSpec');

    $(function(){
        require(specs, function(){
            jasmineEnv.execute();
    });
  });

});