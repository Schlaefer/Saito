/*jshint node: true */
module.exports = {
    watchCommon: {
        options: {
            basePath: './webroot/css/',
            config: './webroot/css/config.rb',
            watch: true,
            poll: true
        }
    },
    watchDefault: {
        options: {
            basePath: './plugins/Paz/webroot/css/',
            config: './plugins/Paz/webroot/css/config.rb',
            watch: true,
            poll: true
        }
    },
    compileExampleTheme: {
        options: {
            basePath: './plugins/ExampleTheme/webroot/css/',
            config: './plugins/ExampleTheme/webroot/css/config.rb'
        }
    }
};
