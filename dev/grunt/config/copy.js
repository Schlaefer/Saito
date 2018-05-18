/*jshint node: true */
module.exports = {
  // non minified files needed for debug modus
  nonmin: {
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
};
