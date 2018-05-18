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
    // optimizer not working with ES2015+ JS features
    // see https://github.com/requirejs/r.js/issues/959
    // so use non-require.js uglifier later
    optimize: 'none', // 'uglify2'|'none'
    skipDirOptimize: true,
    findNestedDependencies: true,
    preserveLicenseComments: false, // just to many comments in bootstrap
    shim: requireConfig.shim,
    paths: requireConfig.paths, // paths used by r.js
    // for old marionette version not finding backbone
    wrapShim: true,
    modules: [
      {
        name: 'common',
        include: [
          'backbone',
          'backboneLocalStorage',
          'backbone.babysitter',
          'backbone.radio',
          'blazy',
          'bootstrap',
          'cakeRest',
          'domReady',
          'drop',
          'marionette',
          'moment', 'moment-de',
          'humanize',
          'jqueryAutosize',
          'jqueryTinyTimer',
          'jqueryUi',
          'text',
          'tether',
          'underscore'
        ],

        // jquery: is loaded externally on html page
        exclude: ['jquery'],
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
