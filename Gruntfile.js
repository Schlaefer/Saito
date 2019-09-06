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
            dest: './webroot/js/',
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
          /// Assets for plugins/SprectrumColorpicker
          {
            src: './node_modules/spectrum-colorpicker/spectrum.js',
            dest: './plugins/SpectrumColorpicker/webroot/js/spectrum.js',
          },
          {
            src: './node_modules/spectrum-colorpicker/spectrum.css',
            dest: './plugins/SpectrumColorpicker/webroot/css/spectrum.css',
          },
          /// Assets Cabin font
          {
            expand: true,
            flatten: true,
            src: './node_modules/typeface-cabin/files/cabin-latin-[4|7]00.woff*',
            dest: './plugins/Bota/webroot/fonts/',
          },
          {
            expand: true,
            flatten: true,
            src: './node_modules/typeface-cabin/files/cabin-latin-[4|7]00italic.woff*',
            dest: './plugins/Bota/webroot/fonts/',
          },
          /// Assets Fenix font
          {
            expand: true,
            flatten: true,
            src: './node_modules/typeface-fenix/files/fenix-latin-400.woff*',
            dest: './plugins/Bota/webroot/fonts/',
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
      release: ['./webroot/js/**/!(empty)'],
      releasePost: ['./webroot/release-tmp']
    },
    shell: {
      locale: {
        command: `
          node dev/gettextExtractor.js;
          msgmerge --update --backup=none frontend/src/locale/de.po frontend/src/locale/messages.pot;
          msgmerge --update --backup=none frontend/src/locale/en.po frontend/src/locale/messages.pot;
          rm frontend/src/locale/messages.pot;
        `,
        options: { stdout: true, stderr: true, failOnError: true, }
      },
      localeRelease: {
        command: `
        targetDir="./webroot/js/locale/"
        mkdir -p "$targetDir";
        for line in $(find './frontend/src/locale' -type f -name '*.po'); do
          v=$(basename "$line" .po);
          npx po2json --format=mf  frontend/src/locale/$v.po "$targetDir$v".json
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
    },
    'dart-sass': {
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
        }
      },
      theme: {
        files: {
          'plugins/Bota/webroot/css/night.css': 'plugins/Bota/webroot/css/src/night.scss',
          'plugins/Bota/webroot/css/theme.css': 'plugins/Bota/webroot/css/src/theme.scss',
        }
      },
    },
    watch: {
      sassStatic: {
        files: ['webroot/css/src/**/*.scss'],
        tasks: ['dart-sass:static'],
      },
      sassTheme: {
        files: ['plugins/Bota/webroot/css/src/**/*.scss'],
        tasks: ['dart-sass:theme'],
      },
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
          'webroot/css/stylesheets/static.css',
          'plugins/Bota/webroot/css/*.css'
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
  grunt.loadNpmTasks('grunt-dart-sass');
  grunt.loadNpmTasks('grunt-postcss');

  // dev-setup
  grunt.registerTask(
    'dev-setup',
    ['clean:devsetup', 'shell:yarn', 'copy:nonmin']
  );

  // release
  grunt.registerTask('release', [
    // cleanup
    'clean:release',
    // CSS
    'dart-sass:static',
    'dart-sass:theme',
    'postcss:release',
    // webpack
    'shell:webpack',
    // JS
    'copy:nonmin',
    'uglify:release',
    // l10n
    'shell:localeRelease',
    // cleanup
    'clean:releasePost'
  ]);
};
