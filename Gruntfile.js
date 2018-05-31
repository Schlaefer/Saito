/*jshint node: true */
process.env.TZ = 'Europe/Berlin';

module.exports = function (grunt) {
  'use strict';

  var gruntConfig = {
    pkg: grunt.file.readJSON('package.json'),
    copy: {
      nonmin: { // non minified files needed for debug modus
        files: [
          // jQuery Datatables
          {
            expand: true,
            src: [
              './node_modules/datatables.net/js/jquery.dataTables.js',
              './node_modules/datatables.net-bs4/**/*{.js,.css}',
            ],
            dest: './webroot/dist/',
          },
          // CSS
          {
            expand: true,
            flatten: true,
            src: [
              './node_modules/bootstrap/dist/css/bootstrap.min.css',
            ],
            dest: './webroot/css/stylesheets/',
          },
          // font-awesome fonts
          {
            expand: true,
            cwd: './node_modules/font-awesome/fonts/',
            src: '*',
            dest: './webroot/css/stylesheets/fonts/'
          },
        ]
      },
    },
    uglify: {
      release: {
        files: {
          // './webroot/dist/main.min.js': ['./webroot/dist/main.min.js']
        }
      }
    },
    clean: {
      devsetup: [
        // font-awesome
        './webroot/css/stylesheets/fonts/',
      ],
      release: ['./webroot/dist'],
      releasePost: ['./webroot/release-tmp']
    },
    shell: {
      locale: {
        command: `
        targetDir="./webroot/dist/locale/"
        mkdir -p "$targetDir";
        for line in $(find './webroot/src/locale' -type f -name '*.po'); do
          v=$(basename "$line" .po);
          npx po2json --format=mf  webroot/src/locale/$v.po "$targetDir$v".json
        done
        `,
        options: { stdout: true, stderr: true, failOnError: true, }
      },
      webpack: {
        command: 'npx webpack --mode=production --devtool=none',
        options: { stdout: true, stderr: true, failOnError: true, },
      },
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
        command: 'ln -sF $PWD/node_modules/ webroot/dev/node_modules',
        options: {
          stdout: true,
          stderr: true,
          failOnError: true
        }
      },
      // @todo remove
      symlinkBower: {
        command: 'ln -sF $PWD/bower_components/ webroot/dev/bower_components',
        options: {
          stdout: true,
          stderr: true,
          failOnError: true
        }
      },
    },
    sass: {
      options: {
        sourceComments: true,
        sourceMap: false,
        // compression is done by "postcss"-task
        // outputStyle: 'compressed',
      },
      static: {
        files: {
          'webroot/css/stylesheets/static.css': 'webroot/css/src/static.scss',
          'webroot/css/stylesheets/admin.css': 'webroot/css/src/admin.scss',
          'webroot/css/stylesheets/cake.css': 'webroot/css/src/cake.scss',
        }
      },
      theme: {
        files: {
          'plugins/Paz/webroot/css/stylesheets/theme.css': 'plugins/Paz/webroot/css/src/theme.scss',
          'plugins/Paz/webroot/css/stylesheets/night.css': 'plugins/Paz/webroot/css/src/night.scss',
        }
      }
    },
    watch: {
      sassStatic: {
        files: ['webroot/css/src/**/*.scss'],
        tasks: ['sass:static'],
      },
      sassTheme: {
        files: ['plugins/Paz/webroot/css/src/**/*.scss'],
        tasks: ['sass:theme'],
      }
    },
    postcss: {
      options: {
        map: false,
        /*
        map: {
            inline: false, // save all sourcemaps as separate files...
            annotation: 'webroot/css/stylesheets/maps/' // ...to the specified directory
        },
        */
        processors: [
          require('autoprefixer')({ browsers: 'last 2 versions' }), // add vendor prefixes
          //// minify the result
          require('cssnano')({
            //// prevents shortening and namespace collision on keyframes names
            // @see https://github.com/ben-eb/gulp-cssnano/issues/33
            // @see https://github.com/ben-eb/cssnano/issues/247
            reduceIdents: {
              keyframes: false
            },
            discardUnused: {
              keyframes: false
            },
          }),
        ]
      },
      release: {
        src: [
          'webroot/css/stylesheets/*.css',
          'plugins/Paz/webroot/css/stylesheets/*.css'
        ]
      },
    },
  };

  grunt.initConfig(gruntConfig);

  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-uglify-es');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-shell');
  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-postcss');

  // dev-setup
  grunt.registerTask(
    'dev-setup',
    ['clean:devsetup', 'shell:yarn', 'shell:symlinkNode', 'shell:symlinkBower', 'copy:nonmin']
  );

  // release
  grunt.registerTask('release', [
    // cleanup
    'clean:release',
    // CSS
    'sass:static',
    'sass:theme',
    'postcss:release',
    // webpack
    'shell:webpack',
    // JS
    'copy:nonmin',
    'uglify:release',
    // l10n
    'shell:locale',
    // cleanup
    'clean:releasePost'
  ]);
};
