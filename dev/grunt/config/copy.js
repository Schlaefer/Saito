/*jshint node: true */
module.exports = {
  // non minified files needed for debug modus
  nonmin: {
    files: [
      {
        src: ['./node_modules/jquery/dist/jquery.js'],
        dest: './webroot/dist/jquery.js'
      },
      {
        src: ['./bower_components/requirejs/require.js'],
        dest: './webroot/dist/require.js'
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
        src: ['leaflet.js', '*.css'],
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
        src: ['./bower_components/bootstrap/docs/assets/js/bootstrap.min.js'],
        dest: './webroot/dist/bootstrap.min.js'
      },
      {
        src: ['./node_modules/jquery/dist/jquery.min.js'],
        dest: './webroot/dist/jquery.min.js'
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
