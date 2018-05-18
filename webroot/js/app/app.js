define(['marionette', 'app/core', 'app/vent',
        'modules/html5-notification/html5-notification'],
    function (Marionette, Core, EventBus) {
        // @todo
        //noinspection JSHint
        var AppInitData = SaitoApp;

        //noinspection JSHint
        var whenReady = function (callback) {
            require(['jquery', 'domReady'], function ($, domReady) {
                if ($.isReady) {
                    callback();
                } else {
                    domReady(function () {
                        callback();
                    });
                }
            });
        };

        var app = {
            fireOnPageCallbacks: function (allCallbacks) {
                var callbacks = allCallbacks.afterAppInit;
                _.each(callbacks, function (fct) {
                    fct();
                });

                EventBus.vent.on('isAppVisible', _.once(function (status) {
                    var callbacks = allCallbacks.afterViewInit;
                    _.each(callbacks, function (fct) {
                        fct();
                    });
                }));
            },

            configureAjax: function ($, App) {
              // prevent caching of ajax results
              $.ajaxSetup({cache: false});

              //// set CSRF-token
              $.ajaxPrefilter(function (options, _, xhr) {
                  if (xhr.crossDomain) {
                    return;
                  }
                  xhr.setRequestHeader(App.request.csrf.header, App.request.csrf.token);
              });

              //// set JWT-token
              const jwtCookie = document.cookie.match(/Saito-jwt=([^\s;]*)/)
              if (!jwtCookie) {
                return;
              }
              App.settings.set('jwt', jwtCookie[1]);

              $.ajaxPrefilter(function (options, _, xhr) {
                  if (xhr.crossDomain) {
                    return;
                  }
                  xhr.setRequestHeader('Authorization', 'bearer ' + App.settings.get('jwt'));
              });
            },

            bootstrapApp: function (event, options) {
                require([
                        'domReady', 'views/app', 'backbone', 'jquery', 'models/app',
                        'views/notification',
                        'views/prerequisitesTester',
                        'modules/html5-notification/html5-notification',
                        'modules/usermap/usermap',

                        'app/time', 'lib/saito/isAppVisible',

                        'bootstrap',
                        'lib/jquery.i18n/jquery.i18n.extend',
                        'lib/saito/backbone.initHelper',
                        'lib/saito/backbone.modelHelper',
                    ],
                    function (domReady, AppView, Backbone, $, App, NotificationView, PrerequisitesTesterView, Html5NotificationModule, UsermapModule) {
                        var appView,
                            appReady,
                            prerequisitesTesterView;


                        // do this always first
                        App.settings.set(options.SaitoApp.app.settings);
                        // init i18n, do this always second
                        $.i18n.setUrl(App.settings.get('webroot') + 'da/langJs');

                        App.currentUser.set(options.SaitoApp.currentUser);
                        App.request = options.SaitoApp.request;

                        app.configureAjax($, App);

                        Html5NotificationModule.start();
                        // @todo
                        // UsermapModule.start();

                        //noinspection JSHint
                        new NotificationView();


                        var callbacks = options.SaitoApp.callbacks.beforeAppInit;
                        _.each(callbacks, function (fct) {
                            fct();
                        });

                        prerequisitesTesterView = new PrerequisitesTesterView({
                            el: $('.app-prerequisites-warnings')
                        });

                        appView = new AppView();

                        appReady = function () {
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

        Application.on('start', app.bootstrapApp);
        Application.start({
            contentTimer: contentTimer,
            SaitoApp: AppInitData
        });

        EventBus.vent.reply('webroot', function () {
            return AppInitData.app.settings.webroot;
        });
        EventBus.vent.reply('apiroot', function () {
            return AppInitData.app.settings.webroot + 'api/v1/';
        });

        return Application;

    });
