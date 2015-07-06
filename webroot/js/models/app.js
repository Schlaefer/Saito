define([
    'underscore',
    'backbone',
    'app/vent',
    'models/appSetting',
    'models/appStatus',
    'models/currentUser'
], function (_, Backbone, Vent, AppSettingModel, AppStatusModel,
             CurrentUserModel) {
    'use strict';

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
            this.eventBus = Vent.vent;
            this.commands = Vent.commands;
            this.reqres = Vent.reqres;
            this.settings = new AppSettingModel();
            this.status = new AppStatusModel({}, {settings: this.settings});
            this.currentUser = new CurrentUserModel();
        }

    });

    return new AppModel();
});
