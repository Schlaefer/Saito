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
          paths: {
            requireLib: 'lib/require/require.min'
          },
          include: ['requireLib'],
          // dir: "../js-build",
          // optimize: 'none',

          // just to many comments in bootstrap
          preserveLicenseComments: false,
          mainConfigFile: './app/webroot/js/main.js'
        }
      }
    },
    uglify: {
      release: {
        files: {
          './app/webroot/js/jquery.min.js': ['./app/webroot/dev/bower_components/jquery/jquery.js']
        }
      }
    }
  });

  grunt.loadNpmTasks('grunt-bower-task');
  grunt.loadNpmTasks('grunt-contrib-requirejs');
  grunt.loadNpmTasks('grunt-contrib-uglify');

  grunt.registerTask('dev-setup', [
    'bower:devsetup'
  ]);
  grunt.registerTask('release', [
    'requirejs:release',
    'uglify:release'
  ]);
};