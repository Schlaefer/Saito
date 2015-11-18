/*jshint node: true */
module.exports = {
  application: {
    dir: [
      './app/Controller',
      './app/Model',
      './app/Lib',
      './app/Test',
      './app/View',
      './app/Plugin'
    ]
  },
  options: {
    bin: 'app/Vendor/bin/phpcs',
    standard: 'app/Test/ruleset.xml',
    // suppress warnings
    warningSeverity: 8
  }
};
