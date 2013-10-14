//noinspection JSHint

module.exports = function(grunt) {
  'use strict';

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    bower: {
      devsetup: {
        options: {
          targetDir: './app/webroot/dev/bower_components',
          cleanBowerDir: true,
          layout: 'byComponent'
        }
      }
    },
    requirejs: {
      release: {
        options: {
          baseUrl: "./app/webroot/js",
          name: 'main',
          findNestedDependencies: true,
          out: './app/webroot/js/main-prod.js',
          optimize: 'uglify2',
          // dir: "../js-build",
          // optimize: 'none',

          //Stub out the cs coffee-script module after a build since
          //it will not be needed.
          stubModules: ['cs'],

          //The optimization will load CoffeeScript to convert
          //the CoffeeScript files to plain JS. Use the exclude
          //directive so that the coffee-script module is not included
          //in the built file.
          exclude: ['coffee-script'],

          paths: {
            'jquery': 'lib/jquery/jquery-require',
            'jqueryhelpers': 'lib/jqueryhelpers',
            'underscore': 'lib/underscore/underscore',
            'backbone': 'lib/backbone/backbone',
            'backboneLocalStorage': 'lib/backbone/backbone.localStorage',
            'bootstrap': 'bootstrap/bootstrap.min',
            'domReady': 'lib/require/domReady',
            jqueryAutosize: 'lib/jquery.autosize',
            cakeRest: 'lib/saito/backbone.cakeRest',
            'text': 'lib/require/text',
            cs: 'lib/require/cs',
            "coffee-script": 'lib/coffee-script'
          },

          mainConfigFile: './app/webroot/js/main.js'
        }
      }
    }
  });

  grunt.loadNpmTasks('grunt-bower-task');
  grunt.loadNpmTasks('grunt-contrib-requirejs');

  grunt.registerTask('dev-setup', [
    'bower:devsetup'
  ]);
  grunt.registerTask('release', [
    'requirejs:release'
  ]);
};