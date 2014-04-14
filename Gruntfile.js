//noinspection JSHint

module.exports = function(grunt) {
  'use strict';

  var requireJsOptions = {
    baseUrl: "./app/webroot/js",
    dir: "./app/webroot/release-tmp",
    optimize: "uglify2", // "none"
    skipDirOptimize: true,
    findNestedDependencies: true,
    // just to many comments in bootstrap
    preserveLicenseComments: false,
    shim: {
      drop: {
        deps: ['tether'],
        exports: 'Drop'
      },
      jqueryTinyTimer: {
        deps: [/* 'jquery' */]
      }
    },
    // paths used by r.js
    paths: {
      backbone: '../dev/bower_components/backbone/js/backbone',
      backboneLocalStorage: '../dev/bower_components/Backbone.localStorage/js/backbone.localStorage',
      cakeRest: 'lib/saito/backbone.cakeRest',
      domReady: '../dev/bower_components/requirejs-domready/js/domReady',
      drop: '../dev/bower_components/drop/drop',
      fastclick: '../dev/bower_components/fastclick/js/fastclick',
      humanize: '../dev/bower_components/humanize/js/humanize',
      jquery: '../dev/bower_components/jquery/jquery',
      jqueryAutosize: '../dev/bower_components/jquery-autosize/js/jquery.autosize',
      jqueryDropdown: '../dev/bower_components/jquery-dropdown/jquery.dropdown',
      jqueryTinyTimer: '../dev/bower_components/jquery-tinytimer/jquery.tinytimer',
      jqueryUi: 'lib/jquery-ui/jquery-ui.custom.min',
      templateHelpers: 'lib/saito/templateHelpers',
      tether: '../dev/bower_components/tether/tether',
      text: '../dev/bower_components/requirejs-text/js/text',
      underscore: '../dev/bower_components/lodash/js/lodash',
      // marionette
      marionette: '../dev/bower_components/marionette/backbone.marionette',
      "backbone.wreqr": '../dev/bower_components/backbone.wreqr/js/backbone.wreqr',
      "backbone.babysitter": '../dev/bower_components/backbone.babysitter/js/backbone.babysitter',
      // moment
      moment: '../dev/bower_components/momentjs/js/moment',
      'moment-de': '../dev/bower_components/momentjs/lang/de'
    },
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