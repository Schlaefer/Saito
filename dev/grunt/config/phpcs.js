/*jshint node: true */
module.exports = {
    src: {
        dir: ['./src']
    },
    plugins: {
        dir: [
            './plugins/Api',
            './plugins/BbcodeParser',
            './plugins/Bookmarks',
            './plugins/Commonmark',
            './plugins/Cron',
            './plugins/Detectors',
            './plugins/Embedly',
            './plugins/MailObfuscator',
            './plugins/SaitoHelp',
            './plugins/SpectrumColorpicker',
            './plugins/Stopwatch'
        ]
    },
    templates: {
        dir: ['./src/Template'],
        options: {
            extensions: 'ctp',
            standard: 'tests/ruleset-templates.xml',
            warningSeverity: 5
        }

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
