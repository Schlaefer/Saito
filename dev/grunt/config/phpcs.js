/*jshint node: true */
module.exports = {
    src: {
        dir: ['./src']
    },
    plugins: {
        dir: [
            './plugins/Api',
            './plugins/Bookmarks',
            './plugins/Detectors',
            './plugins/SpectrumColorpicker'
        ]
    },
    tests: {
        dir: ['./tests'],
        options: {
            ignore: 'Selenium'
        }
    },
    options: {
        bin: 'vendor/bin/phpcs',
        standard: 'tests/ruleset.xml',
        ignore: 'webroot'
        // suppress warnings
        //warningSeverity: 8
    }
};
