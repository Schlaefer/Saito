/*jshint node: true */
module.exports = function(requireJsOptions) {
  'use strict';

  var _ = require('lodash');
  _.extend(requireJsOptions.shim, {
    sinon: { exports: 'sinon' }
  });
  _.extend(requireJsOptions.paths, {
    sinon: '../dev/bower_components/sinonjs/sinon',
    jsjq: '../dev/bower_components/jasmine-jquery/jasmine-jquery'
  });

  return {
    test: {
      // src: './app/webroot/js/main.js',
      options: {
        specs: './app/webroot/js/tests/**/*Spec.js',
        vendor: [
          './app/webroot/dev/bower_components/jquery/jquery.js',
          './app/webroot/js/bootstrap/bootstrap.js',
          './app/Plugin/JasmineJs/webroot/js/jasmine-jquery.js',
          './app/Plugin/JasmineJs/webroot/js/sinon-1.6.0.js'
        ],
        helpers: [
          './app/webroot/js/lib/bootstrapHelper.js',
          './app/webroot/js/tests/jasmineBootstrapHelper.js',
          './app/webroot/js/lib/postBootstrapHelper.js'
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
