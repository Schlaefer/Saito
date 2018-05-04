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

            configureAjax: function ($, csrfConfig) {
                // prevent caching of ajax results
                $.ajaxSetup({cache: false});
                // set CSRF-token
                $.ajaxPrefilter(function (options, _, xhr) {
                    if (!xhr.crossDomain) {
                        xhr.setRequestHeader(csrfConfig.header, csrfConfig.token);
                    }
                });
            },

            bootstrapApp: function (event, options) {
                require([
                        'domReady', 'views/app', 'backbone', 'jquery', 'models/app',
                        'views/notification',
                        'views/prerequisitesTester',
                        'modules/html5-notification/html5-notification',
                        'modules/usermap/usermap',
                        'fastclick',

                        'app/time', 'lib/saito/isAppVisible',

                        'bootstrap',
                        'lib/jquery.i18n/jquery.i18n.extend',
                        'lib/saito/backbone.initHelper',
                        'lib/saito/backbone.modelHelper', 'fastclick'
                    ],
                    function (domReady, AppView, Backbone, $, App, NotificationView, PrerequisitesTesterView, Html5NotificationModule, UsermapModule, FastClick) {
                        var appView,
                            appReady,
                            prerequisitesTesterView;


                        // do this always first
                        App.settings.set(options.SaitoApp.app.settings);
                        // init i18n, do this always second
                        $.i18n.setUrl(App.settings.get('webroot') + 'da/langJs');

                        App.currentUser.set(options.SaitoApp.currentUser);
                        App.request = options.SaitoApp.request;

                        app.configureAjax($, App.request.csrf);

                        // @todo
                        // Html5NotificationModule.start();
                        // @todo
                        // UsermapModule.start();

                        //noinspection JSHint
                        new NotificationView();


                        var callbacks = options.SaitoApp.callbacks.beforeAppInit;
                        _.each(callbacks, function (fct) {
                            fct();
                        });

                        window.addEventListener('load', function () {
                            FastClick.attach(document.body);
                        }, false);

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
