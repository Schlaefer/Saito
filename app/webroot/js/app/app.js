define(['marionette', 'app/vent'], function(Marionette, EventBus) {
    // @todo
    //noinspection JSHint
    var AppInitData = SaitoApp;

    //noinspection JSHint
    var whenReady = function(callback) {
        require(['jquery', 'domReady'], function($, domReady) {
            if ($.isReady) {
                callback();
            } else {
                domReady(function() {
                    callback();
                });
            }
        });
    };

    var app = {

        bootstrapShoutbox: function(options) {
            whenReady(function() {
                require(
                    ['modules/shoutbox/shoutbox'],
                    function(ShoutboxModule) {
                        ShoutboxModule.start();
                    });
            });
        },

        bootstrapApp: function(options) {
            require([
                'domReady', 'views/app', 'backbone', 'jquery', 'models/app',
                'views/notification',

                'app/time',

                'lib/jquery.i18n/jquery.i18n.extend',
                'bootstrap', 'lib/saito/backbone.initHelper',
                'lib/saito/backbone.modelHelper', 'fastclick'
            ],
                function(domReady, AppView, Backbone, $, App, NotificationView) {
                    var appView,
                        appReady;

                    App.settings.set(options.SaitoApp.app.settings);
                    App.currentUser.set(options.SaitoApp.currentUser);
                    App.request = options.SaitoApp.request;

                    //noinspection JSHint
                    new NotificationView();

                    window.addEventListener('load', function() {
                        //noinspection JSHint
                        new FastClick(document.body);
                    }, false);

                    // init i18n
                    $.i18n.setUrl(App.settings.get('webroot') + "saitos/langJs");

                    appView = new AppView();

                    appReady = function() {
                        // we need the App object initialized
                        // @todo decouple
                        if ('shouts' in AppInitData) {
                          app.bootstrapShoutbox();
                        }
                        appView.initFromDom({
                            SaitoApp: options.SaitoApp,
                            contentTimer: options.contentTimer
                        });
                    };

                    whenReady(appReady);
                }
            );
        }
    };

    var Application = new Marionette.Application();

      Application.addInitializer(app.bootstrapApp);
      Application.addInitializer(function() {
        require(['modules/html5-notification/html5-notification'],
            function(Html5NotificationModule) {
              Html5NotificationModule.start();
            });
      });
      Application.start({
        contentTimer: contentTimer,
        SaitoApp: AppInitData
      });

      EventBus.reqres.setHandler('webroot', function() {
        return AppInitData.app.settings.webroot;
      });
      EventBus.reqres.setHandler('apiroot', function() {
        return AppInitData.app.settings.webroot + 'api/v1/';
      });

    return Application;

});
