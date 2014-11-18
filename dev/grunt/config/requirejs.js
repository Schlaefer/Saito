(function(module) {
  /*jshint node: true */
  'use strict';

  var _ = require('lodash'),
      root = './../../../',
      requireCommon = require(root + 'webroot/js/common.js'),
      requireConfig = {
        shim: requireCommon.shim,
        paths: _.extend(requireCommon.paths, {
          templateHelpers: 'lib/saito/templateHelpers'
        })
      };

  var requireJsOptions = {
    baseUrl: './webroot/js',
    dir: './webroot/release-tmp',
    optimize: 'uglify2', // "none"
    skipDirOptimize: true,
    findNestedDependencies: true,
    preserveLicenseComments: false, // just to many comments in bootstrap
    shim: requireConfig.shim,
    paths: requireConfig.paths, // paths used by r.js
    modules: [
      {
        name: 'common',
        include: [
          'backbone',
          'backboneLocalStorage',
          'backbone.babysitter',
          'backbone.wreqr',
          'cakeRest',
          'domReady',
          'drop',
          'fastclick',
          'marionette',
          'moment', 'moment-de',
          'humanize',
          'jqueryAutosize',
          'jqueryDropdown',
          'jqueryTinyTimer',
          'jqueryUi',
          'text',
          'tether',
          'underscore'
        ],

        // jquery: is loaded externally on html page
        // CoffeeScript: is not needed in compiled build file
        exclude: ['coffee-script', 'jquery'],

        //Stub out the cs coffee-script module after a build since
        //it will not be needed.
        stubModules: ['cs']
      },
      {
        name: 'main',
        exclude: ['common']
      }
    ]
  };

  module.exports = {
    // config used for r.js and in non-dev mode
    release: {
      options: requireJsOptions
    }
  };

})(module);