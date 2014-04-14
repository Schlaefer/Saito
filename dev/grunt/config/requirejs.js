(function(module) {
  /*jslint node: true */
  'use strict';

  var _ = require('lodash'),
      root = './../../../',
      requireCommon = require(root + 'app/webroot/js/common.js'),
      requireConfig = {
        shim: requireCommon.shim,
        paths: _.extend(requireCommon.paths, {
          moment: '../dev/bower_components/momentjs/js/moment',
          'moment-de': '../dev/bower_components/momentjs/lang/de'
        })
      };

  var requireJsOptions = {
    baseUrl: "./app/webroot/js",
    dir: "./app/webroot/release-tmp",
    optimize: "uglify2", // "none"
    skipDirOptimize: true,
    findNestedDependencies: true,
    preserveLicenseComments: false, // just to many comments in bootstrap
    shim: requireConfig.shim,
    paths: requireConfig.paths, // paths used by r.js
    modules: [
      {
        name: "common",
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
          'humanize',
          'jqueryAutosize',
          'jqueryDropdown',
          'jqueryTinyTimer',
          'jqueryUi',
          'text',
          'tether',
          'templateHelpers',
          'underscore'
        ],
        // jquery is loaded externally on html page
        exclude: ['jquery']
      },
      {
        name: "main",
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