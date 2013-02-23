define([
    'underscore',
    'backbone',
    'models/appSetting',
    'cakeRest'
], function(_, Backbone, AppSettings) {

    var AppStatusModel = Backbone.Model.extend({


        initialize: function(options) {
            this.methodToCakePhpUrl.read = 'status';

            this.eventBus = options.eventBus;
            this.webroot = AppSettings.get('webroot') + 'saitos/';

            this.listenTo(this, "change:lastShoutId", this._triggerNewShout);
        },

        _triggerNewShout: function() {
            this.eventBus.trigger('lastShoutId', this.get('lastShoutId'))
        }


    });

    _.extend(AppStatusModel.prototype, SaitoApp.Mixins.cakeRest)

    return AppStatusModel;
});
