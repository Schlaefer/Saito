define([
    'underscore',
    'backbone',
    'models/appSetting',
    'models/appStatus',
    'models/currentUser'
], function (_, Backbone,
    AppSettingModel, AppStatusModel, CurrentUserModel
    ) {

    "use strict";

    var AppModel = Backbone.Model.extend({


        /**
         * global event handler for the app
         */
        eventBus: null,

        /**
         * CakePHP app settings
         */
        settings: null,

        /**
         * Current app status from server
         */
        status: null,

        /**
         * CurrentUser
         */
        currentUser: null,

        /**
         * Request info from CakePHP
         */
        request: null,


        initialize: function () {
            this.eventBus = _.extend({}, Backbone.Events);
            this.settings = new AppSettingModel();
            this.status = new AppStatusModel();
            this.currentUser = new CurrentUserModel();
        },

        initAppStatusUpdate: function () {
            var resetRefreshTime,
                updateAppStatus,
                setTimer,
                timerId,
                stopTimer,
                refreshTimeAct,
                refreshTimeBase = 5000,
                refreshTimeMax = 30000;

            stopTimer = function () {
                if (timerId !== undefined) {
                    clearTimeout(timerId);
                }
            },

            resetRefreshTime = function () {
                stopTimer();
                refreshTimeAct = refreshTimeBase;
            };

            setTimer = function () {
                timerId = setTimeout(
                    updateAppStatus,
                    refreshTimeAct
                );
            };

            updateAppStatus = _.bind(function () {
                setTimer();
                this.status.fetch();
                refreshTimeAct = Math.floor(
                    refreshTimeAct * (1 + refreshTimeAct / 40000)
                );
                if (refreshTimeAct > refreshTimeMax) {
                    refreshTimeAct = refreshTimeMax;
                }
            }, this);

            this.status.setWebroot(this.settings.get('webroot'));

            this.listenTo(
                this.status,
                'change',
                function () {
                    resetRefreshTime();
                    setTimer();
                }
            );

            updateAppStatus();
            resetRefreshTime();
            setTimer();
        }

    });

    return new AppModel();
});
