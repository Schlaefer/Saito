/*jshint node: true */
module.exports = {
  // non minified files needed for debug modus
  nonmin: {
    files: [
      {
        expand: true,
        flatten: true,
        src: [
          './bower_components/requirejs/require.js',
          './node_modules/jquery/dist/jquery.js',
        ],
        dest: './webroot/dist/',
      },
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
        cwd: './bower_components/font-awesome/fonts/',
        src: '*',
        dest: './webroot/css/stylesheets/fonts/'
      },
      // font-awesome scss
      {
        expand: true,
        cwd: './bower_components/font-awesome/scss/',
        src: '*',
        dest: './webroot/css/src/partials/lib/font-awesome/'
      },
      // leaflet
      {
        expand: true,
        cwd: './bower_components/leaflet/dist/',
        src: ['images/*', 'leaflet.js', '*.css'],
        dest: './webroot/dist/leaflet/'
      },
      {
        expand: true,
        cwd: './bower_components/leaflet.markercluster/dist/',
        src: '*',
        dest: './webroot/dist/leaflet/'
      }
    ]
  },
  release: {
    files: [
      // copy minified JS-files from packages which are used vanilla
      {
        src: ['./node_modules/bootstrap/dist/js/bootstrap.bundle.min.js'],
        dest: './webroot/dist/bootstrap.min.js'
      },
      {
        expand: true,
        flatten: true,
        src: [
          './node_modules/jquery/dist/jquery.min.js'
        ],
        dest: './webroot/dist/'
      },
      // copy minified JS-files from saito-build
      {
        src: ['./webroot/release-tmp/common.js'],
        dest: './webroot/dist/common.min.js'
      },
      {
        src: ['./webroot/release-tmp/main.js'],
        dest: './webroot/dist/main.min.js'
      }
    ]
  }
};
