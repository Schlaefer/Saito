/*jshint node: true */
module.exports = {
  // non minified files needed for debug modus
  nonmin: {
    files: [
      {
        src: ['./webroot/dev/bower_components/jquery/jquery.js'],
        dest: './webroot/dist/jquery.js'
      },
      {
        src: ['./webroot/dev/bower_components/requirejs/js/require.js'],
        dest: './webroot/dist/require.js'
      },
      // font-awesome fonts
      {
        expand: true,
        cwd: './webroot/dev/bower_components/font-awesome/fonts/',
        src: '*',
        dest: './webroot/css/stylesheets/fonts/'
      },
      // font-awesome scss
      {
        expand: true,
        cwd: './webroot/dev/bower_components/font-awesome/scss/',
        src: '*',
        dest: './webroot/css/src/partials/lib/font-awesome/'
      },
      // leaflet
      {
        expand: true,
        cwd: './webroot/dev/bower_components/leaflet/',
        src: '**',
        dest: './webroot/dist/leaflet/'
      },
      {
        expand: true,
        cwd: './webroot/dev/bower_components/leaflet.markercluster/dist/',
        src: '*',
        dest: './webroot/dist/leaflet/'
      }
    ]
  },
  release: {
    files: [
      {
        src: ['./webroot/release-tmp/common.js'],
        dest: './webroot/dist/common.min.js'
      },
      {
        src: ['./webroot/release-tmp/main.js'],
        dest: './webroot/dist/main.min.js'
      },
      {
        expand: true,
        cwd: './webroot/dev/vendors/farbtastic/',
        src: '*',
        dest: './webroot/js/farbtastic/'
      }
    ]
  }
};
