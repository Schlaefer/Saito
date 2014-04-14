module.exports = {
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
      },
      // font-awesome fonts
      {
        expand: true,
        cwd: './app/webroot/dev/bower_components/font-awesome/fonts/',
        src: '*',
        dest: './app/webroot/css/stylesheets/fonts/'
      },
      // font-awesome scss
      {
        expand: true,
        cwd: './app/webroot/dev/bower_components/font-awesome/scss/',
        src: '*',
        dest: './app/webroot/css/src/partials/lib/font-awesome/'
      },
      // leaflet
      {
        expand: true,
        cwd: './app/Vendor/leaflet/',
        src: '**',
        dest: './app/webroot/dist/leaflet/'
      },
      {
        expand: true,
        cwd: './app/webroot/dev/bower_components/leaflet.markercluster/dist/',
        src: '*',
        dest: './app/webroot/dist/leaflet/'
      }
    ]
  },
  release: {
    files: [
      {
        src: ['./app/webroot/release-tmp/common.js'],
        dest: './app/webroot/dist/common.min.js'
      },
      {
        src: ['./app/webroot/release-tmp/main.js'],
        dest: './app/webroot/dist/main.min.js'
      },
      {
        expand: true,
        cwd: './app/webroot/dev/vendors/farbtastic/',
        src: '*',
        dest: './app/webroot/js/farbtastic/'
      }
    ]
  }
};
