require.config({
    // paths for running jasmine in browser
    shim: {
        sinon: {exports: 'sinon'}
    },
    paths: {
        // Comment to load all common.js files separately from
        // bower_components/ or vendors/.
        // Run `grunt dev-setup` to install bower components first.
        common: '../dist/common.min',

        sinon: '../dev/bower_components/sinonjs/sinon',
        jsjq: '../dev/bower_components/jasmine-jquery/lib/jasmine-jquery',
        templateHelpers: 'lib/saito/templateHelpers'
    }
});

require(['lib/bootstrapHelper', 'common', 'tests/jasmineBootstrapHelper'], function () {
    require([
            // used in function
            'jquery', 'underscore',
            // common test case files
            'lib/jquery.i18n/jquery.i18n.extend'],
        function ($, _) {
            // override local storage store name - for testing
            window.store = "TestStore";
            // make empty dict available for test cases
            $.i18n.setDict({});

            var jasmineEnv = jasmine.getEnv(),
                specs = [
                    'models/AppStatusModelSpec.js',
                    'models/BookmarkModelSpec.js',
                    'models/SlidetabModelSpec.js',
                    'models/UploadModelSpec.js',
                    'lib/MarkItUpSpec.js',
                    'lib/jquery.i18n.extendSpec.js',
                    'lib/TemplateHelpersSpec.js',
                    'lib/underscore.extend.Spec.js',
                    'views/AppViewSpec.js',
                    'views/MapViewSpec.js',
                    'views/PrerequisitesTesterSpec.js',
                    'views/ThreadViewSpec.js'
                ];

            specs = _.map(specs, function (value) {
                return window.webroot + 'js/tests/' + value;
            });
            delete(window.webroot);

            $(function () {
                require(specs, function () {
                    jasmineEnv.execute();
                });
            });
        });
});
