define([
    'underscore',
    'backbone',
    'cakeRest'
], function(_, Backbone, cakeRest) {

    "use strict";

    var AppStatusModel = Backbone.Model.extend({

        initialize: function() {
            this.methodToCakePhpUrl = _.clone(this.methodToCakePhpUrl);
            this.methodToCakePhpUrl.read = 'status/';
        },

        setWebroot: function(webroot) {
            this.webroot = webroot + 'saitos/';
        }

    });

    _.extend(AppStatusModel.prototype, cakeRest);

    return AppStatusModel;
});
