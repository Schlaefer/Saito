// call in js/ build.js `node lib/r/r.js -o build.js`

({
  baseUrl: "./",
  name: 'main',
  out: './main-prod.js',
  // dir: "../js-build",
  // optimize: 'none',
  paths: {
    'jquery': 'lib/jquery/jquery-require',
		'jqueryhelpers': 'lib/jqueryhelpers',
    'underscore': 'lib/underscore/underscore',
    'backbone': 'lib/backbone/backbone',
    'backboneLocalStorage': 'lib/backbone/backbone.localStorage',
		'domReady': 'lib/domReady'
  },
  mainConfigFile: 'main.js'
})
