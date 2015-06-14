/*jshint node: true */
process.env.TZ = 'Europe/Berlin';

module.exports = function(grunt) {
  'use strict';


  var gruntConfig = {
    pkg: grunt.file.readJSON('package.json'),
    bower: {
      devsetup: {
        options: {
          targetDir: './webroot/dev/bower_components',
          cleanBowerDir: true,
          cleanTargetDir: true,
          layout: 'byComponent'
        }
      }
    },
    uglify: {
      release: {
        files: {
          './webroot/dist/jquery.min.js': ['./webroot/dev/bower_components/jquery/jquery.js'],
          './webroot/dist/require.min.js': ['./webroot/dev/bower_components/requirejs/js/require.js']
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
      testCake: {
        command: './vendor/bin/phpunit --colors',
        options: {
          stdout: true,
          stderr: true,
          failOnError: true
        }
      },
      testCakeStopOn: {
        command: './vendor/bin/phpunit --colors --stop-on-error --stop-on-failure',
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

  var configs = ['compass', 'copy', 'jasmine', 'phpcs', 'requirejs'];
  configs.map(function(config) {
    gruntConfig[config] = require('./dev/grunt/config/' + config);
  });
  gruntConfig.jasmine = require('./dev/grunt/config/jasmine')(gruntConfig.requirejs.release.options);

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
  grunt.registerTask('test:js', ['jasmine', 'jshint']);
  grunt.registerTask('test:cake', ['shell:testCake']);
  grunt.registerTask('test:cakeStopOn', ['shell:testCakeStopOn']);
  grunt.registerTask('test:phpcs', ['phpcs']); // alias for `grunt phpcs`
  grunt.registerTask('test:php', ['test:cake', 'phpcs']);
  // @todo 3.0 make json tests working
  grunt.registerTask('test', [ /* 'test:js', */ 'test:php']);

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
