define([
    'underscore',
    'backbone',
    'models/appSetting',
    'models/appStatus'
], function(_, Backbone,
    AppSettingModel, AppStatusModel
    ) {

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


        initialize: function(options) {

            this.eventBus = _.extend({}, Backbone.Events);
            this.settings = new AppSettingModel();
            this.status = new AppStatusModel();

            // @td remove export after thread_line.class.js is removed
            eventBus = this.eventBus;

        },

        initAppStatusUpdate: function() {
            var resetRefreshTime,
                updateAppStatus,
                setTimer,
                timerId,
                refreshTimeAct,
                refreshTimeBase = 5000,
                refreshTimeMax = 30000;


            resetRefreshTime = function() {
                if (typeof timerId !== "undefined") {
                    clearTimeout(timerId);
                }
                refreshTimeAct = refreshTimeBase;
            };

            setTimer = function() {
                timerId = setTimeout(
                    updateAppStatus,
                    refreshTimeAct
                );
            }

            updateAppStatus = _.bind(function() {
                setTimer();
                this.status.fetch();
                refreshTimeAct = Math.floor(refreshTimeAct * (1 + refreshTimeAct/40000))
                if (refreshTimeAct > refreshTimeMax) {
                    refreshTimeAct = refreshTimeMax;
                }
            }, this);

            this.status.setWebroot(this.settings.get('webroot'));

            this.listenTo(
                this.status,
                'change',
                function() {
                    resetRefreshTime();
                    setTimer();
                }
            );

            updateAppStatus();
            resetRefreshTime();
            setTimer()
        }

    });

    return new AppModel();
});
