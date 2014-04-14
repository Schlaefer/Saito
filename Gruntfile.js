//noinspection JSHint

var _ = require('lodash');

module.exports = function(grunt) {
  'use strict';

  var requireCommon = require('./app/webroot/js/common.js'),
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

  var gruntConfig = {
    pkg: grunt.file.readJSON('package.json'),
    bower: {
      devsetup: {
        options: {
          targetDir: './app/webroot/dev/bower_components',
          cleanBowerDir: true,
          cleanTargetDir: true,
          layout: 'byComponent'
        }
      }
    },
    requirejs: {
      // config used for r.js and in non-dev mode
      release: {
        options: requireJsOptions
      }
    },
    uglify: {
      release: {
        files: {
          './app/webroot/dist/jquery.min.js': ['./app/webroot/dev/bower_components/jquery/jquery.js'],
          './app/webroot/dist/require.min.js': ['./app/webroot/dev/bower_components/requirejs/js/require.js']
        }
      }
    },
    clean: {
      devsetup: [
        // font-awesome
        './app/webroot/css/stylesheets/fonts/',
        './app/webroot/css/src/partials/lib/font-awesome/'
      ],
      release: ['./app/webroot/dist'],
      releasePost: ['./app/webroot/release-tmp']
    },
    jshint: {
      all: ['Gruntfile.js', './app/webroot/js/**/*.js'],
      options: {
        ignores: [
          './app/webroot/js/bootstrap/*.js',
          './app/webroot/js/farbtastic/*.js',
          './app/webroot/js/lib/**/*.js'
        ]
      }
    },
    shell: {
      testCake: {
        command: './app/Console/cake test app all --stderr',
        options: {
          stdout: true,
          stderr: true,
          failOnError: true
        }
      }
    },
    concurrent: {
      compassWatch: ['compass:watchCommon', 'compass:watchDefault'],
      options: {
        logConcurrentOutput: true
      }
    }
  };

  var configs = ['copy', 'phpcs', 'jasmine', 'compass'];
  configs.map(function(config) {
    gruntConfig[config] = require('./dev/grunt/config/' + config);
  });

  grunt.initConfig(gruntConfig);

  grunt.loadNpmTasks('grunt-bower-task');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-requirejs');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-phpcs');
  grunt.loadNpmTasks('grunt-contrib-jasmine');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-shell');
  grunt.loadNpmTasks('grunt-concurrent');
  grunt.loadNpmTasks('grunt-contrib-compass');

  // dev-setup
  grunt.registerTask('dev-setup', [
    'clean:devsetup', 'bower:devsetup', 'copy:nonmin'
  ]);

  // test
  grunt.registerTask('test:js', [
    // jasmine is broken with version 0.6.x
    // https://github.com/cloudchen/grunt-template-jasmine-requirejs
    // 'jasmine',
    'jshint']);
  grunt.registerTask('test:cake', ['shell:testCake']);
  grunt.registerTask('test:phpcs', ['phpcs']); // alias for `grunt phpcs`
  grunt.registerTask('test:php', ['test:cake', 'phpcs']);
  grunt.registerTask('test', ['test:js', 'test:php']);

  // compass
  grunt.registerTask('compass:watch', 'concurrent:compassWatch');
  grunt.registerTask('compass:compile', ['compass:compileExampleTheme']);

  // release
  grunt.registerTask('release', [
    'clean:release',
    // 'compass:compile',
    'requirejs:release',
    'uglify:release',
    'copy:release',
    'copy:nonmin',
    'clean:releasePost'
  ]);
};