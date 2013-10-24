//noinspection JSHint

module.exports = function(grunt) {
  'use strict';

  var requireJsOptions = {
    baseUrl: "./app/webroot/js",
    dir: "./app/webroot/release-tmp",
    optimize: "uglify2",
    skipDirOptimize: true,
    findNestedDependencies: true,
    // just to many comments in bootstrap
    preserveLicenseComments: false,
    shim: {
      underscore: {
        exports: '_'
      },
      backbone: {
        deps: ['underscore' /*, 'jquery' */],
        exports: 'Backbone'
      },
      backboneLocalStorage: {
        deps: ['backbone'],
        exports: 'Store'
      },
      marionette: {
        deps: ['underscore', 'backbone' /*, 'jquery' */],
        exports: 'Marionette'
      }
    },
    // paths used by r.js
    paths: {
      backbone: '../dev/bower_components/backbone/js/backbone',
      backboneLocalStorage: '../dev/bower_components/Backbone.localStorage/js/backbone.localStorage',
      bootstrap: 'bootstrap/bootstrap',
      cakeRest: 'lib/saito/backbone.cakeRest',
      domReady: '../dev/bower_components/requirejs-domready/js/domReady',
      fastclick: '../dev/bower_components/fastclick/js/fastclick',
      humanize: '../dev/bower_components/humanize/js/humanize',
      jquery: '../dev/bower_components/jquery/jquery',
      jqueryAutosize: '../dev/bower_components/jquery-autosize/js/jquery.autosize',
      jqueryUi: 'lib/jquery-ui/jquery-ui-1.9.2.custom.min',
      marionette: '../dev/bower_components/marionette/backbone.marionette',
      text: '../dev/bower_components/requirejs-text/js/text',
      underscore: '../dev/bower_components/underscore/js/underscore',
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
          'bootstrap',
          'cakeRest',
          'domReady',
          'fastclick',
          'marionette',
          'humanize',
          'jqueryAutosize',
          'jqueryUi',
          'text',
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

  grunt.initConfig({
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
        copy: {
          // non minified files needed for debug modus
          nonmin: {
            files: [
              {
                src: ['./app/webroot/dev/bower_components/jquery/jquery.js'],
                dest: './app/webroot/dist/jquery.js'
              },
              {
                src: ['./app/webroot/dev/bower_components/requirejs/js/require.js'],
                dest: './app/webroot/dist/require.js'
              }
            ]
          },
          release: {
            files: [
              {
                src: ['./app/webroot/release-tmp/common.js'],
                dest: './app/webroot/dist/common.js'
              },
              {
                src: ['./app/webroot/release-tmp/main.js'],
                dest: './app/webroot/dist/main.js'
              },
              {
                src: ['./app/webroot/dev/vendors/countdown/jquery.countdown.min.js'],
                dest: './app/webroot/dist/jquery.countdown.min.js'
              },
              {
                expand: true,
                cwd: './app/webroot/dev/vendors/farbtastic/',
                src: '*',
                dest: './app/webroot/js/farbtastic/'
              }
            ]
          }
        },
        clean: {
          release: ['./app/webroot/dist'],
          releasePost: ['./app/webroot/release-tmp']
        },
        phpcs: {
          controllers: {dir: './app/Controller'},
          models: {dir: './app/Model'},
          lib: {dir: './app/Lib'},
          tests: {
            dir: './app/Test',
            options: {
              ignore: 'Selenium'
            }
          },
          view: {
            dir: './app/View',
            options: {
              ignore: 'Themed'
            }
          },
          plugins: {
            dir: './app/Plugin',
            options: {
              ignore: 'Embedly,Geshi,FileUpload,Flattr,Install,Markitup,Search,SimpleCaptcha,webroot'
            }
          },
          options: {
            standard: 'app/Test/phpcs-ruleset.xml',
            ignore: 'webroot',
            // suppress warnings
            warningSeverity: 8
          }
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
            command: './lib/Cake/Console/cake test app all --stderr',
            options: {
              stdout: true,
              stderr: true,
              failOnError: true
            }
          }
        },
        jasmine: {
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
                './app/webroot/js/tests/jasmineBootstrapHelper.js'
              ],
              keepRunner: false,
              template: require('grunt-template-jasmine-requirejs'),
              templateOptions: {
                requireConfig: requireJsOptions
              }
            }
          }
        }
      }
  );

  grunt.loadNpmTasks('grunt-bower-task');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-requirejs');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-phpcs');
  grunt.loadNpmTasks('grunt-contrib-jasmine');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-shell');

  grunt.registerTask('dev-setup', [ 'bower:devsetup', 'copy:nonmin' ]);

  grunt.registerTask('test:js', ['jasmine', 'jshint']);
  grunt.registerTask('test:cake', ['shell:testCake']);
  grunt.registerTask('test:php', ['test:cake', 'phpcs']);
  grunt.registerTask('test', ['test:js', 'test:php']);

  grunt.registerTask('release', [
    'clean:release',
    'requirejs:release',
    'uglify:release',
    'copy:release',
    'copy:nonmin',
    'clean:releasePost'
  ]);
}
;