/*jshint node: true */
module.exports = {
  src: {dir: ['./src']},
  /*
  controllers: {dir: ['./src/Controller']},
  models: {dir: ['./src/Model']},
  lib: {dir: ['./src/Lib']},
  tests: {
    dir: ['./src/Test'],
    options: {
      ignore: 'Selenium'
    }
  },
  view: {
    dir: ['./src/View'],
    options: {
      ignore: 'Themed'
    }
  },
  */
  plugins: {
    dir: ['./src/Plugin'],
    options: {
      ignore: 'Embedly,Geshi,FileUpload,Install,Markitup,SaitoHelp/Vendor,Search,SimpleCaptcha,webroot'
    }
  },
  options: {
    bin: 'vendor/bin/phpcs',
    standard: 'tests/ruleset.xml',
    ignore: 'webroot',
    // suppress warnings
    warningSeverity: 8
  }
};
