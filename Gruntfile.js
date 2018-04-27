/*jshint node: true */
process.env.TZ = 'Europe/Berlin';

module.exports = function(grunt) {
  'use strict';


  var gruntConfig = {
    pkg: grunt.file.readJSON('package.json'),
    uglify: {
      release: {
        files: {
          './webroot/dist/require.min.js': ['./bower_components/requirejs/require.js'],
          './webroot/dist/common.min.js': ['./webroot/dist/common.min.js'],
          './webroot/dist/main.min.js': ['./webroot/dist/main.min.js']
        }
      }
    },
    clean: {
      devsetup: [
        // font-awesome
        './webroot/css/stylesheets/fonts/',
        './webroot/css/src/partials/lib/font-awesome/'
      ],
      release: ['./webroot/dist'],
      releasePost: ['./webroot/release-tmp']
    },
    jshint: {
      all: ['Gruntfile.js', './webroot/js/**/*.js'],
      options: {
        ignores: [
          './webroot/js/bootstrap/*.js',
          './webroot/js/lib/**/*.js'
        ]
      }
    },
    shell: {
      yarn: {
        command: 'yarn',
        options: {
          stdout: true,
          stderr: true,
          failOnError: true
        }
      },
      // access too JS-libraries for development in webroot through browser
      symlinkNode: {
        command: 'ln -s $PWD/node_modules/ webroot/dev/node_modules',
        options: {
          stdout: true,
          stderr: true,
          failOnError: true
        }
      },
      // @todo remove
      symlinkBower: {
        command: 'ln -s $PWD/bower_components/ webroot/dev/bower_components',
        options: {
          stdout: true,
          stderr: true,
          failOnError: true
        }
      },
      testCake: {
        command: './vendor/bin/phpunit --colors --stderr',
        options: {
          stdout: true,
          stderr: true,
          failOnError: true
        }
      },
      testCakeStopOn: {
        command: './vendor/bin/phpunit --colors --stderr --stop-on-error --stop-on-failure',
        options: {
          stdout: true,
          stderr: true,
          failOnError: true
        }
      }
    },
    sass: {
      options: {
        sourceMap: false,
        outputStyle: 'compressed',
      },
      release: {
        files: {
          'webroot/css/stylesheets/static.css': 'webroot/css/src/static.scss',
          'webroot/css/stylesheets/admin.css': 'webroot/css/src/admin.scss',
          'webroot/css/stylesheets/cake.css': 'webroot/css/src/cake.scss',
        }
      }
    },
  };

  var configs = ['compass', 'copy', 'jasmine', 'phpcs', 'requirejs'];
  configs.map(function(config) {
    gruntConfig[config] = require('./dev/grunt/config/' + config);
  });
  gruntConfig.jasmine = require('./dev/grunt/config/jasmine')(gruntConfig.requirejs.release.options);

  grunt.initConfig(gruntConfig);

  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-requirejs');
  grunt.loadNpmTasks('grunt-contrib-uglify-es');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-phpcs');
  grunt.loadNpmTasks('grunt-contrib-jasmine');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-shell');
  grunt.loadNpmTasks('grunt-sass');

  // dev-setup
  grunt.registerTask('dev-setup', [
    'clean:devsetup', 'shell:yarn', 'shell:symlinkNode', 'shell:symlinkBower', 'copy:nonmin' 
  ]);

  // test
  grunt.registerTask('test:js', ['jasmine', 'jshint']);
  grunt.registerTask('test:cake', ['shell:testCake']);
  grunt.registerTask('test:cakeStopOn', ['shell:testCakeStopOn']);
  grunt.registerTask('test:phpcs', ['phpcs']); // alias for `grunt phpcs`
  grunt.registerTask('test:php', ['test:cake', 'phpcs']);
  grunt.registerTask('test', ['test:js', 'test:php']);

  // release
  grunt.registerTask('release', [
    'clean:release',
    'sass:release',
    'requirejs:release',
    'copy:release',
    'copy:nonmin',
    'uglify:release',
    'clean:releasePost'
  ]);
};
