const path = require('path');
const webpackConfig = require('./webpack.config.js');

process.env.CHROMIUM_BIN = '/usr/bin/chromium';
// Timezone the browser should be in (timezone offset on timestamps)
process.env.TZ = 'Europe/Berlin';

// Karma configuration
// Generated on Thu May 24 2018 09:05:27 GMT+0200 (CEST)

module.exports = function (config) {
  config.set({

    // base path that will be used to resolve all patterns (eg. files, exclude)
    basePath: './frontend',


    // frameworks to use
    // available frameworks: https://npmjs.org/browse/keyword/karma-adapter
    frameworks: ['jasmine-jquery', 'jasmine-ajax', 'jasmine', 'jasmine-matchers'],


    plugins: ['@metahub/karma-jasmine-jquery', 'karma-*'],

    // list of files / patterns to load in the browser
    files: [
      // 'test/**/*.js',
      // 'dist/vendor.bundle.js',
      // 'node_modules/jasmine-jquery/lib/jasmine-jquery.js',
      // 'src/**/*.js',
      // 'dist/app.bundle.js',
      // 'test/jasmineBootstrapHelper.js',
      // 'test/**/*Spec.js',
      'test/runner.js',
      {pattern: 'test/fixtures/assets/**/*', watched: false, included: false, served: true, nocache: false},
    ],

    // list of files / patterns to exclude
    exclude: [
      'src/index.js',
    ],

    proxies: {
      "/assets/": "/base/test/fixtures/assets/", // basePath is deleted from second part
    },

    // preprocess matching files before serving them to the browser
    // available preprocessors: https://npmjs.org/browse/keyword/karma-preprocessor
    preprocessors: {
      // 'src/**/*js': ['webpack'],
      'test/**/*js': ['webpack'],
      // 'test/**/*Spec.jsx': ['webpack']
    },


    // test results reporter to use
    // possible values: 'dots', 'progress'
    // available reporters: https://npmjs.org/browse/keyword/karma-reporter
    reporters: ['mocha'],


    // web server port
    port: 9876,


    // enable / disable colors in the output (reporters and logs)
    colors: true,


    // level of logging
    // possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
    logLevel: config.LOG_INFO,


    // enable / disable watching file and executing tests whenever any file changes
    autoWatch: true,


    // start these browsers
    // available browser launchers: https://npmjs.org/browse/keyword/karma-launcher
    //browsers: ['Chrome'],
    browsers: ['ChromeHeadlessCustom', 'ChromiumHeadlessCustom'],
    customLaunchers: {
      ChromiumHeadlessCustom: {
        base: 'ChromiumHeadless',
        flags: ['--no-sandbox']
      },
      ChromeHeadlessCustom: {
        base: 'ChromeHeadless',
        flags: ['--no-sandbox']
      }
    },


    // Continuous Integration mode
    // if true, Karma captures browsers, runs the tests and exits
    singleRun: false,

    // Concurrency level
    // how many browser should be started simultaneous
    concurrency: Infinity,

    // webpack: webpackConfig,
    webpack: {
      mode: 'development',
      devtool: 'eval-source-map',
      resolve: {
        extensions: ['.js', '.ts'],
        modules: [path.join(__dirname, 'frontend/src'), 'node_modules'],
      },
      module: {
        rules: [
          {
            test: /\.html$/,
            loader: 'underscore-template-loader',
            query: {
              engine: 'underscore',
            },
          },
          {
            test: /\.tsx?$/,
            use: 'ts-loader',
            exclude: /node_modules/
          },
        ],
      },
    },
  })
}
