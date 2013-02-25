define([
    'underscore',
    'backbone',
    'models/app',
    'cakeRest'
], function(_, Backbone, App) {

    var AppStatusModel = Backbone.Model.extend({

        initialize: function(options) {
            this.methodToCakePhpUrl = _.clone(this.methodToCakePhpUrl);
            this.methodToCakePhpUrl.read = 'status/';
        },

        setWebroot: function(webroot) {
            this.webroot = webroot + 'saitos/';
        }

    });

    _.extend(AppStatusModel.prototype, SaitoApp.Mixins.cakeRest)

    return AppStatusModel;
});
