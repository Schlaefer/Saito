/*jshint node: true */
module.exports = {
  watchCommon: {
    options: {
      basePath: './app/webroot/css/',
      config: './app/webroot/css/config.rb',
      watch: true,
      poll: true
    }
  },
  watchDefault: {
    options: {
      basePath: './app/View/Themed/Paz/webroot/css/',
      config: './app/View/Themed/Paz/webroot/css/config.rb',
      watch: true,
      poll: true
    }
  },
  compileExampleTheme: {
    options: {
      basePath: './app/View/Themed/Example/webroot/css/',
      config: './app/View/Themed/Example/webroot/css/config.rb'
    }
  }
};
