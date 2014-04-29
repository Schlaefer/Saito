define(['marionette', 'app/core', 'app/vent',
  'modules/html5-notification/html5-notification'],
  function(Marionette, Core, EventBus) {
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

      fireOnPageCallbacks: function(allCallbacks) {
        var callbacks = allCallbacks.afterAppInit;
        _.each(callbacks, function(fct) { fct(); });

        EventBus.vent.on('isAppVisible', _.once(function(status){
          var callbacks = allCallbacks.afterViewInit;
          _.each(callbacks, function(fct) { fct(); });
        }));
      },

      bootstrapShoutbox: function() {
        whenReady(function() {
          require(['modules/shoutbox/shoutbox'], function(ShoutboxModule) {
            ShoutboxModule.start();
          });
        });
      },

      bootstrapApp: function(options) {
        require([
          'domReady', 'views/app', 'backbone', 'jquery', 'models/app',
          'views/notification',
          'views/prerequisitesTester',
          'modules/html5-notification/html5-notification',
          'modules/usermap/usermap',

          'app/time', 'lib/Saito/isAppVisible',

          'lib/jquery.i18n/jquery.i18n.extend',
          'jqueryDropdown',
          'lib/saito/backbone.initHelper',
          'lib/saito/backbone.modelHelper', 'fastclick'
        ],
          function(domReady, AppView, Backbone, $, App, NotificationView, PrerequisitesTesterView, Html5NotificationModule, UsermapModule) {
            var appView,
              appReady,
              prerequisitesTesterView;

            // do this always first
            App.settings.set(options.SaitoApp.app.settings);
            // init i18n, do this always second
            $.i18n.setUrl(App.settings.get('webroot') + 'da/langJs');

            App.currentUser.set(options.SaitoApp.currentUser);
            App.request = options.SaitoApp.request;

            Html5NotificationModule.start();
            UsermapModule.start();

            //noinspection JSHint
            new NotificationView();

            window.addEventListener('load', function() {
              //noinspection JSHint
              new FastClick(document.body);
            }, false);

            prerequisitesTesterView = new PrerequisitesTesterView({
              el: $('.app-prerequisites-warnings')
            });

            appView = new AppView();

            appReady = function() {
              // we need the App object initialized
              // @todo decouple
              if ('shouts' in AppInitData) {
                app.bootstrapShoutbox();
              }
              app.fireOnPageCallbacks(options.SaitoApp.callbacks);
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

    var Application = Core;

    Application.addInitializer(app.bootstrapApp);
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
