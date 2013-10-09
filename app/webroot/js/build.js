// call in js/ build.js `node lib/r/r.js -o build.js`

({
  baseUrl: "./",
  name: 'main',
  findNestedDependencies: true,
  out: './main-prod.js',
  // dir: "../js-build",
  // optimize: 'none',

    //Stub out the cs coffee-script module after a build since
    //it will not be needed.
    stubModules: ['cs'],

    //The optimization will load CoffeeScript to convert
    //the CoffeeScript files to plain JS. Use the exclude
    //directive so that the coffee-script module is not included
    //in the built file.
    exclude: ['coffee-script'],

  paths: {
    'jquery': 'lib/jquery/jquery-require',
    'jqueryhelpers': 'lib/jqueryhelpers',
    'underscore': 'lib/underscore/underscore',
    'backbone': 'lib/backbone/backbone',
    'backboneLocalStorage': 'lib/backbone/backbone.localStorage',
    'bootstrap': 'bootstrap/bootstrap.min',
    'domReady': 'lib/require/domReady',
    jqueryAutosize: 'lib/jquery.autosize',
    cakeRest: 'lib/saito/backbone.cakeRest',
    'text': 'lib/require/text',
    cs: 'lib/require/cs',
    "coffee-script": 'lib/coffee-script'
  },

  mainConfigFile: 'main.js'
})
