define([
    'underscore',
    'backbone',
    'models/app',
    'cakeRest'
], function(_, Backbone, App) {

    var AppStatusModel = Backbone.Model.extend({


        initialize: function(options) {
            this.methodToCakePhpUrl.read = 'status';

            this.webroot = App.settings.get('webroot') + 'saitos/';

            this.listenTo(this, "change:lastShoutId", this._triggerNewShout);
        },

        _triggerNewShout: function() {
            App.eventBus.trigger('lastShoutId', this.get('lastShoutId'))
        }


    });

    _.extend(AppStatusModel.prototype, SaitoApp.Mixins.cakeRest)

    return AppStatusModel;
});
