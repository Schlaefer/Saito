/*jshint node: true */
module.exports = function(requireJsOptions) {
  'use strict';

  var _ = require('lodash');
  _.extend(requireJsOptions.shim, {
    sinon: { exports: 'sinon' },
    jsjq: { deps: ['jquery'] }
  });
  _.extend(requireJsOptions.paths, {
    sinon: '../dev/bower_components/sinonjs/sinon',
    jsjq: '../dev/bower_components/jasmine-jquery/jasmine-jquery'
  });

  return {
    test: {
      // src: './webroot/js/main.js',
      options: {
        display: 'full',
        specs: './webroot/js/tests/**/*Spec.js',
        vendor: [
          './webroot/dev/bower_components/jquery/jquery.js',
          './webroot/js/bootstrap/bootstrap.js',
          './app/Plugin/JasmineJs/webroot/js/jasmine-jquery.js',
          './app/Plugin/JasmineJs/webroot/js/sinon-1.6.0.js'
        ],
        helpers: [
          './webroot/js/lib/bootstrapHelper.js',
          './webroot/js/tests/jasmineBootstrapHelper.js',
          './webroot/js/lib/postBootstrapHelper.js'
        ],
        keepRunner: false,
        template: require('grunt-template-jasmine-requirejs'),
        templateOptions: {
          requireConfig: requireJsOptions
        }
      }
    }
  };
};
